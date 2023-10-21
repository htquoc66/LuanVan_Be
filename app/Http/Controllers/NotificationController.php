<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\SendNotification;
use App\Models\Notification;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

class NotificationController extends Controller
{
    public function sendNotification(Request $request){
       
        event(new SendNotification(
            $request->input('receiverId'), 
            $request->input('message')
        ));

        $notification = new Notification;
        $notification->user_id = $request->input('receiverId');
        $notification->message =$request->input('message');
        $notification->read = false;
        $notification->save();
    
        return response()->json(['success' => true], 200);
     
    }

    public function getNotifications($id){
        $notifications = Notification::where('user_id', $id)
                                  ->orderBy('created_at', 'desc')
                                  ->get();
        return response()->json($notifications);
    }

    public function updateStatus($id){
        $notification = Notification::find($id);
        $notification->read = true;
        $notification->save();
        return response()->json(['success' => true], 200);
    }

    // public function generateDocument(Request $request)
    // {
    //     $fileName = $request->input('ten_file');
    //     $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('storage/' . $fileName);
    //     $templateProcessor->setValue('ngay_thang', $request->input('ngay_thang'));

    //     $templateProcessor->setValue('ten_a', $request->input('ten_a'));
    //     $templateProcessor->setValue('dia_chi_a', $request->input('dia_chi_a'));
    //     $templateProcessor->setValue('cccd_a', $request->input('cccd_a'));

    //     $templateProcessor->setValue('ten_b', $request->input('ten_b'));
    //     $templateProcessor->setValue('dia_chi_b', $request->input('dia_chi_b'));
    //     $templateProcessor->setValue('cccd_b', $request->input('cccd_b'));

    //     $templateProcessor->setValue('noi_dung', $request->input('noi_dung'));

    //     $templateProcessor->setValue('ten_ccv', $request->input('ten_ccv'));


    //     $outputFileName = $request->input('ten_file_moi');
    //     // Save the modified template as a new Word document
    //     $outputFilePath = public_path('storage/'. $outputFileName);
    //     $templateProcessor->saveAs($outputFilePath);
    //     return response()->json(['message' => 'Document generated successfully', 'file_path' => $outputFilePath]);

    // }

   

    

}
