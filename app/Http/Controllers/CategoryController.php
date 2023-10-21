<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('created_at', 'DESC')->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validatedData =  $request->validate([
            'name' => 'required|string', 
        ],
        [
            'name.required' => 'Trường tên danh mục là bắt buộc.',

        ]);


        try{
            $category = Category::create( $validatedData);
            return response()->json(['success' => true], 200);
        
        } catch(\Exception $e){
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi tạo danh mục', 'error' => $e->getMessage()], 500);

        }



    }

    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $validatedData =  $request->validate([
            'name' => 'required|string', 
        ],
        [
            'name.required' => 'Trường tên danh mục là bắt buộc.',
        ]);

        try{
            $category->update( $validatedData);
            return response()->json(['success' => true], 200);
        
        } catch(\Exception $e){
            return response()->json(['success' => false, 
            'message' => 'Đã xảy ra lỗi khi cập nhật danh mục',
             'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $category->delete();
        return response()->json(['success' => true], 200);
    }
}
