<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}
$username = $_SESSION['user'];

$currentPage = basename($_SERVER['PHP_SELF']);


// 🔹 Convert filename to a readable title
switch ($currentPage) {
    case 'dashboard.php':
        $pageTitle = 'Dashboard';
        break;
    case 'index.php':
        if (strpos($_SERVER['PHP_SELF'], 'students') !== false)
            $pageTitle = 'Students';
        elseif (strpos($_SERVER['PHP_SELF'], 'teachers') !== false)
            $pageTitle = 'Teachers';
        elseif (strpos($_SERVER['PHP_SELF'], 'courses') !== false)
            $pageTitle = 'Courses';
        elseif (strpos($_SERVER['PHP_SELF'], 'class') !== false)
            $pageTitle = 'Classes';
        else
            $pageTitle = 'Home';
        break;
    case 'reports.php':
        $pageTitle = 'Reports';
        break;
    case 'settings.php':
        $pageTitle = 'Settings';
        break;
    default:
        $pageTitle = 'Student Information System';
        break;
}

?>





<!-- SIDEBAR -->




<!-- HEADER + SIDEBAR -->
 <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: Arial; margin: 40px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #007BFF; color: #fff; }
        a { text-decoration: none; padding: 6px 10px; border-radius: 4px; }
        .add { background: green; color: white; }
        .edit { background: orange; color: white; }
        .delete { background: red; color: white; }
		
		
		 /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 70px;
            background: linear-gradient(180deg, #007bff, #6610f2);
            color: #fff;
            transition: all 0.3s ease;
            overflow-x: hidden;
            z-index: 1050;
        }

        .sidebar:hover {
            width: 250px;
        }

        .sidebar h3 {
            text-align: center;
            font-weight: 700;
            margin: 20px 0;
            opacity: 0;
            transition: 0.3s;
        }

        .sidebar:hover h3 {
            opacity: 1;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: #fff;
            text-decoration: none;
            padding: 12px 20px;
            white-space: nowrap;
            transition: background 0.3s;
        }

        .sidebar a i {
            font-size: 20px;
            margin-right: 15px;
            min-width: 25px;
            text-align: center;
        }

        .sidebar a:hover, .sidebar a.active {
            background-color: rgba(255,255,255,0.2);
            border-left: 4px solid #ffc107;
        }

        .sidebar a span {
            opacity: 0;
            transition: opacity 0.3s;
        }

        .sidebar:hover a span {
            opacity: 1;
        }
		
		 /* Main content */
        .main-content {
            margin-left: 70px;
            transition: margin-left 0.3s;
            padding: 20px;
        }

        .sidebar:hover ~ .main-content {
            margin-left: 250px;
        }

        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
		
        /* ✅ Responsive Sidebar */
        @media (max-width: 992px) {
            .sidebar {
                width: 0;
                left: -250px;
            }

            .sidebar.active {
                left: 0;
                width: 250px;
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar h3, .sidebar a span {
                opacity: 1;
            }

            .sidebar:hover {
                width: 250px;
            }

            .toggle-btn {
                display: inline-block;
                cursor: pointer;
                font-size: 1.7rem;
                margin-right: 10px;
                border: none;
                background: none;
            }

            .overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.4);
                z-index: 1049;
                display: none;
            }

            .overlay.show {
                display: block;
            }
        }

        .toggle-btn {
            display: none;
        }
    </style>
</head>
<body>

<!-- ✅ Sidebar -->
<div class="sidebar" id="sidebar">
    <h3>🎓 SIS Portal</h3>
    <a href="/shs_portal/dashboard.php" class="<?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>">
        <i class="bi bi-house"></i><span> Dashboard</span>
    </a>
    <a href="/shs_portal/students/index.php" class="<?= ($currentPage == 'index.php' && strpos($_SERVER['PHP_SELF'], 'students') !== false) ? 'active' : '' ?>">
        <i class="bi bi-people"></i><span> Students Info</span>
    </a>
    <a href="/shs_portal/exams_score/index.php" class="<?= ($currentPage == 'index.php' && strpos($_SERVER['PHP_SELF'], 'exams_score') !== false) ? 'active' : '' ?>">
        <i class="bi bi-person-badge"></i><span> Exams Marks</span>
    </a>
    <a href="/shs_portal/class/index.php" class="<?= ($currentPage == 'index.php' && strpos($_SERVER['PHP_SELF'], 'class') !== false) ? 'active' : '' ?>">
        <i class="bi bi-building"></i><span> Classes</span>
    </a>
    <a href="/shs_portal/courses/index.php" class="<?= ($currentPage == 'index.php' && strpos($_SERVER['PHP_SELF'], 'courses') !== false) ? 'active' : '' ?>">
        <i class="bi bi-book"></i><span> Courses</span>
    </a>
    <a href="/shs_portal/report/report_list.php" class="<?= ($currentPage == 'report.php' && strpos($_SERVER['PHP_SELF'], 'report') !== false) ? 'active' : '' ?>">
        <i class="bi bi-bar-chart"></i><span> Reports</span>
    </a>
    <a href="/shs_portal/settings/index.php" class="<?= ($currentPage == 'settings.php') ? 'active' : '' ?>">
        <i class="bi bi-gear"></i><span> Settings</span>
    </a>
</div>


<!-- Overlay for mobile -->
<div class="overlay" id="overlay"></div>

<!-- ✅ Main Content -->
<div class="main-content">
    <nav class="navbar navbar-expand-lg navbar-light mb-4">
        <div class="container-fluid">
            <button class="toggle-btn" id="toggleBtn"><i class="bi bi-list"></i></button>
            <span class="navbar-brand fw-bold"><?= htmlspecialchars($pageTitle) ?></span>
            <div class="d-flex align-items-center ms-auto">
                <span class="me-3">👋 Welcome, <strong><?= htmlspecialchars($username) ?></strong></span>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

<script>
const toggleBtn = document.getElementById('toggleBtn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('show');
    });
}
if (overlay) {
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('show');
    });
}
</script>
