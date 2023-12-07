<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\CustomerNotarizedDocument;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon; // Import Carbon

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with('customer')
            ->orderBy('created_at', 'desc') 
            ->get();
    
        // Sử dụng map để định dạng lại trường created_at
        $reviews = $reviews->map(function ($review) {
            return [
                'id' => $review->id,
                'customer_id' => $review->customer_id,
                'content' => $review->content,
                'rating' => $review->rating,
                'status' => $review->status,
                'created_at' => $review->created_at->format('H:i d/m/Y '), // Định dạng lại created_at
                'updated_at' => $review->updated_at->format('H:i d/m/Y '), // Định dạng lại updated_at
                'customer' => $review->customer,
            ];
        });
    
        return response()->json($reviews);
    }
    

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'customer_id' => 'required',
            'rating' => 'required|numeric',
        ], [
            'customer_id.required' => 'Trường ID khách hàng là bắt buộc.',
            'rating.required' => 'Trường điểm đánh giá là bắt buộc.',
            'rating.numeric' => 'Trường điểm đánh giá phải là số.',
        ]);

        try {

            $review = Review::create($validatedData);

            return response()->json(['success' => true], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi tạo đánh giá', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['error' => 'Đánh giá không tồn tại'], 404);
        }

        return response()->json($review);
    }

    public function update(Request $request, $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['error' => 'Đánh giá không tồn tại'], 404);
        }

        $validatedData = $request->validate([
            'customer_id' => 'required',
            'rating' => 'required|numeric',
        ], [
            'customer_id.required' => 'Trường ID khách hàng là bắt buộc.',
            'rating.required' => 'Trường điểm đánh giá là bắt buộc.',
            'rating.numeric' => 'Trường điểm đánh giá phải là số.',
        ]);

        try {

            $review = Review::update($validatedData);


            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi cập nhật đánh giá', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['error' => 'Đánh giá không tồn tại'], 404);
        }

        $review->delete();
        return response()->json(['success' => true], 200);
    }

    public function checkCustomerNotarizedDocument($customer_id)
    {
        // Đếm số lượng đánh giá của khách hàng
        $reviewCount = Review::where('customer_id', $customer_id)->count();
    
        // Đếm số lượng hồ sơ trong bảng `customer_notarized_document` với `customer_id` cụ thể
        $documentCount = CustomerNotarizedDocument::where('customer_id', $customer_id)->count();
        // return $reviewCount;
        // So sánh số lượng hồ sơ và số lượng đánh giá
        if ($reviewCount >= $documentCount ) {
            // Không thể đánh giá thêm
            return response()->json(['success' => false]);          
        } else {
            // Có thể đánh giá
            return response()->json(['success' => true]);
        }
    }
    
    

}
