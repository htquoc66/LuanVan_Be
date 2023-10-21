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
    protected $filePath;
    protected $fileName;

    public function __construct($model, $filePath, $fileName)
    {
        $this->model = $model;
        $this->filePath = $filePath;
        $this->fileName = $fileName;
    }

    public function handle()
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        $service = new Google_Service_Drive($client);

        $fileMetadata = new Google_Service_Drive\DriveFile([
            'name' => $this->fileName,
            'parents' => [env('GOOGLE_DRIVE_FOLDER_ID_2')],
        ]);

        $uploadedFile = $service->files->create($fileMetadata, [
            'data' => file_get_contents($this->filePath),
            'uploadType' => 'multipart',
            'fields' => 'id',
        ]);
        unlink($this->filePath);

     
        $this->model->file = $uploadedFile->id;
        $this->model->save();
    }
}
