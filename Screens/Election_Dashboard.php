<?php
require_once __DIR__ . '/../Logic/Backend/Authentication_Handler.php';
require_once __DIR__ . '/../db/config.php';

$Auth = new Authentication_Handler();
$Auth->Check_Auth();

if ($_SESSION['Access_Level'] < 1) {
    header('Location: Voting_Screen.php');
    exit;
}

// Fetch Stats
$db = db();
$totalVoters = $db->query("SELECT COUNT(*) FROM Voters")->fetchColumn();
$totalCandidates = $db->query("SELECT COUNT(*) FROM Candidates")->fetchColumn();
$totalVotes = $db->query("SELECT COUNT(*) FROM Voters WHERE Has_Voted = 1")->fetchColumn();

// Fetch Active Elections
$activeElections = $db->query("SELECT * FROM Election_History WHERE Status = 'Active' ORDER BY Start_Date DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Dashboard | Online School Election System</title>
    <link rel="stylesheet" href="../Design/Style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="Dashboard_Container">
        <!-- Sidebar -->
        <aside class="Sidebar">
            <div class="Sidebar_Header">
                <div class="Logo_Text">
                    <div class="Logo_Title" style="font-size: 1.1rem; color: var(--Primary_Color);">ElectionSystem</div>
                    <div class="Logo_Subtitle">Administrator Portal</div>
                </div>
            </div>
            <nav class="Sidebar_Nav">
                <a href="Election_Dashboard.php" class="Nav_Item Active">
                    <i class="fas fa-th-large"></i> Election Dashboard
                </a>
                <a href="Election_History.php" class="Nav_Item">
                    <i class="fas fa-vote-yea"></i> Election History
                </a>
                <a href="Voters.php" class="Nav_Item">
                    <i class="fas fa-users"></i> Voter Management
                </a>
                <a href="Candidates.html" class="Nav_Item">
                    <i class="fas fa-user-tie"></i> Candidate Management
                </a>
                <a href="Officers.html" class="Nav_Item">
                    <i class="fas fa-user-shield"></i> Officers Management
                </a>
                <a href="Audit_Trail.html" class="Nav_Item">
                    <i class="fas fa-file-alt"></i> Reports & Audit
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
                    <input type="text" placeholder="Search for voters, candidates, or records...">
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
                    <h1 class="Page_Title">Election Dashboard</h1>
                    <p class="Page_Subtitle">Welcome back! Here's what's happening with the current elections.</p>
                </div>

                <!-- Stat Cards -->
                <div class="Stat_Cards_Grid">
                    <div class="Stat_Card">
                        <div class="Stat_Label">Total Voters</div>
                        <div class="Stat_Value"><?php echo number_format($totalVoters); ?></div>
                        <div class="Stat_Trend Trend_Up"><i class="fas fa-users"></i> Registered Students</div>
                    </div>
                    <div class="Stat_Card">
                        <div class="Stat_Label">Total Candidates</div>
                        <div class="Stat_Value"><?php echo number_format($totalCandidates); ?></div>
                        <div class="Stat_Trend" style="color: var(--Primary_Color);"><i class="fas fa-user-tie"></i> Running for Office</div>
                    </div>
                    <div class="Stat_Card">
                        <div class="Stat_Label">Total Votes Cast</div>
                        <div class="Stat_Value"><?php echo number_format($totalVotes); ?></div>
                        <div class="Stat_Trend Trend_Up"><i class="fas fa-check-circle"></i> Verified Ballots</div>
                    </div>
                </div>

                <!-- Dashboard Grid -->
                <div class="Dashboard_Grid">
                    <!-- Main Content: Active Elections -->
                    <div class="Card">
                        <div class="Card_Header">
                            <h2 class="Card_Title">Active Elections</h2>
                            <button class="Button_Primary" style="width: auto; padding: 8px 16px; font-size: 0.85rem;">
                                <i class="fas fa-plus"></i> New Election
                            </button>
                        </div>
                        <div class="Table_Wrapper">
                            <table>
                               <thead>
                                   <tr>
                                       <th>Election Title</th>
                                       <th>Start Date</th>
                                       <th>End Date</th>
                                       <th>Status</th>
                                       <th>Actions</th>
                                   </tr>
                               </thead>
                               <tbody>
                                   <?php if (empty($activeElections)): ?>
                                   <tr>
                                       <td colspan="5" style="text-align: center; padding: 40px; color: var(--Text_Secondary);">
                                           No active elections found. <a href="#" style="color: var(--Primary_Color); font-weight: 600;">Create one now.</a>
                                       </td>
                                   </tr>
                                   <?php else: ?>
                                       <?php foreach ($activeElections as $election): ?>
                                       <tr>
                                           <td style="font-weight: 600;">School Year <?php echo $election['Year']; ?> Election</td>
                                           <td><?php echo date('M d, Y', strtotime($election['Start_Date'])); ?></td>
                                           <td><?php echo date('M d, Y', strtotime($election['End_Date'])); ?></td>
                                           <td><span class="Badge Badge_Success">Active</span></td>
                                           <td>
                                               <a href="#" style="color: var(--Primary_Color); margin-right: 12px;"><i class="fas fa-eye"></i></a>
                                               <a href="#" style="color: var(--Secondary_Color);"><i class="fas fa-cog"></i></a>
                                           </td>
                                       </tr>
                                       <?php endforeach; ?>
                                   <?php endif; ?>
                               </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>