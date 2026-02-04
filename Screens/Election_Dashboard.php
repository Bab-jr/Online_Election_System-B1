<?php
require_once __DIR__ . '/../Logic/Backend/Authentication_Handler.php';
$Auth = new Authentication_Handler();
$Auth->Check_Auth();

if ($_SESSION['Access_Level'] < 1) {
    header('Location: Voting_Screen.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Dashboard - Online School Election System</title>
    <link rel="stylesheet" href="../Design/Style.css">
</head>
<body>
    <nav class="Dashboard_Navbar">
        <div class="Logo"><strong>ElectionSystem</strong></div>
        <div class="User_Info">
            <span><?php echo $_SESSION['User_Name']; ?> (<?php echo $_SESSION['User_Role']; ?>)</span>
            <a href="Logout.php" style="margin-left: 20px; font-size: 0.875rem;">Logout</a>
        </div>
    </nav>

    <div class="Tab_Navigation">
        <a href="Election_History.html" class="Tab_Item">Election History</a>
        <a href="Election_Dashboard.php" class="Tab_Item Active">Election Dashboard</a>
        <a href="Officers.html" class="Tab_Item">Officers</a>
        <a href="Voters.html" class="Tab_Item">Voters</a>
        <a href="Audit_Trail.html" class="Tab_Item">Audit Trail</a>
    </div>

    <main style="padding: 40px 24px;">
        <h2 style="margin-bottom: 24px;">Election Dashboard</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
            <div class="Card">
                <h3 style="font-size: 1rem; margin-bottom: 12px;">Active Election</h3>
                <p class="Text_Muted">No active election currently running.</p>
                <button class="Button_Primary" style="margin-top: 16px;">Start New Election</button>
            </div>
            <div class="Card">
                <h3 style="font-size: 1rem; margin-bottom: 12px;">Voter Participation</h3>
                <div style="font-size: 2rem; font-weight: 700;">0%</div>
                <p class="Text_Muted">0 of 0 voters have cast their ballots.</p>
            </div>
        </div>
    </main>
</body>
</html>
