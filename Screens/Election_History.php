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

// Fetch all completed elections
$history = $db->query("SELECT * FROM Election_History ORDER BY Year DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election History | Online School Election System</title>
    <link rel="stylesheet" href="../Design/Style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .Year_Selector {
            display: flex;
            align-items: center;
            gap: 12px;
        }
    </style>
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
                <a href="Election_History.php" class="Nav_Item Active">
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
                <a href="Audit_Trail.php" class="Nav_Item">
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
                    <input type="text" placeholder="Search for records...">
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
                    <div>
                        <h1 class="Page_Title">Election History</h1>
                        <p class="Page_Subtitle">Voter turnout and candidate results per election year</p>
                    </div>
                    <div class="Year_Selector">
                        <select class="Select" id="JumpToYear">
                            <option value="">Jump to School Year</option>
                            <?php foreach ($history as $item): ?>
                                <option value="year-<?php echo $item['Year']; ?>">School Year <?php echo $item['Year']; ?>–<?php echo $item['Year']+1; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php if (empty($history)): ?>
                    <div class="Card" style="text-align: center; padding: 60px;">
                        <i class="fas fa-history" style="font-size: 3rem; color: var(--Border_Color); margin-bottom: 20px;"></i>
                        <h3>No Election History Found</h3>
                        <p class="Text_Muted">Completed elections will appear here once they are finalized.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($history as $index => $item): ?>
                        <div class="Accordion_Item <?php echo $index === 0 ? 'Active' : ''; ?>" id="year-<?php echo $item['Year']; ?>">
                            <div class="Accordion_Header">
                                <h3>School Year <?php echo $item['Year']; ?>–<?php echo $item['Year']+1; ?></h3>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span class="Badge <?php echo $item['Status'] === 'Finished' ? 'Badge_Success' : 'Badge_Warning'; ?>"><?php echo $item['Status']; ?></span>
                                    <i class="fas fa-chevron-down Accordion_Icon"></i>
                                </div>
                            </div>
                            <div class="Accordion_Content">
                                <!-- Stats Overview -->
                                <div class="Stat_Cards_Grid" style="margin-bottom: 24px;">
                                    <div class="Stat_Card">
                                        <div class="Stat_Label">Total Voters</div>
                                        <div class="Stat_Value"><?php echo $item['Total_Voters']; ?></div>
                                    </div>
                                    <div class="Stat_Card">
                                        <div class="Stat_Label">Election Period</div>
                                        <div style="font-size: 1rem; font-weight: 600;">
                                            <?php echo date('M d, Y', strtotime($item['Start_Date'])); ?> to <?php echo date('M d, Y', strtotime($item['End_Date'])); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Breakdown Tables -->
                                <div class="Dashboard_Grid" style="grid-template-columns: 1fr; gap: 24px;">
                                    <div class="Card" style="padding: 0; box-shadow: none; border: 1px solid var(--Border_Color);">
                                        <div class="Card_Header" style="padding: 20px 24px; margin-bottom: 0; border-bottom: 1px solid var(--Border_Color);">
                                            <h4 class="Card_Title" style="font-size: 1rem;">Candidate Results</h4>
                                        </div>
                                        <div class="Table_Wrapper">
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th>Candidate Name</th>
                                                        <th>Position</th>
                                                        <th>Party</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $results = json_decode($item['Results'] ?? '[]', true) ?: [];
                                                    if (empty($results)):
                                                    ?>
                                                        <tr><td colspan="3" style="text-align: center; padding: 20px;">No detailed results saved for this election.</td></tr>
                                                    <?php else: ?>
                                                        <?php foreach ($results as $candidate): ?>
                                                            <tr>
                                                                <td style="font-weight: 600;"><?php echo htmlspecialchars($candidate['Name']); ?></td>
                                                                <td><?php echo htmlspecialchars($candidate['Position']); ?></td>
                                                                <td><?php echo htmlspecialchars($candidate['Party']); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        document.querySelectorAll('.Accordion_Header').forEach(header => {
            header.addEventListener('click', () => {
                const item = header.parentElement;
                item.classList.toggle('Active');
            });
        });

        document.getElementById('JumpToYear').addEventListener('change', function() {
            const targetId = this.value;
            if (targetId) {
                const element = document.getElementById(targetId);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                    element.classList.add('Active');
                }
            }
        });
    </script>
</body>
</html>