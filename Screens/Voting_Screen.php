<?php
require_once __DIR__ . '/../Logic/Backend/Authentication_Handler.php';
$Auth = new Authentication_Handler();
$Auth->Check_Auth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Screen - Online School Election System</title>
    <link rel="stylesheet" href="../Design/Style.css">
</head>
<body>
    <nav class="Dashboard_Navbar">
        <div class="Logo"><strong>Click to Vote</strong></div>
        <div class="User_Info">
            <span><?php echo $_SESSION['User_Name']; ?> (Voter)</span>
            <a href="Logout.php" style="margin-left: 20px; font-size: 0.875rem;">Logout</a>
        </div>
    </nav>

    <main style="padding: 40px 24px; max-width: 800px; margin: 0 auto;">
        <h2 style="margin-bottom: 24px; text-align: center;">Welcome, <?php echo $_SESSION['User_Name']; ?></h2>
        
        <div class="Card" style="max-width: 100%; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 20px;">🗳️</div>
            <h3 style="margin-bottom: 12px;">Election Status</h3>
            <p class="Text_Muted">There is no active election at the moment. Please check back later during the scheduled voting period.</p>
        </div>
    </main>
</body>
</html>
