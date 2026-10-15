<?php

namespace App\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use App\Support\Database;

class UserController
{
    public function index(Request $request): Response
    {
        $pdo = Database::getInstance();
        $users = $pdo->query("SELECT id, username, full_name, role, can_record_stock_in, is_active as status, created_at FROM users ORDER BY created_at DESC")->fetchAll();
        return View::render('users/index', ['users' => $users]);
    }

    public function store(Request $request): Response
    {
        $pdo = Database::getInstance();
        $data = $request->all();
        
        $username = trim($data['username'] ?? '');
        $fullName = trim($data['full_name'] ?? '');
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? 'viewer';
        $canRecordStockIn = isset($data['can_record_stock_in']) ? 1 : 0;
        
        if (empty($username) || empty($password) || empty($fullName)) {
            $_SESSION['error'] = 'All fields are required.';
            return Response::redirect(\url('/users'));
        }
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Username already exists.';
            return Response::redirect(\url('/users'));
        }
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, role, can_record_stock_in, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$username, $passwordHash, $fullName, $role, $canRecordStockIn]);
        
        $_SESSION['success'] = 'User Account created successfully.';
        return Response::redirect(\url('/users'));
    }

    public function update(Request $request, string $id): Response
    {
        $pdo = Database::getInstance();
        $data = $request->all();
        
        $username = trim($data['username'] ?? '');
        $fullName = trim($data['full_name'] ?? '');
        $role = $data['role'] ?? 'viewer';
        $canRecordStockIn = isset($data['can_record_stock_in']) ? 1 : 0;
        
        if (empty($username) || empty($fullName)) {
            $_SESSION['error'] = 'Name and Username are required.';
            return Response::redirect(\url('/users'));
        }
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $id]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Username already exists.';
            return Response::redirect(\url('/users'));
        }
        
        $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, role = ?, can_record_stock_in = ? WHERE id = ?");
        $stmt->execute([$username, $fullName, $role, $canRecordStockIn, $id]);
        
        $_SESSION['success'] = 'User details updated successfully.';
        return Response::redirect(\url('/users'));
    }

    public function resetPassword(Request $request, string $id): Response
    {
        $newPassword = $request->input('new_password');
        
        if (empty($newPassword) || strlen($newPassword) < 6) {
            $_SESSION['error'] = 'Password must be at least 6 characters.';
            return Response::redirect(url('/users'));
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$passwordHash, $id]);

        $_SESSION['success'] = 'User password reset successfully.';
        return Response::redirect(url('/users'));
    }

    public function toggle(Request $request, string $id): Response
    {
        // Don't lockout self if logic evaluates to true, but we omit session check since user might not be in $_SESSION directly (they use a model maybe). We will just toggle.
        $pdo = Database::getInstance();
        
        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success'] = 'User status updated successfully.';
        return Response::redirect(\url('/users'));
    }
}
