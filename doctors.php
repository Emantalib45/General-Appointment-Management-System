<?php
session_start();

// 🔒 SECURITY LOCK: Agar user login nahi hai ya uska role 'admin' nahi hai, toh wapis bhej do!
if(!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin'){ 
    header("Location: login.php"); 
    exit(); 
}

$conn = mysqli_connect("localhost", "root", "", "hospital_db");

// Naya Doctor aur Date Add Karne ka Logic
if(isset($_POST['add_doc'])){
    $n = mysqli_real_escape_string($conn, $_POST['name']);
    $s = mysqli_real_escape_string($conn, $_POST['spec']);
    $f = $_POST['fees'];
    $t = mysqli_real_escape_string($conn, $_POST['timing']);
    $date = mysqli_real_escape_string($conn, $_POST['available_date']); 
    
    // 1. Doctor ka schedule database mein save karein
    mysqli_query($conn, "INSERT INTO doctors (name, specialization, fees, timing, available_days, status) VALUES ('$n', '$s', '$f', '$t', '$date', 'Available')");
    
    // 2. 🤖 SMART AUTO-USER CREATION LOGIC
    // Check karein ke is naam ka doctor 'users' table mein pehle se majood hai ya nahi
    $check_user = mysqli_query($conn, "SELECT * FROM users WHERE doctor_name='$n'");
    
    if(mysqli_num_rows($check_user) == 0) {
        // Agar account nahi hai, toh Naya Account banayen!
        // Username banane ke liye name se spaces aur dots (.) hata kar chotay lafzon mein convert karein
        $username = strtolower(str_replace(array(' ', '.'), '', $n)); 
        
        // Password uske username ke aage '123' laga kar banega
        $password = $username . "123"; 
        
        mysqli_query($conn, "INSERT INTO users (username, password, role, doctor_name) VALUES ('$username', '$password', 'doctor', '$n')");
    }

    header("Location: doctors.php?msg=Doctor Schedule Added (Login auto-created if new)");
    exit();
}

// Doctor ki chutti/schedule Delete karne ka logic
if(isset($_GET['delete'])){
    $del_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM doctors WHERE id='$del_id'");
    header("Location: doctors.php?msg=Schedule Deleted Successfully");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Doctors | Admin Panel</title>
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
        
        /* FORM GRID */
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end; }
        label { font-size: 13px; color: #7f8c8d; font-weight: bold; margin-bottom: 5px; display: block; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; outline: none; transition: 0.3s; }
        input:focus { border-color: #3498db; box-shadow: 0 0 5px rgba(52, 152, 219, 0.3); }
        button.btn-add { background: #2ecc71; color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 15px; transition: 0.3s; }
        button.btn-add:hover { background: #27ae60; }
        
        /* TABLE DESIGN */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: white; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #7f8c8d; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:hover { background: #fcfcfc; }
        .tag-date { background: #e1f5fe; color: #01579b; padding: 5px 10px; border-radius: 4px; font-size: 13px; font-weight: bold; display: inline-block; }
        .tag-time { background: #fff3e0; color: #e65100; padding: 5px 10px; border-radius: 4px; font-size: 13px; font-weight: bold; display: inline-block; margin-top: 5px; }
        .btn-delete { background: #ffebee; color: #c62828; padding: 8px 12px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 13px; transition: 0.3s; }
        .btn-delete:hover { background: #c62828; color: white; }
        
        .msg { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; border-left: 5px solid #28a745; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>🏥 CareSync Portal</h2>
        <a href="admin.php">📊 Dashboard</a>
        <a href="doctors.php" class="active">👨‍⚕️ Manage Doctors</a>
        <a href="patients.php">👥 Appointments</a>
        <a href="index.php" target="_blank">🌐 View Website</a>
        <a href="logout.php" class="logout-btn">🔒 Logout</a>
    </div>

    <div class="main-content">
        <h1 class="header-title">Doctor Schedule Management</h1>
        
        <?php if(isset($_GET['msg'])) { echo "<div class='msg'>✅ {$_GET['msg']}</div>"; } ?>
        
        <div class="card" style="background: #e1f5fe; border-left: 5px solid #3498db;">
            <p style="color: #01579b; font-size: 14px;"><b>💡 Smart Feature:</b> When you add a new doctor's schedule, the system will automatically create their login account! <br><i>(Username: doctor's name without spaces, Password: username+123)</i></p>
        </div>

        <div class="card">
            <h3>➕ Add New Availability Slot</h3>
            <form method="POST" class="form-grid">
                <div>
                    <label>Doctor Name (e.g. Dr. Salman)</label>
                    <input type="text" name="name" placeholder="Dr. Salman" required>
                </div>
                <div>
                    <label>Specialization</label>
                    <input type="text" name="spec" placeholder="Cardiologist" required>
                </div>
                <div>
                    <label>Select Date</label>
                    <input type="date" name="available_date" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div>
                    <label>Timing / Shift</label>
                    <input type="text" name="timing" placeholder="05:00 PM - 09:00 PM" required>
                </div>
                <div>
                    <label>Consultation Fee (Rs.)</label>
                    <input type="number" name="fees" placeholder="2000" required>
                </div>
                <div>
                    <button type="submit" name="add_doc" class="btn-add">Save Schedule</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>📋 Current Active Schedules</h3>
            <table>
                <tr>
                    <th>Doctor Details</th>
                    <th>Scheduled Date & Time</th>
                    <th>Fees</th>
                    <th>Action</th>
                </tr>
                <?php
                $today = date('Y-m-d');
                $res = mysqli_query($conn, "SELECT * FROM doctors WHERE available_days >= '$today' ORDER BY available_days ASC");
                
                if(mysqli_num_rows($res) > 0) {
                    while($row = mysqli_fetch_assoc($res)) {
                        $f_date = date('d M Y', strtotime($row['available_days']));
                        $day_name = date('l', strtotime($row['available_days']));
                        
                        echo "<tr>
                            <td>
                                <div style='font-size:16px; font-weight:bold; color:#2c3e50;'>{$row['name']}</div>
                                <div style='font-size:13px; color:#7f8c8d; margin-top:3px;'>{$row['specialization']}</div>
                            </td>
                            <td>
                                <span class='tag-date'>📅 $f_date ($day_name)</span><br>
                                <span class='tag-time'>🕒 {$row['timing']}</span>
                            </td>
                            <td><strong style='color:#27ae60; font-size:16px;'>Rs. {$row['fees']}</strong></td>
                            <td>
                                <a href='doctors.php?delete={$row['id']}' class='btn-delete' onclick=\"return confirm('Are you sure you want to delete this schedule?');\">🗑️ Delete</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center; color:#7f8c8d; padding:20px;'>No upcoming doctor schedules found. Please add above.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>

</body>
</html>