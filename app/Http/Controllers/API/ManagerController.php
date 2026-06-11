<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\ManagerService;
use App\Models\Application;
use App\Models\ReviewComment;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use App\Http\Services\LogsService;

class ManagerController extends Controller
{
    protected $managerService;

    protected $logsService;

    public function __construct(ManagerService $managerService, LogsService $logsService)
    {
        $this->managerService = $managerService;
        $this->logsService = $logsService;
    }


    private function authorizeManager(Request $request)
    {
        if (!$request->user() || !$request->user()->isManager()) {
            abort(403, 'عفواً، لا تمتلك الصلاحية لدخول هذه الصفحة (للمدير فقط).');
        }
    }

    public function dashboard(Request $request)
    {
        $this->authorizeManager($request);

        $data = $this->managerService->getPendingFinalApprovals();
        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function finalApprovals(Request $request)
    {
        $this->authorizeManager($request);

        $data = $this->managerService->getFinalApprovalsHistory();
        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function decisionDetails(Request $request, $id)
    {
        $this->authorizeManager($request);

        $data = $this->managerService->getDecisionDetails($id);
        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function processDecision(Request $request, $id)
    {
        $this->authorizeManager($request);

        $request->validate([
            'decision' => 'required|in:approved,rejected,needs_modification',
            'notes'    => 'nullable|string'
        ]);

        try {
            $result = DB::transaction(function () use ($id, $request) {
                $application = Application::findOrFail($id);
                $managerId = $request->user()->id;



                if ($request->decision === 'approved') {
                    $application->current_stage = 'approved';
                    $application->save();
                    $this->logsService->store($application->id, $managerId, 'تم اعتماد البحث نهائياً وتحويله لمرحلة الدفع', 'decision');

                    Notification::create([
                        'user_id'        => $application->student_id,
                        'application_id' => $application->id,
                        'message'        => "تهانينا! تم اعتماد بحثك رقم ({$application->serial_number}) نهائياً، يرجى الانتقال لسداد الرسوم الماليّة.",
                        'channel'        => 'system',
                        'is_read'        => 0,
                        'email_sent'     => 1,
                    ]);

                    return [
                        'status' => 'success',
                        'message' => 'تم اعتماد البحث بنجاح، وإرسال الإشعار للباحث، والآن سوف يتم تحويل الطلب لمرحلة سداد الرسوم.',
                        'action' => 'redirect_payment'
                    ];
                }
                if ($request->decision === 'rejected') {
                    $application->current_stage = 'rejected';
                    $application->save();
                    $this->logsService->store($application->id, $managerId, 'تم رفض البحث نهائياً من قبل الإدارة', 'decision');
                    Notification::create([
                        'user_id'        => $application->student_id,
                        'application_id' => $application->id,
                        'message'        => "للأسف، تم رفض طلبك البحثي رقم ({$application->serial_number}) نهائياً. سبب الرفض: " . ($request->notes ?? 'لم يتم ذكر أسباب إضافية.'),
                        'channel'        => 'system',
                        'is_read'        => 0,
                        'email_sent'     => 1,
                    ]);

                    return [
                        'status' => 'success',
                        'message' => 'تم رفض البحث نهائياً، وحفظ الملاحظات، وإشعار الباحث المباشر.',
                        'action' => 'redirect_dashboard'
                    ];
                }
                if ($request->decision === 'needs_modification') {
                    $application->needs_modification = 1;
                    $application->save();
                    $this->logsService->store($application->id, $managerId, 'تم طلب إجراء تعديلات على البحث من الباحث', 'decision');

                    Notification::create([
                        'user_id'        => $application->student_id,
                        'application_id' => $application->id,
                        'message'        => "برجاء العلم أن بحثك رقم ({$application->serial_number}) يحتاج إلى بعض التعديلات بناءً على مراجعة الإدارة. الملاحظات: " . ($request->notes ?? 'يرجى مراجعة صفحة الطلب لمزيد من التفاصيل.'),
                        'channel'        => 'system',
                        'is_read'        => 0,
                        'email_sent'     => 1,
                    ]);

                    return [
                        'status' => 'success',
                        'message' => 'تم إرسال طلب التعديل للباحث بنجاح، وحفظ الملاحظات في الإشعارات.',
                        'action' => 'redirect_dashboard'
                    ];
                }


                return [
                    'status' => 'error',
                    'message' => 'القرار غير معرف حالياً.'
                ];
            });

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function reportsStatistics(Request $request)
    {
        $this->authorizeManager($request);

        $data = $this->managerService->getSystemStatistics();
        return response()->json(['status' => 'success', 'data' => $data]);
    }
    public function getCertificateDetails($application_id)
    {
        try {
            $certificateData = $this->managerService->getCertificateDetails($application_id);

            return response()->json([
                'status' => 'success',
                'data' => $certificateData
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'عفواً، لم يتم العثور على شهادة صادرة لهذا البحث.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ ما أثناء جلب بيانات الشهادة: ' . $e->getMessage()
            ], 500);
        }
    }
}
