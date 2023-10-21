<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Chengivy Store - Chào mừng bạn đến với đội ngũ!</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333333;
        }
        p {
            color: #666666;
        }
        .message {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .password {
            font-weight: bold;
            color: #00aaff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Chào mừng bạn đến với công chứng CTU!</h1>
        <p>Xin chào, <strong>{{ $staffName }}!</strong></p>
        <p>Chúc mừng bạn đã trở thành một phần của đội ngũ của chúng tôi tại công chứng CTU.</p>
        <div class="message">
            <p>Dưới đây là thông tin đăng nhập của bạn:</p>
            <p><strong>Email đăng nhập:</strong> {{ $staffEmail }}</p>
            <p><strong>Mật khẩu đăng nhập:</strong> <span class="password">{{ $staffPassword }}</span></p>
            <p>Vui lòng <a href="http://localhost:8080/admin/login">đăng nhập</a> vào hệ thống và đổi mật khẩu ngay sau khi đăng nhập thành công.</p>
        </div>
        <p>Xin cảm ơn và chúc bạn một ngày làm việc hiệu quả!</p>
    </div>
</body>
</html>