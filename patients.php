<?php
session_start();
// Security Check: Agar login nahi kiya toh wapis login page par bhejo
if(!isset($_SESSION['logged_in'])){ 
    header("Location: login.php"); 
    exit(); 
}

$conn = mysqli_connect("localhost", "root", "", "hospital_db");

// Role check kar rahe hain ke Admin hai ya Doctor
$role = $_SESSION['role']; 
$logged_in_doc = $_SESSION['doctor_name']; 

// 1. STATUS UPDATE LOGIC
if(isset($_GET['update_status']) && isset($_GET['id'])){
    $id = $_GET['id'];
    $status = $_GET['update_status'];
    mysqli_query($conn, "UPDATE appointments SET status='$status' WHERE id='$id'");
    header("Location: patients.php?msg=Patient Status Updated to $status");
}

// 2. DELETE LOGIC (Sirf Admin Delete kar sakta hai)
if(isset($_GET['delete_id']) && $role == 'admin'){
    $id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM appointments WHERE id='$id'");
    header("Location: patients.php?msg=Appointment Record Deleted");
}

// 3. SMART FILTER LOGIC (Admin sab ko filter karega, Doctor sirf apni date filter karega)
if($role == 'doctor'){
    // Doctor sirf apne patients dekhega
    $where_clause = "WHERE doctor_name='$logged_in_doc'";
} else {
    // Admin sabke patients dekhega
    $where_clause = "WHERE 1=1";
}

if(isset($_POST['filter'])){
    if(!empty($_POST['f_date'])) {
        $f_date = $_POST['f_date'];
        $where_clause .= " AND app_date='$f_date'";
    }
    // Doctor filter sirf Admin use kar sakta hai
    if(!empty($_POST['f_doc']) && $role == 'admin') {
        $f_doc = $_POST['f_doc'];
        $where_clause .= " AND doctor_name='$f_doc'";
    }
}
if(isset($_POST['reset'])){
    header("Location: patients.php"); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointments | CareSync</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; background: #f4f7f6; min-height: 100vh; }
        
        /* SIDEBAR DESIGN */
        .sidebar { width: 260px; background: #1a2a3a; color: white; padding: 20px; position: fixed; height: 100vh; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
        .sidebar h2 { text-align: center; margin-bottom: 40px; color: #3498db; border-bottom: 1px solid #2c3e50; padding-bottom: 15px; letter-spacing: 1px; font-size: 22px; }
        .sidebar a { color: #bdc3c7; text-decoration: none; display: block; padding: 12px 15px; border-radius: 8px; margin-bottom: 8px; transition: 0.3s; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background: #3498db; color: white; padding-left: 20px; }
        .logout-btn { background: #e74c3c !important; color: white !important; margin-top: 50px !important; text-align: center; }
        .logout-btn:hover { background: #c0392b !important; padding-left: 15px !important; }
        
        /* MAIN CONTENT AREA */
        .main-content { margin-left: 260px; flex: 1; padding: 40px; }
        .header-title { color: #2c3e50; margin-bottom: 25px; font-size: 28px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        
        /* CARD DESIGN */
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .card h3 { color: #2c3e50; margin-bottom: 20px; font-size: 18px; }
        
        /* FILTER FORM */
        .filter-form { display: flex; gap: 15px; align-items: flex-end; margin-bottom: 20px; background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #eee; }
        .filter-form label { font-size: 13px; color: #7f8c8d; font-weight: bold; margin-bottom: 5px; display: block; }
        .filter-form input, .filter-form select { padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; outline: none; width: 220px; }
        .btn-filter { background: #3498db; color: white; border: none; padding: 11px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn-reset { background: #95a5a6; color: white; border: none; padding: 11px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }

        /* TABLE DESIGN */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: white; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #7f8c8d; font-size: 13px; text-transform: uppercase; }
        .status { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; display: inline-block; }
        .s-Pending { background: #fff3e0; color: #e65100; }
        .s-Completed { background: #e8f5e9; color: #2e7d32; }
        .s-Cancelled { background: #ffebee; color: #c62828; }
        
        .action-btn { padding: 6px 10px; border-radius: 5px; text-decoration: none; font-size: 12px; font-weight: bold; margin-right: 5px; display: inline-block; margin-bottom: 5px; }
        .btn-done { border: 1px solid #2ecc71; color: #2ecc71; }
        .btn-done:hover { background: #2ecc71; color: white; }
        .btn-cancel { border: 1px solid #e67e22; color: #e67e22; }
        .btn-cancel:hover { background: #e67e22; color: white; }
        .btn-del { border: 1px solid #e74c3c; color: #e74c3c; }
        .btn-del:hover { background: #e74c3c; color: white; }
        .msg { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; border-left: 5px solid #28a745; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>🏥 CareSync Portal</h2>
        
        <?php if($role == 'admin'): ?>
            <a href="admin.php">📊 Dashboard</a>
            <a href="doctors.php">👨‍⚕️ Manage Doctors</a>
        <?php endif; ?>
        
        <a href="patients.php" class="active">👥 Appointments</a>
        <a href="index.php" target="_blank">🌐 View Website</a>
        <a href="logout.php" class="logout-btn">🔒 Logout</a>
    </div>

    <div class="main-content">
        <h1 class="header-title">
            <?php echo ($role == 'admin') ? "All Patient Appointments" : "Welcome, Dr. $logged_in_doc - Your Patients"; ?>
        </h1>
        
        <?php if(isset($_GET['msg'])) { echo "<div class='msg'>✅ {$_GET['msg']}</div>"; } ?>

        <div class="card">
            <h3>🔍 Filter Records</h3>
            <form method="POST" class="filter-form">
                <div>
                    <label>Filter by Date:</label>
                    <input type="date" name="f_date" value="<?php echo isset($_POST['f_date']) ? $_POST['f_date'] : ''; ?>">
                </div>
                
                <?php if($role == 'admin'): ?>
                <div>
                    <label>Filter by Doctor:</label>
                    <select name="f_doc">
                        <option value="">-- All Doctors --</option>
                        <?php
                        $docs = mysqli_query($conn, "SELECT DISTINCT name FROM doctors");
                        while($d = mysqli_fetch_assoc($docs)){
                            $sel = (isset($_POST['f_doc']) && $_POST['f_doc'] == $d['name']) ? 'selected' : '';
                            echo "<option value='{$d['name']}' $sel>Dr. {$d['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <?php endif; ?>

                <div>
                    <button type="submit" name="filter" class="btn-filter">Apply Filter</button>
                    <button type="submit" name="reset" class="btn-reset">Reset</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>📋 Booking Records</h3>
            <table>
                <tr>
                    <th>Ticket ID</th>
                    <th>Patient Name</th>
                    <th>Doctor & Date</th>
                    <th>Fees</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php
                $query = "SELECT * FROM appointments $where_clause ORDER BY id DESC";
                $res = mysqli_query($conn, $query);
                
                if(mysqli_num_rows($res) > 0) {
                    while($row = mysqli_fetch_assoc($res)) {
                        $f_date = date('d M Y', strtotime($row['app_date']));
                        $status_class = "s-" . $row['status'];
                        
                        echo "<tr>
                            <td><strong>#TK-{$row['id']}</strong></td>
                            <td style='font-size:16px; font-weight:bold; color:#2c3e50;'>{$row['patient_name']}</td>
                            <td>
                                <div style='color:#3498db; font-weight:bold;'>Dr. {$row['doctor_name']}</div>
                                <div style='font-size:12px; color:#7f8c8d; margin-top:3px;'>📅 $f_date</div>
                            </td>
                            <td><strong style='color:#27ae60;'>Rs. {$row['fees']}</strong></td>
                            <td><span class='status $status_class'>{$row['status']}</span></td>
                            <td>";
                            
                            if($row['status'] == 'Pending') {
                                echo "<a href='patients.php?update_status=Completed&id={$row['id']}' class='action-btn btn-done'>✔ Complete</a> ";
                                echo "<a href='patients.php?update_status=Cancelled&id={$row['id']}' class='action-btn btn-cancel'>✖ Cancel</a><br>";
                            }
                            
                            // Delete button sirf Admin ko nazar aayega
                            if($role == 'admin'){
                                echo "<a href='patients.php?delete_id={$row['id']}' class='action-btn btn-del' onclick=\"return confirm('Delete this record?');\">🗑️ Delete</a>";
                            }
                            echo "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center; padding:30px; color:#7f8c8d;'>No appointments found.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>