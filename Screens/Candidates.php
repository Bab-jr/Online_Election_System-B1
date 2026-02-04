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

// Fetch latest election
$election = $db->query("SELECT * FROM Election_History ORDER BY Election_ID DESC LIMIT 1")->fetch();
$electionStatus = $election['Status'] ?? 'Preparing';
$electionId = $election['Election_ID'] ?? 0;

// Parse Parties and Positions
$electionParties = json_decode($election['Parties'] ?? '[]', true) ?: [];
$electionPositionsRaw = json_decode($election['Positions'] ?? '[]', true) ?: [];

// Normalize Positions (Ensure they are objects with name and type)
$electionPositions = [];
$migrationNeeded = false;
foreach ($electionPositionsRaw as $p) {
    if (is_array($p) && isset($p['name'])) {
        $electionPositions[] = $p;
    } else {
        $electionPositions[] = ['name' => (string)$p, 'type' => 'Uniform'];
        $migrationNeeded = true;
    }
}

if ($migrationNeeded && $electionId) {
    $stmt = $db->prepare("UPDATE Election_History SET Positions = ? WHERE Election_ID = ?");
    $stmt->execute([json_encode($electionPositions), $electionId]);
}

// Handle POST actions for Editors (Preparing Status)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $electionStatus === 'Preparing') {
    if (isset($_POST['action'])) {
        $changed = false;
        $logAction = "";
        $logDetails = "";

        if ($_POST['action'] === 'add_position' && !empty($_POST['new_position'])) {
            $newPosName = trim($_POST['new_position']);
            $newPosType = $_POST['position_type'] ?? 'Uniform';
            
            $exists = false;
            foreach ($electionPositions as $p) {
                if ($p['name'] === $newPosName) { $exists = true; break; }
            }
            
            if (!$exists) {
                $electionPositions[] = ['name' => $newPosName, 'type' => $newPosType];
                $changed = true;
                $logAction = "Add Position";
                $logDetails = "Added new position: $newPosName ($newPosType)";
            }
        } elseif ($_POST['action'] === 'remove_position') {
            $posNameToRemove = $_POST['position'];
            $electionPositions = array_values(array_filter($electionPositions, fn($p) => $p['name'] !== $posNameToRemove));
            $changed = true;
            $logAction = "Remove Position";
            $logDetails = "Removed position: $posNameToRemove";
        } elseif ($_POST['action'] === 'add_party' && !empty($_POST['new_party'])) {
            $newParty = trim($_POST['new_party']);
            if (!in_array($newParty, $electionParties)) {
                $electionParties[] = $newParty;
                $changed = true;
                $logAction = "Add Party";
                $logDetails = "Added new political party: $newParty";
            }
        } elseif ($_POST['action'] === 'remove_party') {
            $partyToRemove = $_POST['party'];
            $electionParties = array_values(array_filter($electionParties, fn($p) => $p !== $partyToRemove));
            $changed = true;
            $logAction = "Remove Party";
            $logDetails = "Removed political party: $partyToRemove";
        }

        if ($changed) {
            $stmt = $db->prepare("UPDATE Election_History SET Parties = ?, Positions = ? WHERE Election_ID = ?");
            $stmt->execute([json_encode($electionParties), json_encode($electionPositions), $electionId]);
            
            // Log to Audit Trail
            $Auth->Log_Action($_SESSION['User_ID'], $_SESSION['User_Role'], $logAction, $logDetails);
            
            header("Location: Candidates.php");
            exit;
        }
    }
}

// Fetch all available tracks from Voters table
$availableTracks = $db->query("SELECT DISTINCT Track_Cluster FROM Voters WHERE Track_Cluster IS NOT NULL AND Track_Cluster != '' ORDER BY Track_Cluster")->fetchAll(PDO::FETCH_COLUMN);

// Filters (for Ongoing/Finished)
$search = $_GET['search'] ?? '';
$filterPosition = $_GET['position'] ?? '';
$filterParty = $_GET['party'] ?? '';
$filterGrade = $_GET['grade'] ?? '';

// Fetch Stats
$totalCandidates = $db->query("SELECT COUNT(*) FROM Candidates")->fetchColumn();
$positionsCount = count($electionPositions) ?: $db->query("SELECT COUNT(DISTINCT Position) FROM Candidates")->fetchColumn();
$partiesCount = count($electionParties) ?: $db->query("SELECT COUNT(DISTINCT Party) FROM Candidates")->fetchColumn();

// Fetch Breakdowns
$positionsBreakdown = $db->query("SELECT Position as label, COUNT(*) as value FROM Candidates GROUP BY Position ORDER BY value DESC")->fetchAll();
$partiesBreakdown = $db->query("SELECT Party as label, COUNT(*) as value FROM Candidates GROUP BY Party ORDER BY value DESC")->fetchAll();
$grades = $db->query("SELECT Grade_Level as label, COUNT(*) as value FROM Candidates GROUP BY Grade_Level ORDER BY Grade_Level ASC")->fetchAll();

// Build Query for List
$queryStr = "SELECT * FROM Candidates WHERE 1=1";
$params = [];

if ($search) {
    $queryStr .= " AND (Name LIKE ? OR Email LIKE ? OR Party LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filterPosition) {
    $queryStr .= " AND Position = ?";
    $params[] = $filterPosition;
}
if ($filterParty) {
    $queryStr .= " AND Party = ?";
    $params[] = $filterParty;
}
if ($filterGrade) {
    $queryStr .= " AND Grade_Level = ?";
    $params[] = $filterGrade;
}

$queryStr .= " ORDER BY Position ASC, Name ASC";
$stmt = $db->prepare($queryStr);
$stmt->execute($params);
$candidatesList = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Management | Online School Election System</title>
    <link rel="stylesheet" href="../Design/Style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .Candidate_Actions_Btn {
            height: 44px;
            padding: 0 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 0.9rem;
            margin-top: 0;
            width: auto;
        }
        .Candidate_Photo_Circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--Primary_Light);
            color: var(--Primary_Color);
            display: flex;
            justify-content: center;
            align-items: center; font-weight: 700;
            overflow: hidden;
            object-fit: cover;
        }
        .Editor_Grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }
        .Editor_Card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid var(--Border_Color);
        }
        .Item_Tag {
            display: inline-flex;
            flex-direction: column;
            background: var(--Primary_Light);
            color: var(--Primary_Color);
            padding: 8px 14px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            margin: 0 8px 8px 0;
            position: relative;
            min-width: 120px;
        }
        .Item_Tag_Header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }
        .Item_Tag_Type {
            font-size: 0.65rem;
            text-transform: uppercase;
            opacity: 0.7;
            font-weight: 700;
        }
        .Item_Tag i {
            cursor: pointer;
            font-size: 0.75rem;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .Item_Tag i:hover {
            opacity: 1;
        }
        .Status_Indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .Status_Preparing { background: #FEF3C7; color: #92400E; }
        .Status_Ongoing { background: #DCFCE7; color: #166534; }
        .Status_Finished { background: #F1F5F9; color: #475569; }
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
                <a href="Election_History.php" class="Nav_Item">
                    <i class="fas fa-vote-yea"></i> Election History
                </a>
                <a href="Voters.php" class="Nav_Item">
                    <i class="fas fa-users"></i> Voter Management
                </a>
                <a href="Candidates.php" class="Nav_Item Active">
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
                    <input type="text" placeholder="Quick search...">
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
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="width: 48px; height: 48px; background: var(--Primary_Light); color: var(--Primary_Color); border-radius: 12px; display: flex; justify-content: center; align-items: center; font-size: 1.5rem;">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div>
                            <h1 class="Page_Title">Candidate Management</h1>
                            <p class="Page_Subtitle">Managing SY <?php echo $election['Year'] ?? date('Y'); ?> Election</p>
                        </div>
                    </div>
                    <div>
                        <div class="Status_Indicator Status_<?php echo $electionStatus; ?>">
                            <i class="fas fa-circle" style="font-size: 0.6rem;"></i>
                            <?php echo $electionStatus; ?>
                        </div>
                    </div>
                </div>

                <?php if ($electionStatus === 'Preparing'): ?>
                    <!-- EDITORS (Preparing Mode) -->
                    <div class="Editor_Grid">
                        <!-- Positions Editor -->
                        <div class="Editor_Card">
                            <h3 class="Card_Title" style="margin-bottom: 15px;"><i class="fas fa-id-badge" style="color: var(--Primary_Color); margin-right: 10px;"></i> Define Positions</h3>
                            <div style="min-height: 80px; margin-bottom: 20px;">
                                <?php foreach ($electionPositions as $pos): ?>
                                    <span class="Item_Tag">
                                        <div class="Item_Tag_Header">
                                            <span><?php echo htmlspecialchars($pos['name']); ?></span>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="remove_position">
                                                <input type="hidden" name="position" value="<?php echo htmlspecialchars($pos['name']); ?>">
                                                <i class="fas fa-times" onclick="this.parentElement.submit()"></i>
                                            </form>
                                        </div>
                                        <div class="Item_Tag_Type"><?php echo htmlspecialchars($pos['type'] ?? 'Uniform'); ?></div>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <form method="POST" style="display: flex; flex-direction: column; gap: 10px;">
                                <input type="hidden" name="action" value="add_position">
                                <div style="display: flex; gap: 10px;">
                                    <input type="text" name="new_position" class="Input" placeholder="Position name..." required style="flex: 2;">
                                    <select name="position_type" class="Select" required style="flex: 1; height: 48px;">
                                        <option value="Uniform">Uniform</option>
                                        <option value="Track/Strand Specific">Track/Strand Specific</option>
                                    </select>
                                    <button type="submit" class="Button_Primary" style="width: auto; margin-top: 0; padding: 0 20px;"><i class="fas fa-plus"></i></button>
                                </div>
                            </form>
                        </div>

                        <!-- Parties Editor -->
                        <div class="Editor_Card">
                            <h3 class="Card_Title" style="margin-bottom: 15px;"><i class="fas fa-flag" style="color: var(--Success_Color); margin-right: 10px;"></i> Define Parties</h3>
                            <div style="min-height: 80px; margin-bottom: 20px;">
                                <?php foreach ($electionParties as $party): ?>
                                    <span class="Item_Tag" style="background: #DCFCE7; color: #166534; flex-direction: row; align-items: center; justify-content: space-between; min-width: auto; padding: 6px 14px; border-radius: 20px;">
                                        <?php echo htmlspecialchars($party); ?>
                                        <form method="POST" style="display: inline; margin-left: 8px;">
                                            <input type="hidden" name="action" value="remove_party">
                                            <input type="hidden" name="party" value="<?php echo htmlspecialchars($party); ?>">
                                            <i class="fas fa-times" onclick="this.parentElement.submit()"></i>
                                        </form>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <form method="POST" style="display: flex; gap: 10px;">
                                <input type="hidden" name="action" value="add_party">
                                <input type="text" name="new_party" class="Input" placeholder="Enter party name..." required>
                                <button type="submit" class="Button_Primary" style="width: auto; margin-top: 0; padding: 0 20px; background: var(--Success_Color);"><i class="fas fa-plus"></i></button>
                            </form>
                        </div>
                    </div>

                    <!-- Add Candidate Form (Bottom) -->
                    <div class="Card">
                        <div class="Card_Header">
                            <h2 class="Card_Title">Register New Candidate</h2>
                        </div>
                        <form method="POST" action="../Logic/Backend/Add_Candidate_Handler.php" enctype="multipart/form-data" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                            <div class="Form_Group">
                                <label class="Label">Full Name</label>
                                <input type="text" name="name" class="Input" placeholder="e.g. Juan Dela Cruz" required>
                            </div>
                            <div class="Form_Group">
                                <label class="Label">Email Address</label>
                                <input type="email" name="email" class="Input" placeholder="juan@school.edu" required>
                            </div>
                            <div class="Form_Group">
                                <label class="Label">User ID</label>
                                <input type="text" name="user_id" class="Input" placeholder="Leave blank for random">
                            </div>
                            <div class="Form_Group">
                                <label class="Label">Position</label>
                                <select name="position" class="Select" style="width: 100%; height: 50px;" required>
                                    <option value="">Select Position</option>
                                    <?php foreach ($electionPositions as $p): ?>
                                        <option value="<?php echo htmlspecialchars($p['name']); ?>"><?php echo htmlspecialchars($p['name']); ?> (<?php echo $p['type']; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="Form_Group">
                                <label class="Label">Political Party</label>
                                <select name="party" class="Select" style="width: 100%; height: 50px;" required>
                                    <option value="">Select Party</option>
                                    <option value="Independent">Independent</option>
                                    <?php foreach ($electionParties as $p): ?>
                                        <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="Form_Group">
                                <label class="Label">Grade Level</label>
                                <select name="grade" class="Select" style="width: 100%; height: 50px;" required>
                                    <option value="11">Grade 11</option>
                                    <option value="12">Grade 12</option>
                                </select>
                            </div>
                            <div class="Form_Group">
                                <label class="Label">Track / Strand</label>
                                <select name="track" class="Select" style="width: 100%; height: 50px;" required>
                                    <option value="">Select Track/Strand</option>
                                    <?php foreach ($availableTracks as $t): ?>
                                        <option value="<?php echo htmlspecialchars($t); ?>"><?php echo htmlspecialchars($t); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="Form_Group">
                                <label class="Label">Password</label>
                                <input type="password" name="password" class="Input" placeholder="Leave blank for default">
                            </div>
                            <div class="Form_Group">
                                <label class="Label">Candidate Photo</label>
                                <input type="file" name="photo" class="Input" accept="image/*">
                            </div>
                            <div class="Form_Group" style="grid-column: span 3; display: flex; justify-content: flex-end;">
                                <button type="submit" class="Button_Primary" style="margin-top: 0; width: 200px;"><i class="fas fa-user-plus"></i> Add Candidate</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- DASHBOARD VIEW (Ongoing/Finished Mode) -->
                    
                    <!-- Primary Stats -->
                    <div class="Stat_Cards_Grid" style="margin-bottom: 20px;">
                        <div class="Stat_Card">
                            <div class="Stat_Label" style="text-transform: uppercase; font-size: 0.7rem; font-weight: 700;">Total Candidates</div>
                            <div class="Stat_Value" style="color: var(--Primary_Color); font-size: 2.5rem; margin-top: 10px;"><?php echo $totalCandidates; ?></div>
                        </div>
                        <div class="Stat_Card">
                            <div class="Stat_Label" style="text-transform: uppercase; font-size: 0.7rem; font-weight: 700; color: var(--Secondary_Color);">Unique Positions</div>
                            <div class="Stat_Value" style="color: var(--Secondary_Color); font-size: 2.5rem; margin-top: 10px;"><?php echo $positionsCount; ?></div>
                        </div>
                        <div class="Stat_Card">
                            <div class="Stat_Label" style="text-transform: uppercase; font-size: 0.7rem; font-weight: 700; color: var(--Success_Color);">Active Parties</div>
                            <div class="Stat_Value" style="color: var(--Success_Color); font-size: 2.5rem; margin-top: 10px;"><?php echo $partiesCount; ?></div>
                        </div>
                    </div>

                    <!-- Breakdown Stats -->
                    <div class="Summary_Breakdowns">
                        <div class="Breakdown_Card">
                            <h3 class="Breakdown_Title">Candidates by Position</h3>
                            <?php if (empty($positionsBreakdown)): ?><p style="font-size: 0.8rem; color: var(--Text_Secondary);">No data available</p><?php endif; ?>
                            <?php foreach (array_slice($positionsBreakdown, 0, 5) as $pos): ?>
                            <div class="Breakdown_Item">
                                <span><?php echo htmlspecialchars($pos['label'] ?: 'N/A'); ?></span>
                                <span style="font-weight: 700;"><?php echo $pos['value']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="Breakdown_Card">
                            <h3 class="Breakdown_Title">Candidates by Party</h3>
                            <?php if (empty($partiesBreakdown)): ?><p style="font-size: 0.8rem; color: var(--Text_Secondary);">No data available</p><?php endif; ?>
                            <?php foreach (array_slice($partiesBreakdown, 0, 5) as $party): ?>
                            <div class="Breakdown_Item">
                                <span><?php echo htmlspecialchars($party['label'] ?: 'Independent'); ?></span>
                                <span style="font-weight: 700;"><?php echo $party['value']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="Breakdown_Card">
                            <h3 class="Breakdown_Title">Candidates by Grade</h3>
                            <?php if (empty($grades)): ?><p style="font-size: 0.8rem; color: var(--Text_Secondary);">No data available</p><?php endif; ?>
                            <?php foreach ($grades as $grade): ?>
                            <div class="Breakdown_Item">
                                <span>Grade <?php echo htmlspecialchars($grade['label'] ?: 'N/A'); ?></span>
                                <span style="font-weight: 700;"><?php echo $grade['value']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Table Card -->
                    <div class="Card" style="padding: 0; overflow: hidden;">
                        <!-- Filters -->
                        <form method="GET" class="Filter_Bar" style="display: flex; flex-wrap: nowrap; gap: 20px; align-items: flex-end; background: transparent; border: none; border-bottom: 1px solid var(--Border_Color); border-radius: 0; margin-bottom: 0; padding: 24px;">
                            <div class="Filter_Group" style="flex: 2;">
                                <label>Search</label>
                                <div class="Search_Input_Wrapper">
                                    <i class="fas fa-search"></i>
                                    <input type="text" name="search" class="Input" placeholder="Search by name, email or party" value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="Filter_Group" style="flex: 1;">
                                <label>Position</label>
                                <select name="position" class="Select" onchange="this.form.submit()" style="width: 100%;">
                                    <option value="">All Positions</option>
                                    <?php foreach ($electionPositions as $p): ?>
                                        <option value="<?php echo htmlspecialchars($p['name']); ?>" <?php echo $filterPosition == $p['name'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="Filter_Group" style="flex: 1;">
                                <label>Party</label>
                                <select name="party" class="Select" onchange="this.form.submit()" style="width: 100%;">
                                    <option value="">All Parties</option>
                                    <?php foreach ($electionParties as $p): ?>
                                        <option value="<?php echo htmlspecialchars($p); ?>" <?php echo $filterParty == $p ? 'selected' : ''; ?>><?php echo htmlspecialchars($p); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="Filter_Group" style="flex: 1;">
                                <label>Grade</label>
                                <select name="grade" class="Select" onchange="this.form.submit()" style="width: 100%;">
                                    <option value="">All Grades</option>
                                    <?php 
                                    $allGradesList = $db->query("SELECT DISTINCT Grade_Level FROM Candidates WHERE Grade_Level IS NOT NULL ORDER BY Grade_Level")->fetchAll(PDO::FETCH_COLUMN);
                                    foreach ($allGradesList as $g): ?>
                                        <option value="<?php echo htmlspecialchars($g); ?>" <?php echo $filterGrade == $g ? 'selected' : ''; ?>>Grade <?php echo htmlspecialchars($g); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>

                        <div class="Table_Wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Candidate</th>
                                        <th>Position</th>
                                        <th>Party</th>
                                        <th>Grade/Track</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($candidatesList)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 40px; color: var(--Text_Secondary);">
                                            No candidates found matching your criteria.
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($candidatesList as $candidate): ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div class="Candidate_Photo_Circle">
                                                        <?php if (!empty($candidate['Photo'])): ?>
                                                            <img src="<?php echo htmlspecialchars($candidate['Photo']); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                                        <?php else: ?>
                                                            <?php echo strtoupper(substr($candidate['Name'] ?? 'C', 0, 1)); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($candidate['Name']); ?></div>
                                                        <div style="font-size: 0.75rem; color: var(--Text_Secondary);"><?php echo htmlspecialchars($candidate['User_ID']); ?> | <?php echo htmlspecialchars($candidate['Email']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="Badge" style="background: var(--Primary_Light); color: var(--Primary_Color);"><?php echo htmlspecialchars($candidate['Position'] ?: 'N/A'); ?></span>
                                            </td>
                                            <td>
                                                <div style="font-weight: 500;"><?php echo htmlspecialchars($candidate['Party'] ?: 'Independent'); ?></div>
                                            </td>
                                            <td>
                                                <div style="font-size: 0.9rem;">Grade <?php echo htmlspecialchars($candidate['Grade_Level'] ?: 'N/A'); ?></div>
                                                <div style="font-size: 0.75rem; color: var(--Text_Secondary);"><?php echo htmlspecialchars($candidate['Track_Cluster'] ?: 'N/A'); ?></div>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 8px;">
                                                    <button title="Edit" style="background: none; border: none; color: var(--Secondary_Color); cursor: pointer; font-size: 1rem;"><i class="fas fa-edit"></i></button>
                                                    <button title="Delete" style="background: none; border: none; color: var(--Error_Color); cursor: pointer; font-size: 1rem;"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="padding: 15px 24px; font-size: 0.85rem; color: var(--Text_Secondary); border-top: 1px solid var(--Border_Color);">
                            Showing <?php echo count($candidatesList); ?> of <?php echo $totalCandidates; ?> candidates
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>