<?php
require_once __DIR__ . '/../Logic/Backend/Authentication_Handler.php';
require_once __DIR__ . '/../db/config.php';

$Auth = new Authentication_Handler();
$Auth->Check_Auth();

// Only Admins can access Officer Management (Access Level 3)
if ($_SESSION['Access_Level'] < 3) {
    header('Location: Election_Dashboard.php');
    exit;
}

$db = db();

// Fetch Officers grouped by role
$admins = $db->query("SELECT * FROM Officers WHERE Role = 'Admin' ORDER BY Name ASC")->fetchAll();
$advisers = $db->query("SELECT * FROM Officers WHERE Role = 'COMEA Adviser' ORDER BY Name ASC")->fetchAll();
$comeaOfficers = $db->query("SELECT * FROM Officers WHERE Role = 'COMEA Officer' ORDER BY Name ASC")->fetchAll();

// Get counts for stats
$totalAdmins = count($admins);
$totalAdvisers = count($advisers);
$totalOfficers = count($comeaOfficers);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Management | Online School Election System</title>
    <link rel="stylesheet" href="../Design/Style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .Officer_Card_Container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 24px;
            margin-top: 24px;
        }
        .Officer_Role_Card {
            background: white;
            border-radius: 16px;
            border: 1px solid var(--Border_Color);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .Role_Card_Header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--Border_Color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .Role_Card_Title {
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .Role_Icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1rem;
        }
        .Icon_Admin { background: #EEF2FF; color: #4F46E5; }
        .Icon_Adviser { background: #FFF7ED; color: #EA580C; }
        .Icon_Officer { background: #ECFDF5; color: #059669; }

        .Officer_List {
            padding: 12px;
            flex: 1;
        }
        .Officer_Item {
            padding: 12px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: background 0.2s;
        }
        .Officer_Item:hover {
            background: var(--Background_Color);
        }
        .Officer_Avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--Primary_Light);
            color: var(--Primary_Color);
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 700;
            overflow: hidden;
        }
        .Officer_Info {
            flex: 1;
        }
        .Officer_Name {
            font-weight: 600;
            font-size: 0.95rem;
        }
        .Officer_Email {
            font-size: 0.75rem;
            color: var(--Text_Secondary);
        }
        .Officer_Actions {
            display: flex;
            gap: 4px;
        }
        .Officer_Action_Btn {
            padding: 8px;
            border: none;
            background: none;
            color: var(--Text_Secondary);
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .Officer_Action_Btn:hover {
            background: #F1F5F9;
            color: var(--Primary_Color);
        }
        .Btn_Delete:hover {
            background: #FEE2E2;
            color: var(--Error_Color);
        }
        
        .Add_Section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--Border_Color);
            margin-bottom: 32px;
        }
        .Form_Grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
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
                    <div class="Logo_Title" style="font-size: 1.1rem; color: var(--Primary_Color);">ElectionSystem</div>
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
                <a href="Officers.php" class="Nav_Item Active">
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
                    <input type="text" placeholder="Search officers...">
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
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <h1 class="Page_Title">Officer Management</h1>
                            <p class="Page_Subtitle">Manage system administrators and COMEA personnel</p>
                        </div>
                    </div>
                </div>

                <!-- Registration Form -->
                <div class="Add_Section">
                    <h2 class="Card_Title" style="margin-bottom: 20px;"><i class="fas fa-plus-circle" style="color: var(--Primary_Color);"></i> Register New Officer</h2>
                    <form action="../Logic/Backend/Add_Officer_Handler.php" method="POST">
                        <div class="Form_Grid">
                            <div class="Form_Group">
                                <label class="Label">Full Name</label>
                                <input type="text" name="name" class="Input" placeholder="Enter name" required>
                            </div>
                            <div class="Form_Group">
                                <label class="Label">Email</label>
                                <input type="email" name="email" class="Input" placeholder="email@school.edu" required>
                            </div>
                            <div class="Form_Group">
                                <label class="Label">User ID (Leave blank for random)</label>
                                <input type="text" name="user_id" class="Input" placeholder="00-0000">
                            </div>
                            <div class="Form_Group">
                                <label class="Label">Role</label>
                                <select name="role" class="Select" style="width: 100%; height: 50px;" required>
                                    <option value="Admin">Admin</option>
                                    <option value="COMEA Adviser">COMEA Adviser</option>
                                    <option value="COMEA Officer">COMEA Officer</option>
                                </select>
                            </div>
                            <div class="Form_Group">
                                <label class="Label">Password (Leave blank for default)</label>
                                <input type="password" name="password" class="Input" placeholder="Set password">
                            </div>
                            <div class="Form_Group" style="display: flex; align-items: flex-end;">
                                <button type="submit" class="Button_Primary" style="margin-top: 0; width: 100%;">
                                    <i class="fas fa-save"></i> Save Officer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Roles Cards -->
                <div class="Officer_Card_Container">
                    <!-- Admin Card -->
                    <div class="Officer_Role_Card">
                        <div class="Role_Card_Header">
                            <div class="Role_Card_Title">
                                <div class="Role_Icon Icon_Admin"><i class="fas fa-user-cog"></i></div>
                                Admins
                            </div>
                            <span class="Badge Badge_Success"><?php echo $totalAdmins; ?> Active</span>
                        </div>
                        <div class="Officer_List">
                            <?php foreach ($admins as $admin): ?>
                            <div class="Officer_Item">
                                <div class="Officer_Avatar">
                                    <?php if (!empty($admin['Photo'])): ?>
                                        <img src="<?php echo htmlspecialchars($admin['Photo']); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($admin['Name'], 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="Officer_Info">
                                    <div class="Officer_Name"><?php echo htmlspecialchars($admin['Name']); ?></div>
                                    <div class="Officer_Email"><?php echo htmlspecialchars($admin['User_ID']); ?> | <?php echo htmlspecialchars($admin['Email']); ?></div>
                                </div>
                                <div class="Officer_Actions">
                                    <button class="Officer_Action_Btn" title="Edit" onclick='openEditModal(<?php echo json_encode($admin); ?>)'><i class="fas fa-edit"></i></button>
                                    <?php if ($admin['User_ID'] !== $_SESSION['User_ID']): ?>
                                    <button class="Officer_Action_Btn Btn_Delete" title="Delete" onclick="deleteOfficer('<?php echo $admin['User_ID']; ?>', '<?php echo htmlspecialchars($admin['Name']); ?>')"><i class="fas fa-trash-alt"></i></button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Adviser Card -->
                    <div class="Officer_Role_Card">
                        <div class="Role_Card_Header">
                            <div class="Role_Card_Title">
                                <div class="Role_Icon Icon_Adviser"><i class="fas fa-user-tie"></i></div>
                                COMEA Advisers
                            </div>
                            <span class="Badge" style="background: #FFF7ED; color: #EA580C;"><?php echo $totalAdvisers; ?> Active</span>
                        </div>
                        <div class="Officer_List">
                            <?php if (empty($advisers)): ?>
                                <p style="padding: 24px; text-align: center; color: var(--Text_Secondary); font-size: 0.85rem;">No advisers registered yet.</p>
                            <?php endif; ?>
                            <?php foreach ($advisers as $adviser): ?>
                            <div class="Officer_Item">
                                <div class="Officer_Avatar">
                                    <?php if (!empty($adviser['Photo'])): ?>
                                        <img src="<?php echo htmlspecialchars($adviser['Photo']); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($adviser['Name'], 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="Officer_Info">
                                    <div class="Officer_Name"><?php echo htmlspecialchars($adviser['Name']); ?></div>
                                    <div class="Officer_Email"><?php echo htmlspecialchars($adviser['User_ID']); ?> | <?php echo htmlspecialchars($adviser['Email']); ?></div>
                                </div>
                                <div class="Officer_Actions">
                                    <button class="Officer_Action_Btn" title="Edit" onclick='openEditModal(<?php echo json_encode($adviser); ?>)'><i class="fas fa-edit"></i></button>
                                    <button class="Officer_Action_Btn Btn_Delete" title="Delete" onclick="deleteOfficer('<?php echo $adviser['User_ID']; ?>', '<?php echo htmlspecialchars($adviser['Name']); ?>')"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Officer Card -->
                    <div class="Officer_Role_Card">
                        <div class="Role_Card_Header">
                            <div class="Role_Card_Title">
                                <div class="Role_Icon Icon_Officer"><i class="fas fa-user-shield"></i></div>
                                COMEA Officers
                            </div>
                            <span class="Badge" style="background: #ECFDF5; color: #059669;"><?php echo $totalOfficers; ?> Active</span>
                        </div>
                        <div class="Officer_List">
                            <?php if (empty($comeaOfficers)): ?>
                                <p style="padding: 24px; text-align: center; color: var(--Text_Secondary); font-size: 0.85rem;">No officers registered yet.</p>
                            <?php endif; ?>
                            <?php foreach ($comeaOfficers as $officer): ?>
                            <div class="Officer_Item">
                                <div class="Officer_Avatar">
                                    <?php if (!empty($officer['Photo'])): ?>
                                        <img src="<?php echo htmlspecialchars($officer['Photo']); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($officer['Name'], 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="Officer_Info">
                                    <div class="Officer_Name"><?php echo htmlspecialchars($officer['Name']); ?></div>
                                    <div class="Officer_Email"><?php echo htmlspecialchars($officer['User_ID']); ?> | <?php echo htmlspecialchars($officer['Email']); ?></div>
                                </div>
                                <div class="Officer_Actions">
                                    <button class="Officer_Action_Btn" title="Edit" onclick='openEditModal(<?php echo json_encode($officer); ?>)'><i class="fas fa-edit"></i></button>
                                    <button class="Officer_Action_Btn Btn_Delete" title="Delete" onclick="deleteOfficer('<?php echo $officer['User_ID']; ?>', '<?php echo htmlspecialchars($officer['Name']); ?>')"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Modal -->
    <div id="EditModal" class="Modal">
        <div class="Modal_Content">
            <div class="Modal_Header">
                <h2 class="Modal_Title">Edit Officer</h2>
                <span class="Modal_Close" onclick="closeModal()">&times;</span>
            </div>
            <form action="../Logic/Backend/Edit_Officer_Handler.php" method="POST">
                <input type="hidden" name="original_user_id" id="edit_original_id">
                <div class="Form_Group">
                    <label class="Label">Full Name</label>
                    <input type="text" name="name" id="edit_name" class="Input" required>
                </div>
                <div class="Form_Group" style="margin-top: 15px;">
                    <label class="Label">Email</label>
                    <input type="email" name="email" id="edit_email" class="Input" required>
                </div>
                <div class="Form_Group" style="margin-top: 15px;">
                    <label class="Label">User ID</label>
                    <input type="text" name="user_id" id="edit_user_id" class="Input" required>
                </div>
                <div class="Form_Group" style="margin-top: 15px;">
                    <label class="Label">Role</label>
                    <select name="role" id="edit_role" class="Select" style="width: 100%; height: 50px;" required>
                        <option value="Admin">Admin</option>
                        <option value="COMEA Adviser">COMEA Adviser</option>
                        <option value="COMEA Officer">COMEA Officer</option>
                    </select>
                </div>
                <div class="Form_Group" style="margin-top: 15px;">
                    <label class="Label">New Password (Leave blank to keep current)</label>
                    <input type="password" name="password" class="Input" placeholder="Enter new password">
                </div>
                <button type="submit" class="Button_Primary" style="width: 100%; margin-top: 24px;">Update Officer</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(officer) {
            document.getElementById('edit_original_id').value = officer.User_ID;
            document.getElementById('edit_user_id').value = officer.User_ID;
            document.getElementById('edit_name').value = officer.Name;
            document.getElementById('edit_email').value = officer.Email;
            document.getElementById('edit_role').value = officer.Role;
            document.getElementById('EditModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('EditModal').style.display = 'none';
        }

        function deleteOfficer(id, name) {
            if (confirm(`Are you sure you want to delete officer ${name} (${id})? This action cannot be undone.`)) {
                window.location.href = `../Logic/Backend/Delete_Officer_Handler.php?user_id=${id}`;
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('EditModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>