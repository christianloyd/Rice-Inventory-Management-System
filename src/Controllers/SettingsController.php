<?php

namespace App\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use App\Models\Setting;
use App\Services\AuditService;

class SettingsController
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    public function index(Request $request): Response
    {
        $settings = Setting::all();
        return View::render('settings/index', ['settings' => $settings]);
    }

    public function update(Request $request): Response
    {
        $keys = ['app_name', 'store_name', 'store_address', 'store_contact', 'receipt_footer', 'low_stock_notify', 'next_control_number'];
        $old = Setting::all();

        foreach ($keys as $key) {
            $val = $request->input($key, '');
            Setting::set($key, $val);
        }

        $this->auditService->log(
            $_SESSION['user_id'],
            'settings.updated',
            'settings',
            null,
            null,
            $old,
            Setting::all()
        );

        Response::setFlash('success', 'Settings saved successfully.');
        return Response::redirect(url('/settings'));
    }
}
