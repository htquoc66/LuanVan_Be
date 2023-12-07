<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cost;
use Illuminate\Support\Facades\Validator;

class CostController extends Controller
{
    public function index()
    {
        $costs = Cost::with('costType') // Sử dụng with để nạp thông tin loại chi phí
        ->get();
        
    return response()->json($costs);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'cost_type_id' => 'required',
            'name' => 'required|string',
            'price' => 'required|numeric',
        ], [
            'cost_type_id.required' => 'Trường loại chi phí là bắt buộc.',
            'name.required' => 'Trường tên chi phí là bắt buộc.',
            'price.required' => 'Trường giá là bắt buộc.',
            'price.numeric' => 'Trường giá phải là số.',
        ]);

        try {
            $cost = Cost::create($validatedData);
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi tạo chi phí', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $cost = Cost::find($id);

        if (!$cost) {
            return response()->json(['error' => 'Chi phí không tồn tại'], 404);
        }

        return response()->json($cost);
    }

    public function update(Request $request, $id)
    {
        $cost = Cost::find($id);

        if (!$cost) {
            return response()->json(['error' => 'Chi phí không tồn tại'], 404);
        }

        $validatedData = $request->validate([
            'cost_type_id' => 'required',
            'name' => 'required|string',
            'price' => 'required|numeric',
        ], [
            'cost_type_id.required' => 'Trường loại chi phí là bắt buộc.',
            'name.required' => 'Trường tên chi phí là bắt buộc.',
            'price.required' => 'Trường giá là bắt buộc.',
            'price.numeric' => 'Trường giá phải là số.',
        ]);

        try {
            $cost->update($validatedData);
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi cập nhật chi phí', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $cost = Cost::find($id);

        if (!$cost) {
            return response()->json(['error' => 'Chi phí không tồn tại'], 404);
        }

       
        // Cập nhật cột 'deleted' thành true thay vì xóa 
        $cost->update(['deleted' => true]);
        return response()->json(['success' => true], 200);
    }
}
