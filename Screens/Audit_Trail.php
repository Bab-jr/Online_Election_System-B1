<?php
require_once __DIR__ . '/../Logic/Backend/Authentication_Handler.php';
require_once __DIR__ . '/../db/config.php';

$Auth = new Authentication_Handler();
$Auth->Check_Auth();

if ($_SESSION['Access_Level'] < 1) {
    header('Location: Voting_Screen.php');
    exit;
}

$db = db();

// Fetch Logs
$query = "SELECT * FROM Audit_Trail ORDER BY Timestamp DESC LIMIT 100";
$logs = $db->query($query)->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Trail | Online School Election System</title>
    <link rel="stylesheet" href="../Design/Style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="Dashboard_Container">
        <!-- Sidebar -->
        <aside class="Sidebar">
            <div class="Sidebar_Header">
                <div class="Logo_Text">
                    <div class="Logo_Title" style="font-size: 1.1rem; color: var(--Primary_Color);">Click to Vote</div>
                    <div class="Logo_Subtitle">Administrator Portal</div>
                </div>
            </div>
            <nav class="Sidebar_Nav">
                <a href="Election_Dashboard.php" class="Nav_Item">
                    <i class="fas fa-th-large"></i> Election Dashboard
                </a>
                <a href="Election_History.php" class="Nav_Item">
                    <i class="fas fa-vote-yea"></i> Election History
                </a>
                <a href="Voters.php" class="Nav_Item">
                    <i class="fas fa-users"></i> Voter Management
                </a>
                <a href="Candidates.php" class="Nav_Item">
                    <i class="fas fa-user-tie"></i> Candidate Management
                </a>
                <a href="Officers.php" class="Nav_Item">
                    <i class="fas fa-user-shield"></i> Officers Management
                </a>
                <a href="Audit_Trail.php" class="Nav_Item Active">
                    <i class="fas fa-file-alt"></i> Reports & Audit
                </a>
                <a href="Credits.php" class="Nav_Item">
                    <i class="fas fa-info-circle"></i> Credits
                </a>
            </nav>
            <div style="padding: 24px; border-top: 1px solid var(--Border_Color);">
                <a href="Logout.php" class="Nav_Item" style="padding: 0; color: var(--Error_Color);">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="Main_Content">
            <!-- Top Bar -->
            <header class="Top_Bar">
                <div class="Search_Box">
                    <i class="fas fa-search" style="color: var(--Secondary_Color);"></i>
                    <input type="text" placeholder="Search logs...">
                </div>
                <div class="Top_Bar_Actions">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="text-align: right;">
                            <div style="font-weight: 700; font-size: 0.9rem;"><?php echo htmlspecialchars($_SESSION['User_Name']); ?></div>
                            <div style="font-size: 0.75rem; color: var(--Text_Secondary);"><?php echo htmlspecialchars($_SESSION['User_Role']); ?></div>
                        </div>
                        <div style="width: 40px; height: 40px; background: var(--Primary_Light); color: var(--Primary_Color); border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: 700;">
                            <?php echo strtoupper(substr($_SESSION['User_Name'] ?? 'A', 0, 1)); ?>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Body -->
            <div class="Content_Body">
                <div class="Page_Title_Section">
                    <h1 class="Page_Title">Audit Trail</h1>
                    <p class="Page_Subtitle">System activity and security logs</p>
                </div>

                <div class="Card" style="padding: 0; overflow: hidden;">
                    <div class="Table_Wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User ID</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--Text_Secondary);">
                                        No activity logs found.
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d H:i:s', strtotime($log['Timestamp'])); ?></td>
                                        <td style="font-weight: 600;"><?php echo htmlspecialchars($log['User_ID']); ?></td>
                                        <td><span class="Badge" style="background: var(--Primary_Light); color: var(--Primary_Color);"><?php echo htmlspecialchars($log['User_Role']); ?></span></td>
                                        <td><strong><?php echo htmlspecialchars($log['Action_Type']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($log['Action_Details']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>