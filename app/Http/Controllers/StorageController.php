<?php

namespace App\Http\Controllers;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Http\Request;
use App\Models\Storage;
use App\Http\Resources\StorageResource;
use App\Jobs\UploadImportToGoogleDrive;


class StorageController extends Controller
{
    public function index()
    {
        $storages = Storage::orderBy('created_at', 'DESC')->get();
        return response()->json(StorageResource::collection($storages));

    }  

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'notarized_document_id' => 'required|exists:notarized_documents,id',
            'file' => 'nullable',
        ]);

        $storage = Storage::create($validatedData);
        return response()->json(['success'=>'true'], 200);
    }

    public function show($id)
    {
        $storage = Storage::find($id);
        if (!$storage) {
            return response()->json(['message' => 'Storage not found'], 404);
        }
        return response()->json($storage);
    }

    public function update(Request $request, $id)
    {
        $storage = Storage::find($id);
        if (!$storage) {
            return response()->json(['message' => 'Storage not found'], 404);
        }
    
        $validatedData = $request->validate([
            'file' => 'required',
        ]);
    
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $file->move(public_path('storage'), $fileName);
            $filePath = public_path('storage') . '/' . $fileName;
    
            // Gọi hàm uploadToGoogleDrive để tải lên tệp lên Google Drive và nhận ID Drive
            $driveFileId = $this->uploadToGoogleDrive($fileName, $filePath);
    
            // Cập nhật trường 'file' của bảng Storage với ID Drive mới
            $validatedData['file'] = $driveFileId;
    
            // Sau khi tải lên thành công, xóa tệp cục bộ
            unlink($filePath);
        }
    
        $storage->update($validatedData);
    
        return response()->json(['success' => true], 200);
    }
    

    private function uploadToGoogleDrive($fileName, $filePath)
    {
        // Khởi tạo Google Client
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));

        // Khởi tạo dịch vụ Google Drive
        $driveService = new Google_Service_Drive($client);

        // Tạo thông tin mô tả cho tệp trên Google Drive
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $fileName,
            'parents' => [env('GOOGLE_DRIVE_FOLDER_ID')],
        ]);

        // Tải lên tệp lên Google Drive
        $uploadedFile = $driveService->files->create($fileMetadata, [
            'data' => file_get_contents($filePath),
            'uploadType' => 'multipart',
            'fields' => 'id',
        ]);

        // Tạo đường dẫn đến tệp trên Google Drive
        return  $uploadedFile->id;
    }

    public function destroy($id)
    {
       
    }
}