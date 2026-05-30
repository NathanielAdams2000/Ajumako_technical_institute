<?php
session_start();
include('db/connect.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate input
    if (empty($username) || empty($email) || empty($password)) {
        $error = "⚠️ All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ Invalid email format!";
    } elseif ($password !== $confirm_password) {
        $error = "❌ Passwords do not match!";
    } else {
        // Check if username or email already exists
        $check_query = "SELECT * FROM users WHERE username = $1 OR email = $2";
        $check_result = pg_query_params($conn, $check_query, array($username, $email));

        if (pg_num_rows($check_result) > 0) {
            $error = "⚠️ Username or Email already exists!";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $insert_query = "INSERT INTO users (username, email, password) VALUES ($1, $2, $3)";
            $result = pg_query_params($conn, $insert_query, array($username, $email, $hashed_password));

            if ($result) {
                $success = "✅ Account created successfully! You can now <a href='login.php'>login</a>.";
            } else {
                $error = "❌ Error: " . pg_last_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account - Student Information System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 1rem; }
        .sign-buttons {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }
        .sign-buttons a {
            border-radius: 0;
            flex: 1;
            text-align: center;
        }
        .sign-buttons a:first-child {
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
        }
        .sign-buttons a:last-child {
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow p-4">
                <h3 class="text-center mb-3">🎓 Student Information System</h3>
                <h6 class="text-center mb-3 text-muted">Sign in or create an account</h6>
                <div class="sign-buttons">
                    <a href="login.php" class="btn btn-outline-primary">Sign In</a>
                    <a href="register.php" class="btn btn-outline-primary">Sign Up</a>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Sign Up</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
