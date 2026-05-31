<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Services\ManagerService;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    protected $managerService;

    public function __construct(ManagerService $managerService)
    {
        $this->managerService = $managerService;
    }

    public function preview($application_id)
    {
        try {
            $data = $this->managerService->getCertificateDetails($application_id);

            return response()->json([
                'status' => 'success',
                'message' => 'بحثك معتمد نهائياً، برجاء التوجه لشؤون الطلاب بالكلية لاستلام الشهادة الرسمية المعتمدة.',
                'data' => [
                    'research_title' => $data['research_title'],
                    'serial_number' => $data['serial_number'],
                    'approval_date' => $data['approval_date'],
                    'status' => 'جاهزة للاستلام الورقي'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'الشهادة غير موجودة.'], 404);
        }
    }

    public function downloadForStaff($application_id, Request $request)
    {
        if (!$request->user() || !$request->user()->isManager()) {
            return response()->json([
                'status' => 'error',
                'message' => 'عفواً، لا تمتلك الصلاحية لتحميل أو طباعة الشهادة الرسمية.'
            ], 403);
        }

        try {
            $data = $this->managerService->getCertificateDetails($application_id);

            return response()->json([
                'status' => 'success',
                'can_print' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'حدث خطأ في جلب بيانات الطباعة.'], 400);
        }
    }
}
