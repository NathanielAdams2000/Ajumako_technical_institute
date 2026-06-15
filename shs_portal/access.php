<?php
// Make sure session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Role-based access control
 * Usage:
 * requireRole(['admin']);
 * requireRole(['admin', 'teacher']);
 */

function requireRole($roles = []) {

    // If role is not set, force login
    if (!isset($_SESSION['role'])) {
        header("Location: /shs_portal/login.php");
        exit();
    }

    // Convert single role into array if needed
    if (!is_array($roles)) {
        $roles = [$roles];
    }

    // Check if user role is allowed
    if (!in_array($_SESSION['role'], $roles)) {
        die("
            <div style='
                font-family: Arial;
                text-align:center;
                margin-top:50px;
                color:red;
                font-size:20px;
            '>
                ❌ Access Denied<br>
                <small>You do not have permission to view this page.</small>
            </div>
        ");
    }
}
?>
