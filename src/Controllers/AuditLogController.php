<?php

namespace App\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use App\Models\AuditLog;

class AuditLogController
{
    public function index(Request $request): Response
    {
        $module = $request->input('module');
        $logs = AuditLog::getRecent(200, $module);
        
        return View::render('audit/index', [
            'logs' => $logs,
            'currentModule' => $module
        ]);
    }
}
