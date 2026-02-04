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

// Filters
$search = $_GET['search'] ?? '';
$filterTrack = $_GET['track'] ?? '';
$filterGrade = $_GET['grade'] ?? '';
$filterSection = $_GET['section'] ?? '';

// Fetch Stats
$totalVoters = $db->query("SELECT COUNT(*) FROM Voters")->fetchColumn();
$votedCount = $db->query("SELECT COUNT(*) FROM Voters WHERE Has_Voted = 1")->fetchColumn();
$notVotedCount = $totalVoters - $votedCount;

// Fetch Breakdowns
$tracks = $db->query("SELECT Track_Cluster as label, COUNT(*) as value FROM Voters GROUP BY Track_Cluster")->fetchAll();
$grades = $db->query("SELECT Grade_Level as label, COUNT(*) as value FROM Voters GROUP BY Grade_Level")->fetchAll();
$sections = $db->query("SELECT Section as label, COUNT(*) as value FROM Voters GROUP BY Section")->fetchAll();

// Build Query for List
$queryStr = "SELECT * FROM Voters WHERE 1=1";
$params = [];

if ($search) {
    $queryStr .= " AND Email LIKE ?";
    $params[] = "%$search%";
}
if ($filterTrack) {
    $queryStr .= " AND Track_Cluster = ?";
    $params[] = $filterTrack;
}
if ($filterGrade) {
    $queryStr .= " AND Grade_Level = ?";
    $params[] = $filterGrade;
}
if ($filterSection) {
    $queryStr .= " AND Section = ?";
    $params[] = $filterSection;
}

$queryStr .= " ORDER BY Email ASC";
$stmt = $db->prepare($queryStr);
$stmt->execute($params);
$votersList = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Management | Online School Election System</title>
    <link rel="stylesheet" href="../Design/Style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .Voter_Actions_Btn {
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

        /* Modal Styles */
        .Modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
        }
        .Modal_Content {
            background: white;
            padding: 32px;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            position: relative;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        }
        .Modal_Header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .Modal_Title {
            font-size: 1.25rem;
            font-weight: 700;
        }
        .Modal_Close {
            cursor: pointer;
            font-size: 1.25rem;
            color: var(--Text_Secondary);
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
                <a href="Election_History.php" class="Nav_Item">
                    <i class="fas fa-vote-yea"></i> Election History
                </a>
                <a href="Voters.php" class="Nav_Item Active">
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
                <?php if (isset($_GET['success'])): ?>
                    <div class="Badge Badge_Success" style="width: 100%; padding: 15px; margin-bottom: 20px; text-align: center;">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="Badge" style="width: 100%; padding: 15px; margin-bottom: 20px; text-align: center; background: #FEE2E2; color: #EF4444;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>

                <div class="Page_Title_Section">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="width: 48px; height: 48px; background: var(--Primary_Light); color: var(--Primary_Color); border-radius: 12px; display: flex; justify-content: center; align-items: center; font-size: 1.5rem;">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div>
                            <h1 class="Page_Title">Voters List</h1>
                            <p class="Page_Subtitle">View and manage registered voters</p>
                        </div>
                    </div>
                </div>

                <!-- Primary Stats -->
                <div class="Stat_Cards_Grid" style="margin-bottom: 20px;">
                    <div class="Stat_Card">
                        <div class="Stat_Label" style="text-transform: uppercase; font-size: 0.7rem; font-weight: 700;">Total Voters</div>
                        <div class="Stat_Value" style="color: var(--Primary_Color); font-size: 2.5rem; margin-top: 10px;"><?php echo $totalVoters; ?></div>
                    </div>
                    <div class="Stat_Card">
                        <div class="Stat_Label" style="text-transform: uppercase; font-size: 0.7rem; font-weight: 700; color: var(--Secondary_Color);">Voters Who Voted</div>
                        <div class="Stat_Value" style="color: var(--Secondary_Color); font-size: 2.5rem; margin-top: 10px;"><?php echo $votedCount; ?></div>
                    </div>
                    <div class="Stat_Card">
                        <div class="Stat_Label" style="text-transform: uppercase; font-size: 0.7rem; font-weight: 700; color: var(--Error_Color);">Voters Who Haven't Voted</div>
                        <div class="Stat_Value" style="color: var(--Error_Color); font-size: 2.5rem; margin-top: 10px;"><?php echo $notVotedCount; ?></div>
                    </div>
                </div>

                <!-- Breakdown Stats -->
                <div class="Summary_Breakdowns">
                    <div class="Breakdown_Card">
                        <h3 class="Breakdown_Title">Total by Track</h3>
                        <?php foreach ($tracks as $track): ?>
                        <div class="Breakdown_Item">
                            <span><?php echo htmlspecialchars($track['label'] ?: 'N/A'); ?></span>
                            <span style="font-weight: 700;"><?php echo $track['value']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="Breakdown_Card">
                        <h3 class="Breakdown_Title">Total by Grade</h3>
                        <?php foreach ($grades as $grade): ?>
                        <div class="Breakdown_Item">
                            <span>Grade <?php echo htmlspecialchars($grade['label'] ?: 'N/A'); ?></span>
                            <span style="font-weight: 700;"><?php echo $grade['value']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="Breakdown_Card">
                        <h3 class="Breakdown_Title">Total by Section</h3>
                        <?php foreach (array_slice($sections, 0, 5) as $section): ?>
                        <div class="Breakdown_Item">
                            <span><?php echo htmlspecialchars($section['label'] ?: 'N/A'); ?></span>
                            <span style="font-weight: 700;"><?php echo $section['value']; ?></span>
                        </div>
                        <?php endforeach; ?>
                        <?php if (count($sections) > 5): ?>
                            <div style="font-size: 0.75rem; color: var(--Text_Secondary); text-align: center; margin-top: 10px;">+ <?php echo count($sections) - 5; ?> more sections</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 20px;">
                    <button class="Button_Primary Voter_Actions_Btn" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add Voter
                    </button>
                    <button class="Button_Secondary Voter_Actions_Btn" onclick="openImportModal()">
                        <i class="fas fa-file-import"></i> Import CSV
                    </button>
                </div>

                <!-- Table Card -->
                <div class="Card" style="padding: 0; overflow: hidden;">
                    <!-- Filters (Integrated into Card with Flex Layout) -->
                    <form method="GET" class="Filter_Bar" style="display: flex; flex-wrap: nowrap; gap: 20px; align-items: flex-end; background: transparent; border: none; border-bottom: 1px solid var(--Border_Color); border-radius: 0; margin-bottom: 0; padding: 24px;">
                        <div class="Filter_Group" style="flex: 2;">
                            <label>Search</label>
                            <div class="Search_Input_Wrapper">
                                <i class="fas fa-search"></i>
                                <input type="text" name="search" class="Input" placeholder="Search by email" value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="Filter_Group" style="flex: 1;">
                            <label>Track</label>
                            <select name="track" class="Select" onchange="this.form.submit()" style="width: 100%;">
                                <option value="">All Tracks</option>
                                <?php 
                                $allTracks = $db->query("SELECT DISTINCT Track_Cluster FROM Voters WHERE Track_Cluster IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
                                foreach ($allTracks as $t): ?>
                                    <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $filterTrack == $t ? 'selected' : ''; ?>><?php echo htmlspecialchars($t); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="Filter_Group" style="flex: 1;">
                            <label>Grade</label>
                            <select name="grade" class="Select" onchange="this.form.submit()" style="width: 100%;">
                                <option value="">All Grades</option>
                                <?php 
                                $allGrades = $db->query("SELECT DISTINCT Grade_Level FROM Voters WHERE Grade_Level IS NOT NULL ORDER BY Grade_Level")->fetchAll(PDO::FETCH_COLUMN);
                                foreach ($allGrades as $g): ?>
                                    <option value="<?php echo htmlspecialchars($g); ?>" <?php echo $filterGrade == $g ? 'selected' : ''; ?>>Grade <?php echo htmlspecialchars($g); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="Filter_Group" style="flex: 1;">
                            <label>Section</label>
                            <select name="section" class="Select" onchange="this.form.submit()" style="width: 100%;">
                                <option value="">All Sections</option>
                                <?php 
                                $allSections = $db->query("SELECT DISTINCT Section FROM Voters WHERE Section IS NOT NULL ORDER BY Section")->fetchAll(PDO::FETCH_COLUMN);
                                foreach ($allSections as $s): ?>
                                    <option value="<?php echo htmlspecialchars($s); ?>" <?php echo $filterSection == $s ? 'selected' : ''; ?>><?php echo htmlspecialchars($s); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <div class="Table_Wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Email</th>
                                    <th>Track</th>
                                    <th>Grade</th>
                                    <th>Section</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($votersList)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: var(--Text_Secondary);">
                                        No voters found matching your criteria.
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($votersList as $voter): ?>
                                    <tr>
                                        <td style="font-weight: 600;"><?php echo htmlspecialchars($voter['User_ID']); ?></td>
                                        <td><?php echo htmlspecialchars($voter['Email']); ?></td>
                                        <td><?php echo htmlspecialchars($voter['Track_Cluster'] ?: 'N/A'); ?></td>
                                        <td>Grade <?php echo htmlspecialchars($voter['Grade_Level'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($voter['Section'] ?: 'N/A'); ?></td>
                                        <td>
                                            <?php if ($voter['Has_Voted']): ?>
                                                <span class="Badge Badge_Success">Voted</span>
                                            <?php else: ?>
                                                <span class="Badge" style="background: var(--Primary_Light); color: var(--Primary_Color);">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="padding: 15px 24px; font-size: 0.85rem; color: var(--Text_Secondary); border-top: 1px solid var(--Border_Color);">
                        Showing <?php echo count($votersList); ?> of <?php echo $totalVoters; ?> voters
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Voter Modal -->
    <div id="AddModal" class="Modal">
        <div class="Modal_Content">
            <div class="Modal_Header">
                <h2 class="Modal_Title">Add New Voter</h2>
                <span class="Modal_Close" onclick="closeModal('AddModal')">&times;</span>
            </div>
            <form action="../Logic/Backend/Add_Voter_Handler.php" method="POST">
                <div class="Form_Group">
                    <label class="Label">Email Address</label>
                    <input type="email" name="email" class="Input" placeholder="voter@school.edu" required>
                </div>
                <div class="Form_Group" style="margin-top: 15px;">
                    <label class="Label">User ID</label>
                    <input type="text" name="user_id" class="Input" placeholder="Leave blank for random">
                </div>
                <div class="Form_Group" style="margin-top: 15px;">
                    <label class="Label">Track / Strand</label>
                    <input type="text" name="track" class="Input" placeholder="e.g. STEM" required>
                </div>
                <div class="Form_Group" style="margin-top: 15px;">
                    <label class="Label">Grade Level</label>
                    <select name="grade" class="Select" style="width: 100%; height: 50px;" required>
                        <option value="11">Grade 11</option>
                        <option value="12">Grade 12</option>
                    </select>
                </div>
                <div class="Form_Group" style="margin-top: 15px;">
                    <label class="Label">Section</label>
                    <input type="text" name="section" class="Input" placeholder="e.g. A" required>
                </div>
                <div class="Form_Group" style="margin-top: 15px;">
                    <label class="Label">Password</label>
                    <input type="password" name="password" class="Input" placeholder="Leave blank for default">
                </div>
                <button type="submit" class="Button_Primary" style="width: 100%; margin-top: 24px;">Save Voter</button>
            </form>
        </div>
    </div>

    <!-- Import CSV Modal -->
    <div id="ImportModal" class="Modal">
        <div class="Modal_Content">
            <div class="Modal_Header">
                <h2 class="Modal_Title">Import Voters via CSV</h2>
                <span class="Modal_Close" onclick="closeModal('ImportModal')">&times;</span>
            </div>
            <p class="Text_Muted" style="margin-bottom: 20px; font-size: 0.85rem;">
                Upload a CSV file with the following headers: <br>
                <strong>User_ID, Email, Track_Cluster, Grade_Level, Section, Password</strong>
            </p>
            <form action="../Logic/Backend/Import_Voters_Handler.php" method="POST" enctype="multipart/form-data">
                <div class="Form_Group">
                    <label class="Label">Select CSV File</label>
                    <input type="file" name="csv_file" class="Input" accept=".csv" required>
                </div>
                <button type="submit" class="Button_Primary" style="width: 100%; margin-top: 24px;">Upload & Import</button>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('AddModal').style.display = 'flex';
        }
        function openImportModal() {
            document.getElementById('ImportModal').style.display = 'flex';
        }
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('Modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>