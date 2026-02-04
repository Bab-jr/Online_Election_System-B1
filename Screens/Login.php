<?php
session_start();
require_once __DIR__ . '/../Logic/Backend/Authentication_Handler.php';

$Error = '';
$ShowModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ShowModal = true;
    $Handler = new Authentication_Handler();
    $Result = $Handler->Login(
        $_POST['User_Type'],
        $_POST['Email'],
        $_POST['Password'],
        $_POST['User_ID']
    );

    if ($Result['success']) {
        if ($_SESSION['Access_Level'] >= 1) {
            header('Location: Election_Dashboard.php');
        } else {
            header('Location: Voting_Screen.php');
        }
        exit;
    } else {
        $Error = $Result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Click to Vote - School Election System</title>
    <link rel="stylesheet" href="../Design/Style.css?v=<?php echo time(); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="Landing_Wrapper">
        <div class="Landing_Header">
            <div class="Logo_Pill">
                <div class="Logo_Circle">
                    <img src="../assets/pasted-20260204-200612-dffb96f0.png" alt="INHS Logo">
                </div>
                <div class="Logo_Text">
                    <div class="Logo_Title">Iloilo National High School</div>
                    <div class="Logo_Subtitle">Luna St., La Paz, Iloilo City</div>
                </div>
            </div>
        </div>

        <div class="Landing_Content">
            <h1 class="Landing_Title">Click to Vote</h1>
            <p class="Landing_Description">
                This portal is currently a <strong>PROTOTYPE MODEL</strong> under active development to demonstrate the digital election process. 
                Please be aware that all features are in a testing phase and interactions are for demonstration purposes only. 
                We are continuously refining the system to ensure a seamless experience for the final implementation.
            </p>
            <button class="Btn_Landing_Login" onclick="ToggleModal(true)">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                    <polyline points="10 17 15 12 10 7"></polyline>
                    <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
                Login
            </button>
        </div>

        <div class="Landing_Footer">
            © 2026 Click to Vote System [PROTOTYPE]. All Rights Reserved.
        </div>
    </div>

    <!-- Login Modal -->
    <div id="Login_Modal" class="Modal_Overlay <?php echo $ShowModal ? 'Active' : ''; ?>">
        <div class="Login_Card">
            <button class="Btn_Close_Modal" onclick="ToggleModal(false)">&times;</button>
            <h2 class="Title_Large">Sign In</h2>
            <p class="Text_Muted">Access the election management system</p>

            <?php if ($Error): ?>
                <div class="Alert Alert_Error"><?php echo $Error; ?></div>
            <?php endif; ?>

            <form action="Login.php" method="POST">
                <div class="Form_Group">
                    <label class="Label" for="User_Type">User Type</label>
                    <select class="Input" name="User_Type" id="User_Type" required>
                        <option value="Admin">Admin</option>
                        <option value="COMEA Adviser">COMEA Adviser</option>
                        <option value="COMEA Officer">COMEA Officer</option>
                        <option value="Voter">Voter</option>
                    </select>
                </div>

                <div class="Form_Group">
                    <label class="Label" for="User_ID">User ID</label>
                    <input class="Input" type="text" name="User_ID" id="User_ID" placeholder="e.g. ADMIN-001" required>
                </div>

                <div class="Form_Group">
                    <label class="Label" for="Email">Email Address</label>
                    <input class="Input" type="email" name="Email" id="Email" placeholder="name@school.edu" required>
                </div>

                <div class="Form_Group">
                    <label class="Label" for="Password">Password</label>
                    <div class="Password_Toggle_Wrapper">
                        <input class="Input" type="password" name="Password" id="Password" required>
                        <button type="button" class="Toggle_Button" onclick="TogglePassword()">SHOW</button>
                    </div>
                </div>

                <button type="submit" class="Button_Primary">Login to System</button>
            </form>
        </div>
    </div>

    <script>
        function ToggleModal(show) {
            const modal = document.getElementById('Login_Modal');
            if (show) {
                modal.classList.add('Active');
            } else {
                modal.classList.remove('Active');
            }
        }

        function TogglePassword() {
            const passwordInput = document.getElementById('Password');
            const toggleBtn = event.currentTarget;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = 'HIDE';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = 'SHOW';
            }
        }

        // Close modal on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') ToggleModal(false);
        });

        // Close modal on clicking outside the card
        document.getElementById('Login_Modal').addEventListener('click', (e) => {
            if (e.target.id === 'Login_Modal') ToggleModal(false);
        });
    </script>
</body>
</html>