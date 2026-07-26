<!DOCTYPE html>
<html>
<head>
    <title>Kode Verifikasi HadirYuk</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f5; padding: 20px;">
    <div style="max-w: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="color: #4f46e5; margin-top: 0;">Verifikasi Akun Anda</h2>
        <p style="color: #4b5563; font-size: 16px; line-height: 1.5;">Terima kasih telah mendaftar di HadirYuk. Untuk melanjutkan, silakan masukkan kode verifikasi berikut:</p>
        
        <div style="background-color: #f3f4f6; padding: 20px; border-radius: 8px; text-align: center; margin: 30px 0;">
            <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #111827;">{{ $otpCode }}</span>
        </div>
        
        <p style="color: #ef4444; font-size: 14px;">Kode ini akan kedaluwarsa dalam 15 menit. Jangan berikan kode ini kepada siapa pun.</p>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        <p style="color: #9ca3af; font-size: 12px; text-align: center;">&copy; {{ date('Y') }} HadirYuk. All rights reserved.</p>
    </div>
</body>
</html>
