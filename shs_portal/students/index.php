<?php
session_start();
include('../db/connect.php');
include('../header.php');

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

// Handle search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$classFilter = $_GET['class_id'] ?? '';

$query = "
    SELECT 
        s.student_id, 
        s.first_name, 
        s.last_name, 
        s.gender, 
        s.date_of_birth, 
        c.class_name, 
        s.address, 
        s.phone, 
        s.email,
		t.department_name,
        s.photo,
        p.full_name AS parent_name,
        p.relationship,
        p.phone AS parent_phone,
        p.email AS parent_email
    FROM students s
    LEFT JOIN parents p ON s.student_id = p.student_id
	LEFT JOIN classes c ON s.class_id = c.class_id
	LEFT JOIN department t on s.department_id = t.department_id
    WHERE 1=1
";

$params = [];
if ($search !== '') {
    $query .= " AND (LOWER(s.first_name) LIKE LOWER($1) OR LOWER(s.last_name) LIKE LOWER($1) OR LOWER(p.full_name) LIKE LOWER($1) OR s.phone LIKE $1 OR p.phone LIKE $1)";
    $params[] = "%$search%";
}
if ($classFilter !== '') {
    $query .= count($params) > 0 ? " AND s.class_id = $" . (count($params) + 1) : " AND s.class_id = $1";
    $params[] = $classFilter;
}

$query .= " ORDER BY s.student_id ASC";
$result = pg_query_params($conn, $query, $params);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        th { background-color: #007bff; color: #fff;  font-size: 12px }
        a { text-decoration: none; }
        .add { background: green; color: white; padding: px 12px; border-radius: 4px; }
        .edit { background: orange; color: white; padding: 4px 8px; border-radius: 4px;display: inline-block;
        font-size: 13px;}
        .delete { background: red; color: white; padding: 6px 10px; border-radius: 4px;display: inline-block;
        font-size: 13px;}
        
	
    </style>
</head>
<body>

<div class="main-content p-4">
    <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">🎓 Student List</h3>
            <a href="add.php" class="add">+ Add Student</a>
        </div>

        <!-- 🔍 Search and Filter -->
        <form class="row mb-4" method="GET">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name, phone..." value="<?= htmlspecialchars($search) ?>">
            </div>
          <div class="col-md-3">
    <select name="class_id" class="form-select">
        <option value="">Filter by Class</option>

        <?php
        $classes = pg_query($conn, "SELECT class_id, class_name FROM classes ORDER BY class_name");

        while ($row = pg_fetch_assoc($classes)) {
            $selected = ($row['class_id'] == $classFilter) ? 'selected' : '';
            echo "<option value='{$row['class_id']}' $selected>{$row['class_name']}</option>";
        }
        ?>
    </select>
</div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit">Search</button>
            </div>
            <div class="col-md-2">
                <a href="index.php" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>

        <table class="table table-striped table-bordered align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Photo</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Class</th>
                    <th>Department</th>
                    <th>Parent / Guardian</th>
                    <th>Relationship</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (pg_num_rows($result) > 0) {
                    while ($row = pg_fetch_assoc($result)) {
                        $fullname = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);

                        // ✅ Proper bytea handling for PostgreSQL
                        if (!empty($row['photo'])) {
                            $photoData = pg_unescape_bytea($row['photo']);
                            $photoSrc = 'data:image/jpeg;base64,' . base64_encode($photoData);
                        } else {
                            $photoSrc = 'https://via.placeholder.com/60x60?text=No+Photo';
                        }

                        echo "<tr>
                            <td>{$row['student_id']}</td>
                            <td>
                                <img src='{$photoSrc}' alt='Photo' style='width:60px; height:60px; object-fit:cover; border-radius:50%; border:2px solid #0d6efd;'>
                            </td>
                            <td>{$fullname}</td>
                            <td>{$row['gender']}</td>
                            <td>{$row['class_name']}</td>
                            <td>{$row['department_name']}</td>
                            <td>{$row['parent_name']}</td>
                            <td>{$row['relationship']}</td>
                            <td>
                                <a href='edit.php?id={$row['student_id']}' class='edit'>Edit</a>
                                <a href='delete.php?id={$row['student_id']}' class='delete' onclick='return confirm(\"Delete this student?\")'>Delete</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='13' class='text-center text-muted'>No matching records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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

</body>
</html>
