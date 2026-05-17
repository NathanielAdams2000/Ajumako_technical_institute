<?php
session_start();
include('db/connect.php'); // Ensure this connects to PostgreSQL

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Secure parameterized query
    $query = "SELECT * FROM users WHERE username = $1";
    $result = pg_query_params($conn, $query, array($username));

    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {
            $_SESSION['user'] = $row['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "❌ Invalid password!";
        }
    } else {
        $error = "⚠️ User not found!";
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
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow p-4 border">
                <h3 class="text-center mb-3">🎓 Student Information System</h3>
                <h6 class="text-center mb-3 text-muted">Sign in or create an account</h6>

                <div class="sign-buttons">
                    <a href="login.php" class="btn btn-outline-primary w-50">Sign In</a>
                    <a href="register.php" class="btn btn-outline-primary w-50">Sign Up</a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Login</button>

                    <div class="forgot">
                      <a href="reset.php">Forgot Password?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
