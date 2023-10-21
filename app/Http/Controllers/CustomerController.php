<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CustomerResource;


class CustomerController extends Controller
{

    public function index()
    {
        $customers = Customer::whereNotNull('idCard_number')
                    ->orderBy('created_at', 'DESC')
                    ->get();
    
        return response()->json(CustomerResource::collection($customers));
    }
    

    public function store(Request $request)
    {
        $customer = Customer::where('email', $request->email)->first();
    
        if (!$customer) {
            // Không tìm thấy khách hàng với email đã cung cấp, tạo một khách hàng mới
            $validatedData = $request->validate([
                'type_id' => 'required|integer',
                'name' => 'required|string',
                'idCard_number' => 'string|unique:customers,idCard_number',
                'idCard_issued_date' => 'date',
                'idCard_issued_place' => 'string',
                'gender' => 'string',
                'date_of_birth' => 'date',
                'phone' => 'string',
                'email' => 'required|string|email|unique:customers,email',
                'address' => 'string',
                // 'password' => 'string'
            ]);
    
            // Kiểm tra xem mật khẩu đã được cung cấp
            if ($request->has('password')) {
                $validatedData['password'] = bcrypt($request->password);
            }
    
            try {
                $customer = Customer::create($validatedData);
                return response()->json(['success' => true, 'message' => 'Tạo khách hàng thành công'], 200);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi tạo khách hàng', 'error' => $e->getMessage()], 500);
            }
        // } else {
        //     // Tìm thấy khách hàng với email đã cung cấp, thực hiện cập nhật thông tin
        //     $dataToUpdate = $request->all();
    
        //     // Kiểm tra xem mật khẩu đã được cung cấp
        //     if ($request->has('password')) {
        //         $dataToUpdate['password'] = bcrypt($request->password);
        //     }
    
        //     $customer->update($dataToUpdate);
        //     return response()->json(['success' => true, 'message' => 'Cập nhật thông tin khách hàng thành công'], 200);
        // }
        } else {
            // Tìm thấy khách hàng với email đã cung cấp, thực hiện cập nhật thông tin        
            $dataToUpdate = [];        
            // Kiểm tra từng trường và chỉ cập nhật nếu trường đó là null  
            if ($request->has('idCard_number') && is_null($customer->idCard_number)) {
                $dataToUpdate['idCard_number'] = $request->idCard_number;
            }        
            if ($request->has('idCard_issued_date') && is_null($customer->idCard_issued_date)) {
                $dataToUpdate['idCard_issued_date'] = $request->idCard_issued_date;
            }        
            if ($request->has('idCard_issued_place') && is_null($customer->idCard_issued_place)) {
                $dataToUpdate['idCard_issued_place'] = $request->idCard_issued_place;
            }        
            if ($request->has('gender') && is_null($customer->gender)) {
                $dataToUpdate['gender'] = $request->gender;
            }        
            if ($request->has('date_of_birth') && is_null($customer->date_of_birth)) {
                $dataToUpdate['date_of_birth'] = $request->date_of_birth;
            }     
            if ($request->has('address') && is_null($customer->address)) {
                $dataToUpdate['address'] = $request->address;
            }     
            if ($request->has('phone') && is_null($customer->phone)) {
                $dataToUpdate['phone'] = $request->phone;
            }  
            // Kiểm tra xem mật khẩu đã được cung cấp
            if ($request->has('password')) {
                $dataToUpdate['password'] = bcrypt($request->password);
            }
        
            // Cập nhật chỉ những trường có giá trị null
            $customer->update($dataToUpdate);
            return response()->json(['success' => true, 'message' => 'Cập nhật thông tin khách hàng thành công'], 200);
        }
    
    }
    
    
    

    public function show($id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy khách hàng'], 404);
        }
        return response()->json($customer);
    }


    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);
    
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy khách hàng'], 404);
        }
    
        $validatedData = $request->validate([
            'type_id' => 'required|integer',
            'name' => 'required|string',
            'idCard_number' => 'required|string',
            'idCard_issued_date' => 'required|date',
            'idCard_issued_place' => 'required|string',
            'gender' => 'required|string',
            'date_of_birth' => 'required|date',
            'phone' => 'required|string',
            'email' => 'required|string|email',
            'address' => 'required|string',
        ]);
    
        // Kiểm tra xem email đã tồn tại trong cơ sở dữ liệu và trừ khách hàng hiện tại
        if (Customer::where('email', $validatedData['email'])->where('id', '!=', $id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Email đã tồn tại trong cơ sở dữ liệu.'], 422);
        }
    
        // Kiểm tra xem số căn cước đã tồn tại trong cơ sở dữ liệu và trừ khách hàng hiện tại
        if (Customer::where('idCard_number', $validatedData['idCard_number'])->where('id', '!=', $id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Số căn cước đã tồn tại trong cơ sở dữ liệu.'], 422);
        }
    
        try {
            $customer->update($validatedData);
            return response()->json(['success' => true, 'message' => 'Cập nhật thông tin khách hàng thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi cập nhật thông tin khách hàng', 'error' => $e->getMessage()], 500);
        }
    }
    

    public function login(Request $request){
        $arr = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $customer = Customer::where('email', $request->email)->first();
        
        if(Auth::guard('customer')->attempt($arr)){
            // $customer = Auth::customer();
            
            $response = [
                'success' => true,
                'token' => $customer->createToken('MyApp')->plainTextToken,
                'customer' =>  $customer,
                'message' => "Customer login successfully"
            ];

            return response()->json($response, 200 );
            
        } 
        else {
            $response =[
                'success' => false,
                'message' => 'Email hoặc mật khẩu không đúng!'
            ];
            return response()->json($response);
        }

    }

    // public function destroy($id)
    // {
    //     $customer = Customer::find($id);
        
    //     if (!$customer) {
    //         return response()->json(['success' => false, 'message' => 'Không tìm thấy khách hàng'], 404);
    //     }    
    //     try {
    //         $customer->delete();
    //         return response()->json(['success' => true, 'message' => 'Xóa khách hàng thành công'], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi xóa khách hàng', 'error' => $e->getMessage()], 500);
    //     }
    // }
    
}
