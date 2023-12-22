<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class EnvConfigController extends Controller
{
    public function updateDriveConfig(Request $request)
    {
        // Lấy các giá trị từ Request
        $clientId = $request->input('client_id');
        $clientSecret = $request->input('client_secret');
        $refreshToken = $request->input('refresh_token');
        $folderId = $request->input('folder_id');
        $folderId1 = $request->input('folder_id_1');
        $folderId2 = $request->input('folder_id_2');
    
        // Đọc nội dung của tệp .env
        $envFilePath = base_path('.env');
        $envContent = File::get($envFilePath);
    
        // Kiểm tra và cập nhật giá trị trong tệp .env nếu giá trị từ Request khác null
        if ($clientId !== null && $clientId !== env('GOOGLE_DRIVE_CLIENT_ID')) {
            $envContent = preg_replace("/^GOOGLE_DRIVE_CLIENT_ID=.*/m", "GOOGLE_DRIVE_CLIENT_ID=$clientId", $envContent);
        }
    
        if ($clientSecret !== null && $clientSecret !== env('GOOGLE_DRIVE_CLIENT_SECRET')) {
            $envContent = preg_replace("/^GOOGLE_DRIVE_CLIENT_SECRET=.*/m", "GOOGLE_DRIVE_CLIENT_SECRET=$clientSecret", $envContent);
        }
    
        if ($refreshToken !== null && $refreshToken !== env('GOOGLE_DRIVE_REFRESH_TOKEN')) {
            $envContent = preg_replace("/^GOOGLE_DRIVE_REFRESH_TOKEN=.*/m", "GOOGLE_DRIVE_REFRESH_TOKEN=$refreshToken", $envContent);
        }
    
        if ($folderId !== null && $folderId !== env('GOOGLE_DRIVE_FOLDER_ID')) {
            $envContent = preg_replace("/^GOOGLE_DRIVE_FOLDER_ID=.*/m", "GOOGLE_DRIVE_FOLDER_ID=$folderId", $envContent);
        }
    
        if ($folderId1 !== null && $folderId1 !== env('GOOGLE_DRIVE_FOLDER_ID_1')) {
            $envContent = preg_replace("/^GOOGLE_DRIVE_FOLDER_ID_1=.*/m", "GOOGLE_DRIVE_FOLDER_ID_1=$folderId1", $envContent);
        }
    
        if ($folderId2 !== null && $folderId2 !== env('GOOGLE_DRIVE_FOLDER_ID_2')) {
            $envContent = preg_replace("/^GOOGLE_DRIVE_FOLDER_ID_2=.*/m", "GOOGLE_DRIVE_FOLDER_ID_2=$folderId2", $envContent);
        }
    
        // Ghi lại tệp .env đã được cập nhật
        File::put($envFilePath, $envContent);
    
        // Yêu cầu Laravel làm mới cấu hình
        Artisan::call('config:clear');
    
        return response()->json(['success' => true], 200);
    }
    
    public function updateMailConfig(Request $request)
    {
        // Lấy các giá trị từ Request
        $mailUsername = $request->input('mail_username');
        $mailPassword = $request->input('mail_password');

        // Đọc nội dung của tệp .env
        $envFilePath = base_path('.env');
        $envContent = File::get($envFilePath);

        // Kiểm tra và cập nhật giá trị trong tệp .env nếu giá trị từ Request khác null
        if ($mailUsername !== null && $mailUsername !== env('MAIL_USERNAME')) {
            $envContent = preg_replace("/^MAIL_USERNAME=.*/m", "MAIL_USERNAME=$mailUsername", $envContent);
        }

        if ($mailPassword !== null && $mailPassword !== env('MAIL_PASSWORD')) {
            $envContent = preg_replace("/^MAIL_PASSWORD=.*/m", "MAIL_PASSWORD=$mailPassword", $envContent);
        }

        // Ghi lại tệp .env đã được cập nhật
        File::put($envFilePath, $envContent);

        // Yêu cầu Laravel làm mới cấu hình
        Artisan::call('config:clear');

        return response()->json(['success' => true], 200);
    }

    public function getBankInfo()
    {
        $accountNo = env('ACCOUNT_NO', 'default_account_no');
        $accountName = env('ACCOUNT_NAME', 'default_account_name');
        $acqId = env('ACQ_ID', 'default_acquirer_id');

        $bankInfo = [
            'accountNo' => $accountNo,
            'accountName' => $accountName,
            'acqId' => $acqId,
        ];

        return response()->json($bankInfo);
    }

    public function updateBankInfo(Request $request)
    {
        $request->validate([
            'accountNo' => 'required|string',
            'accountName' => 'required|string',
            'acqId' => 'required|string',
        ]);

        $envFile = base_path('.env');

        // Đọc tệp .env
        $content = file_get_contents($envFile);

        // Thay đổi giá trị
        $content = preg_replace("/^ACCOUNT_NO=.*/m", "ACCOUNT_NO={$request->input('accountNo')}", $content);
        $content = preg_replace("/^ACCOUNT_NAME=.*/m", "ACCOUNT_NAME=\"{$request->input('accountName')}\"", $content);
        $content = preg_replace("/^ACQ_ID=.*/m", "ACQ_ID={$request->input('acqId')}", $content);

        // Ghi lại vào tệp .env
        file_put_contents($envFile, $content);

        // Clear cache để Laravel nhận biết sự thay đổi
        Artisan::call('config:clear');

        return response()->json(['success' => true, 'message' => 'Bank info updated successfully']);
    }


   
}
