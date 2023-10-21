<?php

namespace App\Http\Controllers;
use App\Models\User;
use Validator;
use Auth;
use Mail;
use App\Models\RoleUser;

use Illuminate\Http\Request;
use App\Http\Resources\StaffResource;
use App\Jobs\SendStaffMail;
// DÙNG BẢNG USERS
class StaffController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'DESC')->get();
        return response()->json(StaffResource::collection($users));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'gender' => 'required|string',
            'date_of_birth' => 'required|date',
            'password' => 'required|string|min:8',
        ], [
            'name.required' => 'Trường họ tên là bắt buộc.',
            'phone.required' => 'Trường số điện thoại là bắt buộc.',
            'email.required' => 'Trường email là bắt buộc.',
            'email.email' => 'Trường email phải là một địa chỉ email hợp lệ.',
            'email.unique' => 'Email đã tồn tại',
            'gender.required' => 'Trường giới tính là bắt buộc.',
            'date_of_birth.required' => 'Trường ngày sinh là bắt buộc.',
            'date_of_birth.date' => 'Trường ngày sinh phải là một ngày hợp lệ.',
            'password.required' => 'Trường mật khẩu là bắt buộc.',
            'password.min' => 'Trường mật khẩu phải chứa ít nhất 8 ký tự.',
        ]);

        // // Kiểm tra xem email đã tồn tại trong cơ sở dữ liệu hay chưa
        // if (User::where('email', $validatedData['email'])->exists()) {
        //     return response()->json(['success' => false, 'message' => 'Email đã tồn tại trong cơ sở dữ liệu.'], 422);
        // }

        // Mã hóa mật khẩu trước khi lưu vào cơ sở dữ liệu
        $validatedData['password'] = bcrypt($validatedData['password']);

        try {
            $user = User::create($validatedData);

            // Gửi email bất đồng bộ trong hàng đợi
            SendStaffMail::dispatch($user->name, $user->email,  $request->password);
            
            return response()->json(['success' => true, 'message' => 'Tạo người dùng thành công', 'id' => $user->id], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi tạo người dùng', 'error' => $e->getMessage()], 500);
        }
    }


    public function show($id)
    {
        // Tìm người dùng dựa trên ID
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy người dùng'], 404);
        }
    
        // Trả về thông tin người dùng dưới dạng JSON
        return response()->json(new StaffResource($user));
    }


    public function update(Request $request, $id)
    {
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy người dùng'], 404);
        }
    
        $validatedData = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string|email|unique:users, email',
            'gender' => 'required|string',
            'date_of_birth' => 'required|date',
        ], [
            'name.required' => 'Trường họ tên là bắt buộc.',
            'phone.required' => 'Trường số điện thoại là bắt buộc.',
            'email.required' => 'Trường email là bắt buộc.',
            'email.email' => 'Trường email phải là một địa chỉ email hợp lệ.',
            'email.email' => 'Trường email phải là một địa chỉ email hợp lệ.',
            'gender.required' => 'Trường giới tính là bắt buộc.',
            'date_of_birth.required' => 'Trường ngày sinh là bắt buộc.',
            'date_of_birth.date' => 'Trường ngày sinh phải là một ngày hợp lệ.',
        ]);

        // Kiểm tra xem email đã tồn tại trong cơ sở dữ liệu và không trùng với user hiện tại
        if (User::where('email', $validatedData['email'])->where('id', '!=', $id)->exists()) {
             return response()->json(['success' => false, 'message' => 'Email đã tồn tại trong cơ sở dữ liệu.'], 422);
        } 
    
        try {
            $user->update($validatedData);
            return response()->json(['success' => true, 'message' => 'Cập nhật người dùng thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi cập nhật người dùng', 'error' => $e->getMessage()], 500);
        }
    }
    

    public function destroy($id)
    {
        $user = User::find($id);
        $user->blocked = !$user->blocked;
        $user->save();
        return response()->json(['success'=>'true'], 200);
    }


    public function login(Request $request) {
        $arr = [
            'email' => $request->email,
            'password' => $request->password,
        ];
    
        $user = User::where('email', $request->email)->first();
        
        // Kiểm tra xem tài khoản đã bị khóa chưa
        if ($user && $user->blocked == 0) {
            $response = [
                'success' => false,
                'message' => 'Tài khoản của bạn đã bị khóa'
            ];
            return response()->json($response);
        }
        
        if (Auth::guard('web')->attempt($arr)) {
            $response = [
                'success' => true,
                'token' => $user->createToken('MyApp')->plainTextToken,
                'user' =>  new StaffResource($user),
                'message' => "User login successfully"
            ];
    
            return response()->json($response, 200);
        } else {
            $response = [
                'success' => false,
                'message' => 'Tài khoản hoặc mật khẩu không đúng!'
            ];
            return response()->json($response);
        }
    }

    public function getStaffOfRole(Request $request){
        
        $roleName = $request->input('roleName'); 

        $user = User::whereHas('roles', function ($query) use ($roleName) {
            $query->where('name', $roleName);
        })->get();
    
        return response()->json($user);
    }

    public function getStaffWithPermission(Request $request) {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'permission_id' => 'required|integer',
        ]);
    
        $permissionId = $request->input('permission_id');
    
        // Lấy danh sách người dùng có quyền cụ thể
        $users = User::whereHas('roles.permissions', function ($query) use ($permissionId) {
            $query->where('permission_id', $permissionId);
        })->get();
    
        return response()->json($users);
    }
    

    public function addRoleToStaff(Request $request, $userId)
    {
        $roles = $request->input('roles');
        foreach($roles as $value){
            $roleUser = new RoleUser;
            $roleUser->role_id = $value;
            $roleUser->user_id = $userId;
            $roleUser->save();
        }
        return response()->json(['success' => true]);
    }
    public function updateRoleToStaff(Request $request, $userId)
    {
        $roles = $request->input('roles');
        // Xóa tất cả các bản ghi RoleUser hiện có cho user đã cho
        RoleUser::where('user_id', $userId)->delete();        
        foreach($roles as $value){
            $roleUser = new RoleUser;
            $roleUser->role_id = $value;
            $roleUser->user_id = $userId;
            $roleUser->save();
        }
        return response()->json(['success' => true]);
    }   



}
