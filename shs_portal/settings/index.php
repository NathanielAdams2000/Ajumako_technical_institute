<?php
session_start();
include('../db/connect.php');
include('../header.php');

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch existing settings (assuming one row)
$query = "SELECT * FROM settings LIMIT 1";
$result = pg_query($conn, $query);
$settings = pg_fetch_assoc($result);

$success = $error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $school_name = $_POST['school_name'];
    $motto = $_POST['motto'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $academic_year = $_POST['academic_year'];
    $current_term = $_POST['current_term'];

    // Handle logo upload
    $logoData = null;
    if (!empty($_FILES['logo']['tmp_name'])) {
        $logoData = file_get_contents($_FILES['logo']['tmp_name']);
    }

    if ($settings) {
        // Update existing settings
        if ($logoData) {
            $logoHex = bin2hex($logoData);
            $update = "UPDATE settings SET 
                school_name=$1, motto=$2, address=$3, phone=$4, email=$5, 
                academic_year=$6, current_term=$7, logo=decode($8,'hex')";
            $params = [$school_name, $motto, $address, $phone, $email, $academic_year, $current_term, $logoHex];
        } else {
            $update = "UPDATE settings SET 
                school_name=$1, motto=$2, address=$3, phone=$4, email=$5, 
                academic_year=$6, current_term=$7";
            $params = [$school_name, $motto, $address, $phone, $email, $academic_year, $current_term];
        }

        $res = pg_query_params($conn, $update, $params);
        if ($res) {
            $success = "✅ Settings updated successfully!";
        } else {
            $error = "❌ Error updating settings: " . pg_last_error($conn);
        }

    } else {
        // Insert new settings
        $logoHex = $logoData ? bin2hex($logoData) : null;
        $insert = "INSERT INTO settings 
            (school_name, motto, address, phone, email, academic_year, current_term, logo)
            VALUES ($1, $2, $3, $4, $5, $6, $7, decode($8,'hex'))";
        $params = [$school_name, $motto, $address, $phone, $email, $academic_year, $current_term, $logoHex];
        $res = pg_query_params($conn, $insert, $params);

        if ($res) {
            $success = "✅ Settings saved successfully!";
        } else {
            $error = "❌ Error saving settings: " . pg_last_error($conn);
        }
    }
}
?>

<div class="main-content p-4">
    <div class="card shadow-sm p-4 border">
        <h3 class="text-primary mb-3">⚙️ System Settings</h3>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row g-3 align-items-center">
                <div class="col-md-2 text-center">
                    <div class="position-relative mx-auto" 
                        style="width:120px; height:120px; border-radius:50%; border:2px solid #007bff; overflow:hidden; cursor:pointer;"
                        id="logoContainer">
                        <img id="preview" 
                            src="<?= $settings && $settings['logo'] ? 'data:image/jpeg;base64,'.base64_encode($settings['logo']) : '' ?>" 
                            style="width:100%; height:100%; object-fit:cover; <?= $settings && $settings['logo'] ? '' : 'display:none;' ?>">
                        <div id="placeholder" class="text-muted fw-semibold" style="font-size:0.8rem; <?= $settings && $settings['logo'] ? 'display:none;' : '' ?>">Upload Logo</div>
                    </div>
                    <input type="file" name="logo" id="logo" accept="image/*" style="display:none;">
                </div>

                <div class="col-md-10">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>School Name</label>
                            <input type="text" name="school_name" class="form-control border" required value="<?= $settings['school_name'] ?? '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label>Motto</label>
                            <input type="text" name="motto" class="form-control border" value="<?= $settings['motto'] ?? '' ?>">
                        </div>
                        <div class="col-md-12">
                            <label>Address</label>
                            <textarea name="address" class="form-control border" rows="2"><?= $settings['address'] ?? '' ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control border" value="<?= $settings['phone'] ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control border" value="<?= $settings['email'] ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label>Academic Year</label>
                            <input type="text" name="academic_year" class="form-control border" placeholder="e.g. 2025/2026" value="<?= $settings['academic_year'] ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label>Current Term</label>
                            <select name="current_term" class="form-select border">
                                <option value="">-- Select Term --</option>
                                <option value="1st Term" <?= ($settings['current_term'] ?? '') == '1st Term' ? 'selected' : '' ?>>1st Term</option>
                                <option value="2nd Term" <?= ($settings['current_term'] ?? '') == '2nd Term' ? 'selected' : '' ?>>2nd Term</option>
                                <option value="3rd Term" <?= ($settings['current_term'] ?? '') == '3rd Term' ? 'selected' : '' ?>>3rd Term</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary px-5">💾 Save Settings</button>
            </div>
        </form>
    </div>
</div>

<script>
const logoContainer = document.getElementById('logoContainer');
const logoInput = document.getElementById('logo');
const preview = document.getElementById('preview');
const placeholder = document.getElementById('placeholder');

logoContainer.addEventListener('click', () => logoInput.click());
logoInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (event) => {
        preview.src = event.target.result;
        preview.style.display = 'block';
        placeholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
});
</script>
