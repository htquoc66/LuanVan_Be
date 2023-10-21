<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\NotarizedDocument;
use App\Models\Invoice;
use Carbon\Carbon;
class StatisticController extends Controller
{
    public function getDocumentCountsByCategory($minDate, $maxDate)
    {
        // Truy vấn CSDL để lấy danh mục và số lượng hồ sơ theo danh mục
        $documentCounts = NotarizedDocument::whereBetween('date', [$minDate, $maxDate])
            ->select('category_id', \DB::raw('COUNT(id) as document_count'))
            ->groupBy('category_id')
            ->get();

        // Lấy danh mục và số lượng hồ sơ theo danh mục
        $categoryCounts = [];
        foreach ($documentCounts as $documentCount) {
            $category = Category::find($documentCount->category_id);
            if ($category) {
                $categoryCounts[] = [
                    'category_name' => $category->name,
                    'document_count' => $documentCount->document_count,
                ];
            }
        }

        return response()->json($categoryCounts);
    }

    public function getRevenueByDateRange($minDate, $maxDate)
    {
        // Truy vấn CSDL để lấy doanh thu từng ngày trong khoảng thời gian
        $revenueData = Invoice::whereBetween('date', [$minDate, $maxDate])
            ->select(\DB::raw('DATE(date) as date'), \DB::raw('SUM(cost) as total_revenue'))
            ->groupBy(\DB::raw('DATE(date)'))
            ->get();

        // Format dữ liệu để trả về dưới dạng JSON
        $formattedData = [];
        foreach ($revenueData as $item) {
            $formattedData[] = [
                'date' => $item->date,
                'total_revenue' => $item->total_revenue,
            ];
        }

        return response()->json($formattedData);
    }
    public function getRevenueByCategory($minDate, $maxDate)
    {
        // Truy vấn CSDL để lấy danh sách hồ sơ và doanh thu theo từng loại
        $revenueData = NotarizedDocument::whereBetween('date', [$minDate, $maxDate])
            ->select('category_id', \DB::raw('SUM(total_cost) as total_revenue'))
            ->groupBy('category_id')
            ->get();

        // Lấy thông tin về danh mục cho từng hồ sơ
        $categoryRevenues = [];
        foreach ($revenueData as $item) {
            $category = Category::find($item->category_id);
            if ($category) {
                $categoryRevenues[] = [
                    'category_name' => $category->name,
                    'total_revenue' => $item->total_revenue,
                ];
            }
        }

        return response()->json($categoryRevenues);
    }

    public function getDocumentCountToday()
    {
        // Lấy ngày hiện tại sử dụng Carbon
        $currentDate = Carbon::now()->toDateString();

        // Truy vấn CSDL để lấy tổng số hồ sơ trong ngày hiện tại
        $documentCount = NotarizedDocument::whereDate('date', $currentDate)->count();

        return response()->json($documentCount);
    }

}
