<?php

namespace App\Http\Controllers;

use App\Models\LawText;
use Illuminate\Http\Request;

class LawTextController extends Controller
{
    public function index()
    {
        $lawTexts = LawText::orderBy('created_at', 'DESC')->get();
        return response()->json($lawTexts);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:law_texts,name',
            'file' => 'required|file',
            'effective_date' => 'required|date',
            'status' => 'nullable|string',
        ], [
            'name.required' => 'Trường tên là bắt buộc.',
            'name.unique' => 'Tên đã tồn tại trong cơ sở dữ liệu.',
            'file.required' => 'Tệp tin không được bỏ trống.',
            'file.file' => 'Tệp tin phải là một tệp.',
            'effective_date.required' => 'Trường ngày hiệu lực là bắt buộc.',
            'effective_date.date' => 'Trường ngày hiệu lực phải là ngày hợp lệ.',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();

            // Lưu tệp tin vào thư mục storage/lawTexts
            $file->move('storage/lawTexts', $fileName);

            $validatedData['file'] = $fileName;
        }
  
        try {
            $lawText = LawText::create($validatedData);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tạo văn bản pháp luật',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $lawText = LawText::where('id', $id)->first();
    
        if (!$lawText) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy văn bản pháp luật'], 404);
        }
    
        return response()->json($lawText);
    }
    

    public function update(Request $request, $id)
    {
        $lawText = LawText::find($id);

        if (!$lawText) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy văn bản pháp luật'], 404);
        }
    
        $validatedData = $request->validate([
            'name' => 'required|string',
            'effective_date' => 'required|date',
            'status' => 'nullable|string',
        ], [
            'name.required' => 'Trường tên là bắt buộc.',
            'effective_date.required' => 'Trường ngày hiệu lực là bắt buộc.',
            'effective_date.date' => 'Trường ngày hiệu lực phải là ngày hợp lệ.',
        ]);
    
        try {
            if ($request->file == $lawText->file) {
                $fileName = $request->file;
            } else {
                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $fileName = $file->getClientOriginalName();
    
                    if ($lawText->file && file_exists(public_path('storage/lawTexts/' . $lawText->file))) {
                        unlink(public_path('storage/lawTexts/' . $lawText->file));
                    }
    
                    $file->move('storage/lawTexts', $fileName);
                }
            }
    
            $validatedData['file'] = $fileName;
    
            $lawText->update($validatedData);
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi cập nhật văn bản pháp luật',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        $lawText = LawText::find($id);

        if (!$lawText) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy văn bản pháp luật'], 404);
        }

        try {
            if (file_exists(public_path('storage/lawTexts/' . $lawText->file))) {
                unlink(public_path('storage/lawTexts/' . $lawText->file));
            }

            $lawText->delete();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xóa văn bản pháp luật',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
