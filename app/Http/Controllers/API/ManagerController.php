<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\ManagerService;

class ManagerController extends Controller
{
    protected $managerService;

    public function __construct(ManagerService $managerService)
    {
        $this->managerService = $managerService;
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
            'action' => 'required|in:approve,return'
        ]);

        try {
            $result = $this->managerService->processDecision($id, $request->action, $request->user()->id);
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
