<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Google_Client;
use Google\Service\Drive as Google_Service_Drive;

class UploadImportToGoogleDrive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;
    protected $file;

    public function __construct($model, $file)
    {
        $this->model = $model;
        $this->file = $file;
    }

    public function handle()
    {
        $fileName = $this->file->getClientOriginalName();
        $filePath = $this->file->getRealPath();

        // Initialize Google Client
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        $service = new Google_Service_Drive($client);

        // Create metadata for the file
        $fileMetadata = new Google_Service_Drive\DriveFile([
            'name' => $fileName, // Modify the naming convention as needed
            'parents' => [env('GOOGLE_DRIVE_FOLDER_ID')],
        ]);

        // Upload the file to Google Drive
        $uploadedFile = $service->files->create($fileMetadata, [
            'data' => file_get_contents($filePath),
            'uploadType' => 'multipart',
            'fields' => 'id',
        ]);

        // Lưu ID của tệp tải lên vào model
        $this->model->file = $uploadedFile->id;
        $this->model->save();
    }
}
