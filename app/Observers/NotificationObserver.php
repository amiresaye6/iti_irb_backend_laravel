<?php

namespace App\Observers;

use App\Models\Notification;
use App\Services\EmailService;

class NotificationObserver
{
    
    public function created(Notification $notification): void
    {
        
        $notification->loadMissing(['user', 'application']);

        $user = $notification->user;

        if (!$user || empty($user->email)) {
            return;
        }

        
        $recipientName = $user->full_name ?? $user->name ?? 'مستخدم';
        $appSerial = $notification->application ? $notification->application->serial_number : null;
        
        $subjectLine = 'إشعار جديد من نظام IRB الرقمي';

        EmailService::send(
            toEmail: $user->email,
            recipientName: $recipientName,
            subjectLine: $subjectLine,
            messageBody: $notification->message ?? 'لديك إشعار جديد في النظام.',
            appSerial: $appSerial,
            notificationId: $notification->id
        );
    }
}
