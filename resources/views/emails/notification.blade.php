<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#f0f2f5;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f2f5;padding:30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#2c3e50 0%,#34495e 50%,#1abc9c 100%);padding:36px 40px;border-radius:16px 16px 0 0;text-align:center;">
                            <div style="font-size:28px;margin-bottom:10px;">🔬</div>
                            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:800;letter-spacing:1px;">نظام IRB الرقمي</h1>
                            <p style="margin:6px 0 0;color:rgba(255,255,255,0.8);font-size:13px;font-weight:500;">لجنة أخلاقيات البحث العلمي</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="background:#ffffff;padding:36px 40px;border-left:1px solid #e8e8e8;border-right:1px solid #e8e8e8;">
                            
                            <!-- Greeting -->
                            <p style="margin:0 0 20px;font-size:17px;color:#2c3e50;font-weight:700;">
                                مرحباً {{ $recipientName }} 👋
                            </p>

                            <!-- Serial Badge -->
                            @if(!empty($appSerial))
                            <div style="margin-bottom:20px;">
                                <span style="display:inline-block;background-color:#f8f9fa;border:1px solid #e8e8e8;color:#2c3e50;font-size:13px;font-weight:700;padding:6px 12px;border-radius:6px;">
                                    الرقم المرجعي: {{ $appSerial }}
                                </span>
                            </div>
                            @endif

                            <!-- Message -->
                            <div style="background:linear-gradient(135deg,#f8f9fa 0%,#ffffff 100%);border:1px solid #e8e8e8;border-right:4px solid #1abc9c;border-radius:12px;padding:22px 24px;margin:0 0 24px;font-size:15px;color:#34495e;line-height:1.8;font-weight:600;">
                                {!! nl2br(e($messageBody)) !!}
                            </div>

                            <!-- CTA Button -->
                            <div style="text-align:center;margin:28px 0;">
                                <!-- <a href="{{ config('app.url') }}" style="display:inline-block;background:linear-gradient(135deg,#1abc9c 0%,#16a085 100%);color:#ffffff;text-decoration:none;padding:14px 40px;border-radius:10px;font-size:15px;font-weight:800;letter-spacing:0.5px;box-shadow:0 4px 15px rgba(26,188,156,0.35);">
                                    الدخول إلى النظام ←
                                </a> -->
                                <a href="{{ $ctaUrl ?? config('app.url') }}" style="display:inline-block;background:linear-gradient(135deg,#1abc9c 0%,#16a085 100%);color:#ffffff;text-decoration:none;padding:14px 40px;border-radius:10px;font-size:15px;font-weight:800;letter-spacing:0.5px;box-shadow:0 4px 15px rgba(26,188,156,0.35);">
                                    {{ $ctaText ?? 'الدخول إلى النظام ←' }}
                                </a>
                            </div>

                            <!-- Divider -->
                            <hr style="border:none;border-top:2px dashed #e8e8e8;margin:24px 0;">

                            <!-- Info Note -->
                            <p style="margin:0;font-size:12px;color:#95a5a6;line-height:1.6;font-weight:500;">
                                💡 هذا البريد مرسل تلقائياً من نظام IRB الرقمي. في حال وجود أي استفسار، يرجى التواصل مع إدارة اللجنة.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#2c3e50;padding:24px 40px;border-radius:0 0 16px 16px;text-align:center;">
                            <p style="margin:0 0 6px;color:rgba(255,255,255,0.7);font-size:12px;font-weight:500;">
                                © {{ date('Y') }} نظام IRB الرقمي — جميع الحقوق محفوظة
                            </p>
                            <p style="margin:0;color:rgba(255,255,255,0.45);font-size:11px;">
                                Institutional Review Board Digital System
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
