<?php

namespace App\Http\Controllers;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Http\Request;
use App\Models\Storage;
use App\Http\Resources\StorageResource;
use App\Jobs\UploadImportToGoogleDrive;
use PhpZip\ZipFile;


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
            'zip_password' => 'required',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $notarizedDocumentId = $storage->notarized_document_id;

            // Lấy đuôi file ban đầu
            $fileExtension = $file->getClientOriginalExtension();

            // Đặt tên file theo định dạng "HoSo" + notarized_document_id + đuôi file ban đầu
            $fileName = "HoSo{$notarizedDocumentId}.{$fileExtension}";

            $file->move(public_path('storage'), $fileName);
            $filePath = public_path('storage') . '/' . $fileName;

            // Nén tệp và đặt mật khẩu
            $zipFileName = pathinfo($fileName, PATHINFO_FILENAME) . '.zip';
            $zipFilePath = public_path('storage') . '/' . $zipFileName;

            $zipFile = new ZipFile();
            $zipFile->addFile($filePath, $fileName);
            $zipFile->setPassword($validatedData['zip_password']); // Sử dụng mật khẩu được truyền từ client
            $zipFile->saveAsFile($zipFilePath);

            // Gọi hàm uploadToGoogleDrive để tải lên tệp nén lên Google Drive và nhận ID Drive
            $driveFileId = $this->uploadToGoogleDrive($zipFileName, $zipFilePath);

            // Cập nhật trường 'file' của bảng Storage với ID Drive mới
            $validatedData['file'] = $driveFileId;

            // Sau khi tải lên thành công, xóa tệp cục bộ và tệp nén
            unlink($filePath);
            unlink($zipFilePath);
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
