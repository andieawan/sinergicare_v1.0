<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) { 
        header('Location: /login.php'); 
        exit(); 
    }
}

function requireRole(array $roles): void {
    if (!hasRole($roles)) { 
        header('Location: /pages/dashboard.php'); 
        exit(); 
    }
}

function hasRole(array $roles): bool {
    return count(array_intersect($roles, currentUserRoles())) > 0;
}

function currentUserId(): int { 
    return (int)($_SESSION['user_id'] ?? 0); 
}

function currentUserName(): string { 
    return $_SESSION['user_nama'] ?? ''; 
}

function currentUserRoles(): array {
    $r = $_SESSION['user_roles'] ?? [];
    return is_array($r) ? $r : [$r];
}