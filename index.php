<?php 
$conn = mysqli_connect("localhost", "root", "", "hospital_db");
$msg = "";
$print_btn = "";
$slip_html = "";

if(isset($_POST['book'])){
    $p = mysqli_real_escape_string($conn, $_POST['p_name']);
    $d = mysqli_real_escape_string($conn, $_POST['d_name']);
    $dt = $_POST['date']; 
    $day_of_week = date('l', strtotime($dt)); 

    $query = mysqli_query($conn, "SELECT fees, timing, specialization FROM doctors WHERE name='$d' AND available_days='$dt'");
    
    if (mysqli_num_rows($query) == 0) {
        $msg = "<div class='alert error'>❌ Security Error: Doctor is not available on this date.</div>";
    } else {
        $doc_data = mysqli_fetch_assoc($query);
        $fees = $doc_data['fees'];
        $insert = "INSERT INTO appointments (patient_name, doctor_name, app_date, fees, status) VALUES ('$p', '$d', '$dt', '$fees', 'Pending')";
        
        if(mysqli_query($conn, $insert)){
            $msg = "<div class='alert success'>✅ Appointment Confirmed for " . date('d M Y', strtotime($dt)) . "!</div>";
            $ticket_no = "TK-" . rand(10000, 99999);
            
            $print_btn = "<button type='button' onclick='window.print()' class='btn-print screen-only' style='width:100%;'>🖨️ Print Official Ticket</button>";
            
            $slip_html = "
            <div id='print-section'>
                <div class='slip-container'>
                    <div class='slip-header'>
                        <h2>🏥 CareSync Hospital</h2>
                        <p>123 Health Avenue, Medical District • Tel: +92 300 1234567</p>
                    </div>
                    <div class='slip-title'>
                        <h3>Official Appointment Ticket</h3>
                        <p>Ticket #: <b>$ticket_no</b></p>
                    </div>
                    <table class='slip-table'>
                        <tr><td class='label'>Patient Name:</td><td class='value'>$p</td></tr>
                        <tr><td class='label'>Consultant:</td><td class='value'>Dr. $d</td></tr>
                        <tr><td class='label'>Department:</td><td class='value'>{$doc_data['specialization']}</td></tr>
                        <tr><td class='label'>Date:</td><td class='value'>" . date('d M Y', strtotime($dt)) . " ($day_of_week)</td></tr>
                        <tr><td class='label'>Reporting Time:</td><td class='value time-highlight'>{$doc_data['timing']}</td></tr>
                    </table>
                    <div class='fee-box'>
                        <p>Consultation Fee</p>
                        <h2>Rs. " . number_format($fees) . "</h2>
                    </div>
                    <div class='slip-footer'>
                        <p>Please arrive 15 minutes before your scheduled time.</p>
                        <p>Valid for one-time consultation only.</p>
                        <div class='barcode'>||||| ||| ||||| || |||||||| ||||</div>
                    </div>
                </div>
            </div>";
        }
    }
}

// 1. Un Doctors ki list nikalna jinki future mein dates available hain (Dropdown ke liye)
$today = date('Y-m-d');
$docs_query = mysqli_query($conn, "SELECT DISTINCT name, specialization FROM doctors WHERE available_days >= '$today'");

// 2. Har Doctor ki dates ko JS format ke liye tayyar karna
$sched_query = mysqli_query($conn, "SELECT name, available_days FROM doctors WHERE available_days >= '$today'");
$doctorDates = [];
while($row = mysqli_fetch_assoc($sched_query)){
    $docName = $row['name'];
    $date = $row['available_days'];
    if(!isset($doctorDates[$docName])) {
        $doctorDates[$docName] = [];
    }
    $doctorDates[$docName][] = $date;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CareSync | Patient Portal</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', sans-serif; }
        body { background:#f0f4f8; padding:20px; }
        .screen-only { display:block; }
        #print-section { display: none; }
        .header { background:white; padding:20px; text-align:center; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:30px; color: #2c3e50; }
        .container { max-width:1100px; margin:auto; display:grid; grid-template-columns:1.5fr 1fr; gap:30px; }
        .box { background:white; padding:30px; border-radius:15px; box-shadow:0 5px 20px rgba(0,0,0,0.05); }
        .doc-card { border-bottom:1px solid #eee; padding:15px 0; display:flex; justify-content:space-between; align-items: center; }
        
        input, select, button { width:100%; padding:12px; margin-top:10px; border-radius:8px; border:1px solid #ddd; font-size:15px; display:block; outline: none; }
        
        /* Disabled input style taake user ko pata chale */
        input:disabled, select:disabled { background: #e9ecef; cursor: not-allowed; }
        
        button { background:#3498db; color:white; font-weight:bold; border:none; cursor:pointer; margin-top:20px; transition:0.3s; }
        button:hover { background:#2980b9; }
        .alert { padding:15px; border-radius:8px; margin-bottom:15px; text-align:center; font-weight:bold; }
        .error { color:#c62828; background:#ffebee; }
        .success { color:#2e7d32; background:#e8f5e9; }
        .btn-print { background:#2ecc71; margin-top:15px; margin-bottom: 15px; font-size: 16px; padding: 15px; }
        .btn-print:hover { background:#27ae60; }
        .tag { font-size:11px; padding:4px 8px; border-radius:4px; font-weight:bold; background:#e1f5fe; color:#01579b; margin-right:5px; display:inline-block; margin-top:4px; }
        
        .doctor-available-date {
            background-color: #2ecc71 !important;
            color: white !important;
            border-color: #27ae60 !important;
            font-weight: bold;
            border-radius: 5px !important;
        }

        @media print {
            .screen-only, .header, .container { display: none !important; }
            body { background: white; padding: 0; margin: 0; }
            #print-section { display: flex !important; justify-content: center; padding-top: 20px; }
            .slip-container { width: 100%; max-width: 400px; border: 2px solid #2c3e50; border-radius: 10px; padding: 25px; color: #333; background: #fff; margin: 0 auto; }
            .slip-header { text-align: center; border-bottom: 2px solid #3498db; padding-bottom: 15px; margin-bottom: 15px; }
            .slip-header h2 { margin: 0; color: #2c3e50; font-size: 22px; }
            .slip-header p { margin: 5px 0 0; color: #7f8c8d; font-size: 12px; }
            .slip-title { text-align: center; margin-bottom: 20px; }
            .slip-title h3 { margin: 0; color: #3498db; text-transform: uppercase; font-size: 16px; letter-spacing: 1px; }
            .slip-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px; }
            .slip-table td { padding: 10px 0; border-bottom: 1px solid #ecf0f1; }
            .slip-table .label { color: #7f8c8d; width: 45%; }
            .slip-table .value { font-weight: bold; color: #2c3e50; text-align: right; }
            .time-highlight { color: #e65100 !important; }
            .fee-box { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 20px; border: 1px dashed #bdc3c7; }
            .fee-box p { margin: 0; color: #7f8c8d; font-size: 13px; text-transform: uppercase; }
            .fee-box h2 { margin: 5px 0 0; color: #27ae60; font-size: 24px; }
            .slip-footer { text-align: center; border-top: 2px solid #ecf0f1; padding-top: 15px; }
            .slip-footer p { margin: 2px 0; font-size: 11px; color: #95a5a6; }
            .barcode { margin-top: 15px; font-family: 'Courier New', Courier, monospace; font-size: 24px; letter-spacing: 3px; color: #333; }
        }
    </style>
</head>
<body>
    <div class="header screen-only"><h1>🏥 CareSync Patient Portal</h1></div>
    
    <div class="container screen-only">
        <div class="box">
            <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">📅 Upcoming Available Slots</h3>
            <?php
            $res = mysqli_query($conn, "SELECT * FROM doctors WHERE available_days >= '$today' ORDER BY available_days ASC");
            if(mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)){
                    $f_date = date('d M Y', strtotime($row['available_days']));
                    $day_name = date('l', strtotime($row['available_days']));
                    echo "<div class='doc-card'>
                            <div>
                                <div style='font-weight:bold; font-size:18px; color: #2c3e50;'>{$row['name']}</div>
                                <div style='color:#3498db; font-size:14px; margin-bottom:4px;'>{$row['specialization']}</div>
                                <span class='tag'>📅 $f_date ($day_name)</span>
                                <span class='tag' style='background:#fff3e0; color:#e65100;'>🕒 {$row['timing']}</span>
                            </div>
                            <div style='font-weight:bold; color:#2ecc71; font-size: 18px;'>Rs. {$row['fees']}</div>
                          </div>";
                }
            } else {
                echo "<p style='color:#7f8c8d; text-align:center;'>No upcoming slots available at the moment.</p>";
            }
            ?>
        </div>

        <div class="box">
            <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">📝 Secure Booking</h3>
            
            <?php echo $msg; echo $print_btn; ?>
            
            <form method="POST">
                <input type="text" name="p_name" placeholder="Patient Full Name" required>
                
                <label style="font-size:13px; color:#666; margin-top:15px; display:block; font-weight: bold;">1. Select Consultant Doctor:</label>
                <select name="d_name" id="doctor_select" required onchange="loadDoctorCalendar()">
                    <option value="" disabled selected>-- Choose a Doctor --</option>
                    <?php
                    if(mysqli_num_rows($docs_query) > 0) {
                        while($doc = mysqli_fetch_assoc($docs_query)){
                            echo "<option value='{$doc['name']}'>{$doc['name']} ({$doc['specialization']})</option>";
                        }
                    }
                    ?>
                </select>

                <label style="font-size:13px; color:#666; margin-top:15px; display:block; font-weight: bold;">2. Select Visit Date:</label>
                <input type="text" id="visit_date" name="date" placeholder="Please select a doctor first..." style="background: white;" readonly required disabled>
                
                <button type="submit" name="book">Confirm Booking</button>
            </form>
        </div>
    </div>

    <?php echo $slip_html; ?>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // PHP se saare doctors aur unki dates ka data JavaScript mein laya ja raha hai
        const doctorDates = <?php echo json_encode($doctorDates); ?>;
        let fpInstance = null;

        function loadDoctorCalendar() {
            const selectedDoctor = document.getElementById('doctor_select').value;
            const dateInput = document.getElementById('visit_date');

            if (!selectedDoctor) return;

            // Date input ko active karo
            dateInput.disabled = false;
            dateInput.placeholder = "Click to select highlighted date...";
            dateInput.value = ""; // Agar pehle se koi date select thi toh usay clear kar do
            dateInput.style.cursor = "pointer";

            // Us specific doctor ki dates nikalo
            const availableDates = doctorDates[selectedDoctor] || [];

            // Agar pehle se koi calendar chal raha hai toh usay khatam karo
            if (fpInstance) {
                fpInstance.destroy();
            }

            // Naya Calendar load karo SIRF doctor ki dates ke sath
            fpInstance = flatpickr("#visit_date", {
                enable: availableDates, // MAGIC: Sirf yehi dates click ho sakengi!
                disableMobile: "true",
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                    let d = dayElem.dateObj;
                    let m = ('0' + (d.getMonth() + 1)).slice(-2);
                    let day = ('0' + d.getDate()).slice(-2);
                    let dateString = d.getFullYear() + "-" + m + "-" + day;

                    // Agar yeh date doctor ki hai toh isay green karo
                    if(availableDates.includes(dateString)) {
                        dayElem.className += " doctor-available-date";
                    }
                }
            });
            
            // Foran calendar open karne ke liye (Optional, acha UI feel deta hai)
            setTimeout(() => { fpInstance.open(); }, 100);
        }
    </script>
</body>
</html>