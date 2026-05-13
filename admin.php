<?php
session_start();

// 🔒 SECURITY LOCK: Agar user login nahi hai ya uska role 'admin' nahi hai, toh wapis login page par bhej do!
if(!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin'){ 
    header("Location: login.php"); 
    exit(); 
}

$conn = mysqli_connect("localhost", "root", "", "hospital_db");

// --- DASHBOARD ANALYTICS CALCULATION ---
// 1. Total Doctors
$res_docs = mysqli_query($conn, "SELECT COUNT(*) as total FROM doctors");
$total_docs = mysqli_fetch_assoc($res_docs)['total'];

// 2. Total Appointments (All time)
$res_apps = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments");
$total_apps = mysqli_fetch_assoc($res_apps)['total'];

// 3. Today's Appointments
$today = date('Y-m-d');
$res_today = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments WHERE app_date='$today'");
$today_apps = mysqli_fetch_assoc($res_today)['total'];

// 4. Total Earnings (Sirf unka jinka status 'Completed' hai)
$res_rev = mysqli_query($conn, "SELECT SUM(fees) as total FROM appointments WHERE status='Completed'");
$revenue = mysqli_fetch_assoc($res_rev)['total'];
if($revenue == null) { $revenue = 0; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Admin Panel</title>
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
        .header-title { color: #2c3e50; margin-bottom: 10px; font-size: 28px; }
        .subtitle { color: #7f8c8d; margin-bottom: 30px; font-size: 15px; border-bottom: 2px solid #ddd; padding-bottom: 15px; }
        
        /* ANALYTICS CARDS (4 Boxes) */
        .dashboard-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid #3498db; display: flex; align-items: center; justify-content: space-between; }
        .stat-card.green { border-left-color: #2ecc71; }
        .stat-card.orange { border-left-color: #e67e22; }
        .stat-card.purple { border-left-color: #9b59b6; }
        
        .stat-info h3 { font-size: 32px; color: #2c3e50; margin-bottom: 5px; }
        .stat-info p { color: #7f8c8d; font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-icon { font-size: 40px; opacity: 0.2; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>🏥 CareSync Portal</h2>
        <a href="admin.php" class="active">📊 Dashboard</a>
        <a href="doctors.php">👨‍⚕️ Manage Doctors</a>
        <a href="patients.php">👥 Appointments</a>
        <a href="index.php" target="_blank">🌐 View Website</a>
        <a href="logout.php" class="logout-btn">🔒 Logout</a>
    </div>

    <div class="main-content">
        <h1 class="header-title">Welcome to Control Panel</h1>
        <p class="subtitle">Here is the summary of your hospital operations.</p>
        
        <div class="dashboard-cards">
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $total_docs; ?></h3>
                    <p>Total Doctors</p>
                </div>
                <div class="stat-icon">👨‍⚕️</div>
            </div>

            <div class="stat-card green">
                <div class="stat-info">
                    <h3>Rs. <?php echo number_format($revenue); ?></h3>
                    <p>Total Earnings</p>
                </div>
                <div class="stat-icon">💰</div>
            </div>

            <div class="stat-card orange">
                <div class="stat-info">
                    <h3><?php echo $today_apps; ?></h3>
                    <p>Today's Patients</p>
                </div>
                <div class="stat-icon">📅</div>
            </div>

            <div class="stat-card purple">
                <div class="stat-info">
                    <h3><?php echo $total_apps; ?></h3>
                    <p>Total Bookings</p>
                </div>
                <div class="stat-icon">👥</div>
            </div>

        </div>
    </div>

</body>
</html>