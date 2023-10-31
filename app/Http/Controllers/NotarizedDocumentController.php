<?php
namespace App\Http\Controllers;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use App\Jobs\UploadImportToGoogleDrive;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

use App\Models\NotarizedDocument;
use App\Models\Invoice;
use App\Models\CustomerNotarizedDocument;
use App\Models\LawTextNotarizedDocument;
use App\Models\UserNotarizedDocument;
use App\Models\CostNotarizedDocument;

use App\Http\Resources\NotarizedDocumentResource;

use Illuminate\Http\Request;

class NotarizedDocumentController extends Controller
{
    public function index(){
        $notarizedDocuments = NotarizedDocument::all();
        return response()->json($notarizedDocuments);
    }

    // lấy tất cả hồ sơ của 1 user
    public function getDocumentsByUser($userId)
    {
       // Lấy tất cả hồ sơ của người dùng có $userId
        $userDocuments = NotarizedDocument::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->orderBy('created_at', 'DESC')
        ->get();

        // Trả về danh sách tài liệu công chứng dưới dạng JSON
        return response()->json(NotarizedDocumentResource::collection($userDocuments), 200);
    }

    public function getDocumentsByCustomer($customerId)
    {
        // Lấy tất cả hồ sơ liên quan đến customer có $customerId
        $customerDocuments = NotarizedDocument::whereHas('customers', function ($query) use ($customerId) {
            $query->where('customer_id', $customerId);
        })
        ->where('status', '!=', 1) // Thêm điều kiện status khác 1

        ->orderBy('created_at', 'DESC')
        ->get();

        // Trả về danh sách tài liệu công chứng dưới dạng JSON
        return response()->json(($customerDocuments));
    }


    public function getNotarizedDocuments($status, $userId)
    {
        $notarizedDocuments = NotarizedDocument::where('status', $status)
        ->whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->orderBy('created_at', 'DESC')
        ->get();

        // Trả về danh sách tài liệu công chứng dưới dạng JSON
        return response()->json(NotarizedDocumentResource::collection($notarizedDocuments), 200);
    }
    

    public function store(Request $request)
    {    
        $validatedData = $request->validate([
            'category_id' => 'required',
            'name' => 'required|string',
            'file' => 'required',
            'status' => 'nullable|string',
            'date' => 'required|date',
            'total_cost' => 'required',
        ], [
            'category_id.required' => 'Trường danh mục là bắt buộc.',
            'name.required' => 'Trường tên là bắt buộc.',
            'file.required' => 'Tệp tin không được bỏ trống.',
            // 'file.file' => 'Tệp tin phải là một tệp.',
            'date.required' => 'Trường ngày là bắt buộc.',
            'date.date' => 'Trường ngày phải là ngày hợp lệ.',
            'total_cost.required' => 'Trường tổng chi phí là bắt buộc.',
        ]);

        if ($request->hasFile('file')) {
            $validatedData['file'] = '123';
        }

            $notarizedDocument = NotarizedDocument::create($validatedData);
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName();
                    // Di chuyển tệp tải lên vào thư mục storage/app/public
                $file->move(public_path('storage'), $fileName);

                // Đường dẫn đầy đủ đến tệp sau khi đã di chuyển
                $filePath = public_path('storage') . '/' . $fileName;
                UploadImportToGoogleDrive::dispatch($notarizedDocument, $filePath, $fileName);
            }

            $this->attachUsers($notarizedDocument, $request);



            // căn cứ pháp luật
            $lawTexts = $request['selectedLawTexts'];
            foreach($lawTexts as $value){
                $lawTextNotarizedDocument = new LawTextNotarizedDocument;
                $lawTextNotarizedDocument->law_text_id = $value['id'];
                $lawTextNotarizedDocument->notarized_document_id =  $notarizedDocument->id;
                $lawTextNotarizedDocument->save();
            }

            // bên A
            $customersA = $request['customersA'];
            foreach($customersA as $value){
                $customerNotarizedDocument = new CustomerNotarizedDocument;
                $customerNotarizedDocument->notarized_document_id =  $notarizedDocument->id;
                $customerNotarizedDocument->customer_id = $value['id'];
                $customerNotarizedDocument->description = "customerA";
                $customerNotarizedDocument->save();
            }
            // bên B
            $customersB = $request['customersB'];
            foreach($customersB as $value){
                $customerNotarizedDocument = new CustomerNotarizedDocument;
                $customerNotarizedDocument->notarized_document_id =  $notarizedDocument->id;
                $customerNotarizedDocument->customer_id = $value['id'];
                $customerNotarizedDocument->description = "customerB";
                $customerNotarizedDocument->save();
            }

            return response()->json(['success' => true], 201);
   
    }

    public function show($id)
    {
        $notarizedDocument = NotarizedDocument::find($id);
    
        if (!$notarizedDocument) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy hồ sơ công chứng'], 404);
        }    
        return  response()->json(new NotarizedDocumentResource($notarizedDocument));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'category_id' => 'required',
            'name' => 'required|string',
            'file' => 'required',
            'status' => 'nullable|string',
            'date' => 'required|date',
            'total_cost' => 'required',
        ], [
            'category_id.required' => 'Trường danh mục là bắt buộc.',
            'name.required' => 'Trường tên là bắt buộc.',
            'date.required' => 'Trường ngày là bắt buộc.',
            'date.date' => 'Trường ngày phải là ngày hợp lệ.',
            'total_cost.required' => 'Trường tổng chi phí là bắt buộc.',
        ]);
    
       
            $notarizedDocument = NotarizedDocument::findOrFail($id);
            
         
            if($request->file != $notarizedDocument->file){
                $this->deleteFileFromGoogleDrive($notarizedDocument->file);

                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $fileName = $file->getClientOriginalName();

                    // Tải tệp lên Google Drive và lấy URL trên Google Drive
                     $googleDriveUrl = $this->uploadToGoogleDrive($fileName, $file->getRealPath());

                    // Cập nhật trường 'file' với URL trên Google Drive
                    $validatedData['file'] = $googleDriveUrl;

                    // // Lưu tệp tin mới vào thư mục storage/notarizedDocuments
                    // $file->move('storage/notarizedDocuments', $fileName);
                }
            }
           
    
            $notarizedDocument->update($validatedData);
    
            // Xóa liên kết cũ với người dùng, văn bản pháp luật và khách hàng
            UserNotarizedDocument::where('notarized_document_id', $id)->delete();
            LawTextNotarizedDocument::where('notarized_document_id', $id)->delete();
            CustomerNotarizedDocument::where('notarized_document_id', $id)->delete();
    
            // Cập nhật thông tin người dùng, văn bản pháp luật và khách hàng
            $this->attachUsers($notarizedDocument, $request);

            // căn cứ pháp luật
            $lawTexts = $request['selectedLawTexts'];
            foreach($lawTexts as $value){
                $lawTextNotarizedDocument = new LawTextNotarizedDocument;
                $lawTextNotarizedDocument->law_text_id = $value['id'];
                $lawTextNotarizedDocument->notarized_document_id =  $notarizedDocument->id;
                $lawTextNotarizedDocument->save();
            }

            // bên A
            $customersA = $request['customersA'];
            foreach($customersA as $value){
                $customerNotarizedDocument = new CustomerNotarizedDocument;
                $customerNotarizedDocument->notarized_document_id =  $notarizedDocument->id;
                $customerNotarizedDocument->customer_id = $value['id'];
                $customerNotarizedDocument->description = "customerA";
                $customerNotarizedDocument->save();
            }
            // bên B
            $customersB = $request['customersB'];
            foreach($customersB as $value){
                $customerNotarizedDocument = new CustomerNotarizedDocument;
                $customerNotarizedDocument->notarized_document_id =  $notarizedDocument->id;
                $customerNotarizedDocument->customer_id = $value['id'];
                $customerNotarizedDocument->description = "customerB";
                $customerNotarizedDocument->save();
            }
            
            if($request['costs']){
                $costs = $request['costs'];
                foreach($costs as $value){
                    $costNotarizedDocument = new CostNotarizedDocument;
                    $costNotarizedDocument->notarized_document_id =  $notarizedDocument->id;
                    $costNotarizedDocument->cost_id = $value['id'];
                    $costNotarizedDocument->save();
                }
            }
    
            return response()->json(['success' => true, 'message' => 'Cập nhật thành công'], 200);

    }
    
    public function destroy($id)
    {
        $notarizedDocument = NotarizedDocument::find($id);

        if (!$notarizedDocument) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy hồ sơ công chứng'], 404);
        }

        try {
            // Xóa tệp tin nếu tồn tại
            if ($notarizedDocument->file && file_exists(public_path('storage/notarizedDocuments/' . $notarizedDocument->file))) {
                unlink(public_path('storage/notarizedDocuments/' . $notarizedDocument->file));
            }

            $notarizedDocument->delete();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xóa hồ sơ công chứng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function attachUsers(NotarizedDocument $notarizedDocument, Request $request)
    {
        $roles = [
            // 'secretary' => 'secretary',
            'manager' => 'manager',

            'notary' => 'notary',
            'accountant' => 'accountant',
        ];
    
        foreach ($roles as $role => $description) {
            if ($request[$role] && $request[$role]['id'] !== null) {
                $userNotarizedDocument = new UserNotarizedDocument;
                $userNotarizedDocument->notarized_document_id = $notarizedDocument->id;
                $userNotarizedDocument->user_id = $request[$role]['id'];
                $userNotarizedDocument->description = $description;
                $userNotarizedDocument->save();
            }
        }
    }

    public function generateDocument(Request $request, $id)
    {
        // Lấy tên tệp template và đường dẫn đến tệp template
        $templateFileName = $request->input('ten_file');
        $templateFilePath = public_path('storage/forms/' . $templateFileName);

        // Kiểm tra xem tệp template có tồn tại không
        if (!file_exists($templateFilePath)) {
            return response()->json(['message' => 'Tệp template không tồn tại'], 404);
        }

        // Khởi tạo TemplateProcessor để điền dữ liệu vào tệp template
        $templateProcessor = new TemplateProcessor($templateFilePath);

        // Danh sách các trường cần điền dữ liệu và dữ liệu tương ứng từ request
        if($id == 1){
            $fields = [
                'ngay_thang', 'ten_a','nam_sinh_a', 'cccd_a','ngay_cccd_a', 'dia_chi_a',
                'ten_b','nam_sinh_b', 'cccd_b','ngay_cccd_b', 'dia_chi_b', 'noi_dung', 'ten_ccv'
            ];
        }
        else if($id == 2){
            $fields = [
                'ngay_thang', 'ten_a1', 'nam_sinh_a1', 'cccd_a1','ngay_cccd_a1','dia_chi_a1',
                'ten_a2', 'nam_sinh_a2', 'cccd_a2','ngay_cccd_a2', 'dia_chi_a2',
                'ten_b1', 'nam_sinh_b1', 'cccd_b1','ngay_cccd_b1', 'dia_chi_b1',
                'ten_b2', 'nam_sinh_b2', 'cccd_b2','ngay_cccd_b2', 'dia_chi_b2',
                'so_thua', 'to_ban_do', 'dia_chi_dat', 'dien_tich', 'sd_rieng', 'sd_chung', 'muc_dich_sd', 'hsd', 'nguon_goc', 'gt', 'gt_chu', 'pttt', 'ten_ccv'
            ];
        }
        else if($id == 3){
            $fields = [
                'ngay_thang', 'ten_a1', 'nam_sinh_a1', 'cccd_a1', 'ngay_cccd_a1', 'dia_chi_a1',
                'ten_a2', 'nam_sinh_a2', 'cccd_a2', 'ngay_cccd_a2', 'dia_chi_a2',
                'ten_b1', 'nam_sinh_b1', 'cccd_b1', 'ngay_cccd_b1', 'dia_chi_b1',
                'bs_xe', 'so_dk', 'ngay_cap_dk', 'nhan_hieu', 'dung_tich', 'loai_xe', 'mau_son', 'so_may', 'so_khung', 'ngay_het_han',
                'gt', 'gt_chu', 'pttt', 'ten_ccv',
            ];
        }    
        else{
            $fields = [
                'ngay_thang', 'ten_a1', 'nam_sinh_a1', 'cccd_a1','ngay_cccd_a1','dia_chi_a1',
                'ten_a2', 'nam_sinh_a2', 'cccd_a2','ngay_cccd_a3', 'dia_chi_a2',
                'ten_b1', 'nam_sinh_b1', 'cccd_b1','ngay_cccd_b1', 'dia_chi_b1',
                'ten_b2', 'nam_sinh_b2', 'cccd_b2','ngay_cccd_b2', 'dia_chi_b2',
                'ten_ccv'
            ];

        }    

        foreach ($fields as $field) {
            $value = $request->input($field);
            $templateProcessor->setValue($field, $value);
        }

        // Lấy tên cho tệp mới
        $outputFileName = $request->input('ten_file_moi');
        $outputFilePath = public_path('storage/' . $outputFileName);

        // Lưu tệp đã điền dữ liệu
        $templateProcessor->saveAs($outputFilePath);

        // Tải lên tệp đã điền dữ liệu lên Google Drive
        $googleDriveLink = $this->uploadToGoogleDrive($outputFileName, $outputFilePath);

        // Xóa tệp sau khi tải lên Google Drive
        unlink($outputFilePath);

        return response()->json(['success' => true, 'google_drive_link' => $googleDriveLink]);
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
            'parents' => [env('GOOGLE_DRIVE_FOLDER_ID_2')],
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

    function deleteFileFromGoogleDrive($fileId)
    {
        // Khởi tạo Google Client
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));

        // Khởi tạo dịch vụ Google Drive
        $driveService = new Google_Service_Drive($client);

        try {
            // Sử dụng phương thức 'delete' để xóa tệp
            $driveService->files->delete($fileId);

            // Trả về true nếu không có lỗi
            return true;
        } catch (Exception $e) {
            // Trả về false nếu có lỗi
            return false;
        }
    }
   
}
