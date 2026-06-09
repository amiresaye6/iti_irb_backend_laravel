<?php

namespace App\Http\Services;

use App\Models\Review;
use App\Models\Application;
use App\Models\Certificate;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerService
{
    public function getPendingFinalApprovals()
    {
        return Application::with(['student'])
            ->where('current_stage', 'final_review')
            ->latest()
            ->get();
    }

    public function getFinalApprovalsHistory()
    {
        return Application::with(['student', 'reviews'])
            ->where('current_stage', 'approved')
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function getDecisionDetails($applicationId)
    {
        return Application::with(['student', 'reviews.comments'])
            ->findOrFail($applicationId);
    }

    public function processDecision($applicationId, $decision, $notes, $managerId)
    {
        return DB::transaction(function () use ($applicationId, $decision, $notes, $managerId) {
            $application = Application::with('student')->findOrFail($applicationId);
            $student = $application->student;

            if (in_array($decision, ['rejected', 'needs_modification']) && empty(trim($notes))) {
                throw new \Exception('يجب كتابة الملاحظات وأسباب القرار أولاً.');
            }

            if ($decision === 'approved') {
                $application->update([
                    'current_stage' => 'approved',
                    'needs_modifications' => 0,
                    'manager_notes' => $notes
                ]);

                Notification::create([
                    'user_id' => $student->id,
                    'application_id' => $application->id,
                    'message' => "تمت الموافقة المبدئية على بحثك ذو الرقم التسلسلي ({$application->serial_number}) من قِبل الإدارة، يرجى الانتظار لحين تحديد رسوم السداد.",
                    'channel' => 'system'
                ]);

                return [
                    'status' => 'success',
                    'message' => 'تمت الموافقة بنجاح، جاري التحويل لتحديد الرسوم.'
                ];
            }

            if ($decision === 'needs_modification') {
                $application->update([
                    'current_stage' => 'needs_modification',
                    'needs_modifications' => 1,
                    'manager_notes' => $notes
                ]);

                Notification::create([
                    'user_id' => $student->id,
                    'application_id' => $application->id,
                    'message' => "تمت مراجعة طلبك ({$application->serial_number}) من قِبل المدير، وتوجد ملاحظات تتطلب التعديل: {$notes}",
                    'channel' => 'system'
                ]);

                return [
                    'status' => 'success',
                    'message' => 'تم إرسال طلب التعديل للباحث بنجاح.'
                ];
            }
            if ($decision === 'rejected') {
                $application->update([
                    'current_stage' => 'rejected',
                    'needs_modifications' => 0,
                    'manager_notes' => $notes
                ]);

                Notification::create([
                    'user_id' => $student->id,
                    'application_id' => $application->id,
                    'message' => "نأسف لإبلاغك بأنه قد تم رفض طلب البحث ذو الرقم التسلسلي ({$application->serial_number}) نهائياً بناءً على مراجعة الإدارة.",
                    'channel' => 'system'
                ]);

                return [
                    'status' => 'success',
                    'message' => 'تم رفض طلب البحث نهائياً.'
                ];
            }

            throw new \Exception('إجراء غير صالح أو غير معرف بالسيستم.');
        });
    }

    public function getSystemStatistics()
    {
        $approvedCount = Application::where('current_stage', 'approved')->count();
        $rejectedCount = Application::where('current_stage', 'rejected')->count();
        $certCount = Certificate::count();
        $pendingCount = Application::whereIn('current_stage', ['under_review', 'final_review'])->count();

        $totalApps = $approvedCount + $rejectedCount + $pendingCount;

        $performanceRate = ($totalApps > 0) ? round((($approvedCount + $rejectedCount) / $totalApps) * 100) : 0;
        $acceptanceRate = ($totalApps > 0) ? round(($approvedCount / $totalApps) * 100) : 0;

        return [
            'approved_count' => $approvedCount,
            'certificate_count' => $certCount,
            'rejected_count' => $rejectedCount,
            'pending_count' => $pendingCount,
            'performance_rate' => $performanceRate,
            'acceptance_rate' => $acceptanceRate,
            'chart_data' => [
                'labels' => ['قبول نهائي', 'رفض نهائي', 'قيد المراجعة'],
                'datasets' => [$approvedCount, $rejectedCount, $pendingCount]
            ]
        ];
    }

    public function getCertificateDetails($applicationId)
    {
        $certificate = \App\Models\Certificate::with(['application.student', 'manager'])
            ->where('application_id', $applicationId)
            ->firstOrFail();

        return [
            'certificate_number' => $certificate->certificate_number,
            'university_name' => 'جامعة الزقازيق',
            'faculty_name' => 'لجنة أخلاقيات البحث العلمي (IRB)',
            'student_name' => $certificate->application->student->full_name ?? 'N/A',
            'student_national_id' => $certificate->application->student->national_id ?? 'N/A',
            'research_title' => $certificate->application->title,
            'serial_number' => $certificate->application->serial_number,
            'approval_date' => \Carbon\Carbon::parse($certificate->issued_at)->format('Y-m-d'),
            'manager_name' => $certificate->manager->full_name ?? 'أ.د. طارق الحديدي',
            'qr_code_url' => url("/api/verify-certificate/" . $certificate->certificate_number),
        ];
    }
}
