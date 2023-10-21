<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Customer;
use App\Models\Date;
use App\Models\Time;
use App\Models\Appointment;
use App\Jobs\SendAppointmentConfirmation;

use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['customer', 'date', 'time'])
            ->get();

        return response()->json($appointments);
    }

    public function getMyAppointments(Request $request)
    {
        $user_id = $request->input('user_id');
        $customer_id = $request->input('customer_id');
        
        if ($user_id) {
            $appointments = Appointment::with(['customer', 'date', 'time'])
                ->where('user_id', $user_id)
                ->get();
        } elseif ($customer_id) {
            $appointments = Appointment::with(['customer', 'date', 'time'])
                ->where('customer_id', $customer_id)
                ->get();
        } else {
            $appointments = [];
        }
    
        return response()->json($appointments);
    }
    
    
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required',
            'date' => 'required|date',
            'time' => 'required',
            'content' => 'required',
            'status'  => ' required'
        ]);

        // Kiểm tra xem ngày đã tồn tại trong bảng Date chưa
        $date = Date::firstOrCreate(['date_field' => $data['date']]);

        // Sử dụng ID của ngày để tạo lịch hẹn
        $appointment = new Appointment([
            'customer_id' => $data['customer_id'],
            'date_id' => $date->id, // Sử dụng ID của ngày
            'time_id' => $data['time'],
            'content' => $data['content'],
            'status' => $data['status'],
        ]);

        $appointment->save();
        return response()->json(['success' => true, 'message' => 'Lịch hẹn đã được tạo thành công'], 201);
    }

    public function update(Request $request, $id)
{
    $data = $request->validate([
        'user_id' => 'required',
        'status' => 'required'
    ]);

    // Tìm cuộc hẹn theo ID
    $appointment = Appointment::with(['customer', 'date', 'time'])->find($id);

    if (!$appointment) {
        return response()->json(['success' => false, 'message' => 'Không tìm thấy cuộc hẹn'], 404);
    }

    // Cập nhật user_id và status
    $appointment->user_id = $data['user_id'];
    $appointment->status = $data['status'];
    $appointment->save();

    // Gửi email thông báo cuộc hẹn đã được xác nhận bằng job
    SendAppointmentConfirmation::dispatch(
        $appointment->customer->email,
        $appointment->date->date_field,
        $appointment->time->time_field,
        $appointment->content
    ); 

    return response()->json(['success' => true, 'message' => 'User ID và trạng thái cuộc hẹn đã được cập nhật'], 200);
}

}
