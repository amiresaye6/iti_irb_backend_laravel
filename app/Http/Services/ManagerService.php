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
        return Review::with(['application.student'])
            ->whereHas('application', function ($query) {
                $query->where('current_stage', 'final_review');
            })
            ->where('decision', 'approved')
            ->orderBy(
                Application::select('created_at')
                    ->whereColumn('applications.id', 'reviews.application_id')
                    ->latest()
            )
            ->get();
    }

    public function getFinalApprovalsHistory()
    {
        return Application::with(['student', 'reviews'])
            ->where('current_stage', 'approved')
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function getDecisionDetails($reviewId)
    {
        return Review::with(['application.student', 'comments'])
            ->findOrFail($reviewId);
    }

    public function processDecision($reviewId, $action, $managerId)
    {
        return DB::transaction(function () use ($reviewId, $action, $managerId) {
            $review = Review::with('application.student')->findOrFail($reviewId);
            $application = $review->application;
            $student = $application->student;

            if ($action === 'approve') {
                $review->update(['decision' => 'approved']);
                $application->update(['current_stage' => 'approved']);

                $currentYear = date('Y');
                $totalCertsThisYear = Certificate::whereYear('issued_at', $currentYear)->count();
                $nextNumber = $totalCertsThisYear + 1;
                $certNum = "CERT-" . $currentYear . "-" . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

                $certificate = Certificate::create([
                    'application_id' => $application->id,
                    'student_id' => $student->id,
                    'manager_id' => $managerId,
                    'certificate_number' => $certNum,
                    'issued_to_name' => $student->full_name,
                    'issued_at' => now(),
                ]);

                Notification::create([
                    'user_id' => $student->id,
                    'application_id' => $application->id,
                    'message' => "مبروك! تم اعتماد بحثك ذو الرقم التسلسلي ({$application->serial_number}) نهائياً. وتم إصدار شهادة رقم ({$certNum}) باسمك.",
                    'channel' => 'system'
                ]);

                return ['status' => 'success', 'cert_url' => '/view-certificate/' . $application->id];
            } elseif ($action === 'return') {
                $review->update(['decision' => 'needs_modification']);
                $application->update(['current_stage' => 'under_review']);

                Notification::create([
                    'user_id' => $student->id,
                    'application_id' => $application->id,
                    'message' => "تمت مراجعة طلبك ({$application->serial_number})، وتمت إعادته للمراجع لاستيفاء بعض الملاحظات.",
                    'channel' => 'system'
                ]);

                return ['status' => 'success', 'cert_url' => null];
            }

            throw new \Exception('جراء غير صالح');
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
