<?php

namespace App\Http\Controllers;
use App\Models\Form;
use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Drive;


class FormController extends Controller
{
    public function index()
    {
        $forms = Form::orderBy('created_at', 'DESC')->get();
        return response()->json($forms);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'file' => 'required',
        ],
        [
            'category_id.required' => 'Trường danh mục là bắt buộc.',
            'name.required' => 'Trường tên là bắt buộc.',
            'description.string' => 'Trường mô tả phải là chuỗi.',
            'file.required' => 'Tệp tin không được bỏ trống.',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $file->move('storage/forms', $fileName);

            // Làm hàng đợi
            // Khởi tạo Client của Google
            $client = new Google_Client();
            $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
            $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
            $service = new Google_Service_Drive($client);

            // Tạo thông tin mô tả cho tệp
            $fileMetadata = new \Google_Service_Drive_DriveFile([
                'name' => $fileName,
                'parents' => [env('GOOGLE_DRIVE_FOLDER_ID_1')],
            ]);

            // Kiểm tra và xử lý đường dẫn tới tệp tin
            $filePath = 'storage/forms/' . $fileName;
            if (file_exists($filePath)) {
                // Tải lên tệp lên Google Drive
                $uploadedFile = $service->files->create($fileMetadata, [
                    'data' => file_get_contents($filePath),
                    'uploadType' => 'multipart',
                    'fields' => 'id',
                ]);

                $validatedData['link'] = 'https://docs.google.com/document/d/' .$uploadedFile->id;

            } else {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy tệp tin.'], 404);
            }
        }

        // Gán tên tệp đã tải lên vào mảng dữ liệu đã xác nhận
        $validatedData['file'] = $fileName;
        try {
            $form = Form::create($validatedData);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi tạo biểu mẫu', 'error' => $e->getMessage()], 500);
        }
    }

    
    public function show($id)
    {
        $form = Form::find($id);

        if (!$form) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy biểu mẫu'], 404);
        }
        
        return response()->json($form);
    }

    public function update(Request $request, $id)
    {
        
        $form = Form::find($id);
        if (!$form) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy biểu mẫu'], 404);
        }
    
        $validatedData = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
        ],
        [
            'category_id.required' => 'Trường danh mục là bắt buộc.',
            'name.required' => 'Trường tên là bắt buộc.',
            'description.string' => 'Trường mô tả phải là chuỗi.',
        ]);
        if($request->file == $form->file){
            $fileName = $request->file;
        } 
        else{
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName();      
         
                if ($form->file && file_exists(public_path('storage/forms/' . $form->file))) {
                    unlink(public_path('storage/forms/' . $form->file));
                }        
                $file->move('storage/forms', $fileName);
            }
        }
        
        $validatedData['file'] = $fileName;

    
        try {
            $form->update($validatedData);
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 
            'message' => 'Đã xảy ra lỗi khi cập nhật biểu mẫu', 'error' => $e->getMessage()], 500);
        }    
    }  
    
    public function destroy($id)
    {
        $form = Form::find($id);
    
        if (!$form) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy biểu mẫu'], 404);
        } 

        if (preg_match('/\/document\/d\/(.*?)$/', $form->link, $matches)) {
            $documentId = $matches[1]; // ID của Google Docs Document
        }

        // bỏ dô hàng đợi
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        $service = new Google_Service_Drive($client);
        $service->files->delete($documentId);
    
        try {
            // Xóa file nếu nó tồn tại
            if ($form->file && file_exists(public_path('storage/forms/' . $form->file))) {
                unlink(public_path('storage/forms/' . $form->file));
            }
    
            $form->delete();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 
            'message' => 'Đã xảy ra lỗi khi xóa biểu mẫu', 'error' => $e->getMessage()], 500);
        }
    }
    
}
