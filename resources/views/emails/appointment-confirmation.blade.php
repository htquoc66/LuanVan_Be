<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Thông báo cuộc hẹn đã được xác nhận</title>
    <style>
        .text-blue {
            color: #3279c6 !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-blue">Xác nhận cuộc hẹn</h1>
        <p>Xin chào!</p>
        <p>Cuộc hẹn của bạn vào ngày {{ \Carbon\Carbon::parse($appointmentDate)->locale('vi')->format('d/m/Y') }} lúc {{ $appointmentTime }} đã được xác nhận.</p>
        <p>Nội dung cuộc hẹn: {{ $appointmentContent }}</p>
        <p>Tên công chứng viên: {{ $userName }}</p>
        <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi.</p>
    </div>
</body>

</html>
