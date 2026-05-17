<?php
include('db/connect.php');


$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if user exists
    $query = "SELECT * FROM users WHERE email=$1";
    $result = pg_query_params($conn, $query, [$email]);
    $user = pg_fetch_assoc($result);

    if ($user) {
        // Generate token and expiry
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $update = "UPDATE users SET reset_token=$1, reset_expires=$2 WHERE email=$3";
        $res = pg_query_params($conn, $update, [$token, $expiry, $email]);

        if ($res) {
            $resetLink = "http://localhost/POSA/reset_password.php?token=$token";
            
            // For now, we’ll just display the link (replace this with mail() later)
            $success = "✅ Password reset link: <a href='$resetLink'>$resetLink</a>";

            // Example if you want to use PHP mail():
            /*
            $subject = "Password Reset Request";
            $message = "Click this link to reset your password: $resetLink";
            $headers = "From: no-reply@yourdomain.com\r\nContent-Type: text/html;";
            mail($email, $subject, $message, $headers);
            */
        } else {
            $error = "❌ Could not create reset link. Please try again.";
        }
    } else {
        $error = "⚠️ No account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Information System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 1rem; }
        .sign-buttons { display: flex; gap: 10px; justify-content: center; margin-bottom: 15px; }
        .forgot { text-align: center; margin-top: 10px; }
    </style>
</head>
<body>
<div class="container mt-5" style="max-width: 500px;">
    <div class="card shadow-sm p-4 border">
        <h3 class="text-center text-primary mb-3">🔑 Forgot Password</h3>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="Enter your email">
            </div>
            <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
        </form>
        <div class="text-center mt-3">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</div>
</body>
</html>