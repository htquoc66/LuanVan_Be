<?php

namespace App\Http\Controllers;
use App\Models\NotarizedDocument;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(){
        $invoices = Invoice::orderBy('created_at', 'DESC')->get();
        return response()->json($invoices);

    }

    public function store(Request $request){
        // Xác thực dữ liệu đầu vào từ request
        $validatedData = $request->validate([
            'customer_id' => 'required',
            'user_id' => 'required',
            'content' => 'required',
            'date' => 'required|date',
            'cost' => 'required|numeric',
            'payment_method' => 'required',
            'file_pdf' => 'required|mimes:pdf|max:2048', // Giới hạn tệp PDF 2MB
            'notarizedDocument_id' => 'required', 
        ]);

        if ($request->hasFile('file_pdf')) {
            $pdfFile = $request->file('file_pdf');
            $fileName = $pdfFile->getClientOriginalName();
            // Lưu tệp PDF vào thư mục lưu trữ 
            $pdfFile->move('storage/pdfs', $fileName);
            $validatedData['file_pdf'] = $fileName;
        }

        // Tạo hóa đơn và lưu vào cơ sở dữ liệu
        $invoice = Invoice::create($validatedData);

        //Cập nhật invoice_id cho các notarizedDocument được chọn
        $notarizedDocumentIds = json_decode($request->input('notarizedDocument_id'));
        foreach ($notarizedDocumentIds as $notarizedDocumentId) {
            $notarizedDocument = NotarizedDocument::find($notarizedDocumentId);
            if ($notarizedDocument) {
                $notarizedDocument->invoice_id = $invoice->id;
                $notarizedDocument->status = 5;
                $notarizedDocument->save();
            }
        }

        return response()->json(['success' => true]);
    }
}
