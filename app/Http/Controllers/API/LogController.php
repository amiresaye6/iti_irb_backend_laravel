<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Services\LogsService;
use Illuminate\Http\Request;


class LogController extends Controller
{
    public function __construct(LogsService $logService)
    {
        $this->logService = $logService;
    }

    public function index()
    {
        return response()->json($this->logService->index(), 200);
    }

    public function getLogsByAppId($app_id)
    {
        return response()->json($this->logService->getLogsByAppId($app_id), 200);
    }

    public function getLogsByUserId($user_id)
    {
        return response()->json($this->logService->getLogsByUserId($user_id), 200);
    }

    public function getLogsByType($type)
    {
        return response()->json($this->logService->getLogsByType($type), 200);
    }

    public function getSubmissionLogs()
    {
        return response()->json($this->logService->getLogsByType('submission'), 200);
    }

    public function getAssignmentLogs()
    {
        return response()->json($this->logService->getLogsByType('assignment'), 200);
    }

    public function getDecisionLogs()
    {
        return response()->json($this->logService->getLogsByType('decision'), 200);
    }

    public function getStatusChangeLogs()
    {
        return response()->json($this->logService->getLogsByType('status_change'), 200);
    }

    public function getAuthLogs()
    {
        return response()->json($this->logService->getLogsByType('auth'), 200);
    }

}
