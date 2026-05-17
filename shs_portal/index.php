<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome - Student Information System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #007bff, #6610f2);
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
        }
        .hero {
            text-align: center;
            padding: 80px 20px;
        }
        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
        }
        .hero p {
            font-size: 1.2rem;
            margin-top: 15px;
            color: #f8f9fa;
        }
        .btn-main {
            background-color: #ffc107;
            color: #000;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 30px;
            transition: 0.3s;
        }
        .btn-main:hover {
            background-color: #ffcd39;
            transform: scale(1.05);
        }
        .features {
            background-color: #fff;
            color: #333;
            padding: 60px 20px;
        }
        .feature-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        footer {
            margin-top: auto;
            background: rgba(0,0,0,0.2);
            padding: 15px;
            text-align: center;
            color: #fff;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<!-- ✅ Navigation Bar -->
<header class="p-3 mb-3 border-bottom">
    <div class="container d-flex flex-wrap justify-content-between align-items-center">
        <a href="#" class="d-flex align-items-center mb-2 mb-lg-0 text-white text-decoration-none">
            <h4 class="fw-bold">🎓 SIS Portal</h4>
        </a>
        <div>
            <a href="login.php" class="btn btn-light me-2">Login</a>
            <a href="register.php" class="btn btn-outline-light">Register</a>
        </div>
    </div>
</header>

<!-- ✅ Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Welcome to the Student Information System</h1>
        <p>Manage student records, classes, and academic data all in one place — securely and efficiently.</p>
        <a href="login.php" class="btn btn-main mt-4">Get Started</a>
    </div>
</section>

<!-- ✅ Features Section -->
<section class="features">
    <div class="container text-center">
        <h2 class="fw-bold mb-5 text-primary">Why Choose Our System?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card feature-card p-4">
                    <h5>📚 Student Management</h5>
                    <p>Easily register, update, and track student information with a modern interface.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card p-4">
                    <h5>👨‍🏫 Teacher Dashboard</h5>
                    <p>Empower teachers with tools to record attendance, grades, and performance reports.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card p-4">
                    <h5>📊 Reports & Insights</h5>
                    <p>Generate instant reports and analytics to monitor school performance and progress.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ✅ Footer -->
<footer>
    © <?= date('Y') ?> POSA | Powered by . All rights reserved.
</footer>

</body>
</html>
