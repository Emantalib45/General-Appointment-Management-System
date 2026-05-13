<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "hospital_db");

// 1. AUTO CREATE USERS TABLE (Aapko Database mein kuch nahi karna padega)
$create_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL,
    role VARCHAR(20) NOT NULL,
    doctor_name VARCHAR(100) NULL
)";
mysqli_query($conn, $create_table);

// 2. CREATE DEFAULT ACCOUNTS (Agar table khali hai)
$check = mysqli_query($conn, "SELECT * FROM users");
if(mysqli_num_rows($check) == 0) {
    // Ek Admin Account ban raha hai
    mysqli_query($conn, "INSERT INTO users (username, password, role, doctor_name) VALUES ('admin', 'admin123', 'admin', NULL)");
    // Ek Doctor Account ban raha hai (Aap isay baad mein change kar sakte hain)
    mysqli_query($conn, "INSERT INTO users (username, password, role, doctor_name) VALUES ('dr_ali', 'ali123', 'doctor', 'Ali')");
}

$msg = "";

// 3. LOGIN LOGIC
if(isset($_POST['login'])){
    $u = mysqli_real_escape_string($conn, $_POST['username']);
    $p = mysqli_real_escape_string($conn, $_POST['password']);
    
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$u' AND password='$p'");
    
    if(mysqli_num_rows($query) > 0){
        $user = mysqli_fetch_assoc($query);
        
        // Session mein user ka data save kar lo
        $_SESSION['logged_in'] = true;
        $_SESSION['role'] = $user['role']; // 'admin' ya 'doctor'
        $_SESSION['username'] = $user['username'];
        $_SESSION['doctor_name'] = $user['doctor_name']; // Agar doctor hai toh uska naam
        
        // Login ke baad patients dashboard par bhejo
        header("Location: patients.php");
        exit();
    } else {
        $msg = "<div class='error'>❌ Username ya Password ghalat hai!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Login | CareSync</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #1a2a3a; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 100%; max-width: 400px; text-align: center; }
        .login-box h2 { color: #2c3e50; margin-bottom: 10px; font-size: 24px; }
        .login-box p { color: #7f8c8d; font-size: 14px; margin-bottom: 30px; }
        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { display: block; font-size: 13px; font-weight: bold; color: #34495e; margin-bottom: 5px; }
        .input-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; outline: none; transition: 0.3s; }
        .input-group input:focus { border-color: #3498db; box-shadow: 0 0 5px rgba(52, 152, 219, 0.3); }
        .btn-login { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-login:hover { background: #2980b9; }
        .error { background: #ffebee; color: #c62828; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: bold; }
        .credentials-box { margin-top: 25px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px dashed #bdc3c7; text-align: left; font-size: 13px; color: #7f8c8d; }
    </style>
</head>
<body>

    <div class="login-box">
        <h2>🏥 CareSync Portal</h2>
        <p>Enter your credentials to access the system</p>
        
        <?php echo $msg; ?>
        
        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter username" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit" name="login" class="btn-login">Secure Login 🔒</button>
        </form>

        <div class="credentials-box">
            <strong>Testing Credentials:</strong><br><br>
            👔 <b>Admin:</b> User: <i>admin</i> | Pass: <i>admin123</i><br>
            👨‍⚕️ <b>Doctor:</b> User: <i>dr_ali</i> | Pass: <i>ali123</i>
        </div>
    </div>

</body>
</html>