<?php

namespace App\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Support\Validator;
use App\Support\View;
use App\Models\User;
use App\Services\AuditService;

class AuthController
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    public function showLogin(Request $request): Response
    {
        if (isset($_SESSION['user_id'])) {
            return Response::redirect(\url('/dashboard'));
        }
        return View::render('auth/login');
    }

    public function login(Request $request): Response
    {
        $v = Validator::make($request->all());
        if (!$v->validate(['username' => 'required', 'password' => 'required'])) {
            Response::setFlash('error', 'Username and password are required.');
            return Response::redirect(\url('/login'));
        }

        $appConfig = require __DIR__ . '/../../config/app.php';
        $username = $request->input('username');
        $password = $request->input('password');

        $user = User::findByUsername($username);

        if (!$user) {
            Response::setFlash('error', 'Invalid credentials.');
            return Response::redirect(\url('/login'));
        }

        if (!$user['is_active']) {
            Response::setFlash('error', 'Your account is deactivated.');
            return Response::redirect(\url('/login'));
        }

        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $mins = ceil((strtotime($user['locked_until']) - time()) / 60);
            Response::setFlash('error', "Account locked. Try again in {$mins} minutes.");
            return Response::redirect(\url('/login'));
        }

        if (password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['can_record_stock_in'] = $user['can_record_stock_in'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['last_activity'] = time();

            User::updateLogins($user['id'], 0, null);
            $this->auditService->log($user['id'], 'LOGIN', 'auth');

            return Response::redirect(\url('/dashboard'));
        }

        $fails = $user['failed_login_attempts'] + 1;
        $lockedUntil = null;
        if ($fails >= $appConfig['login_max_attempts']) {
            $lockedUntil = date('Y-m-d H:i:s', time() + ($appConfig['login_lockout_minutes'] * 60));
            Response::setFlash('error', "Too many failed attempts. Account locked for {$appConfig['login_lockout_minutes']} minutes.");
        } else {
            Response::setFlash('error', 'Invalid credentials.');
        }

        User::updateLogins($user['id'], $fails, $lockedUntil);

        return Response::redirect(\url('/login'));
    }

    public function logout(Request $request): Response
    {
        if (isset($_SESSION['user_id'])) {
            $this->auditService->log($_SESSION['user_id'], 'LOGOUT', 'auth');
        }
        
        session_unset();
        session_destroy();
        
        return Response::redirect(\url('/login'));
    }
}
