<?php
session_start();
if (isset($_SESSION['User_ID'])) {
    header('Location: Election_Dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Online School Election System</title>
    <link rel="stylesheet" href="../Design/Style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .Landing_Wrapper {
            background-image: url('../assets/pasted-20260204-200305-388a4105.jpg');
        }
        
        .Landing_Info_Card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            padding: 48px;
            border-radius: 32px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 850px;
            width: 90%;
            margin: 0 auto;
        }

        .Landing_Title {
            font-size: clamp(2rem, 6vw, 3.5rem);
            margin-bottom: 16px;
        }

        .Landing_Description {
            margin-bottom: 32px;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="Landing_Wrapper">
        <!-- Top Left Pill (Logo Section) -->
        <header class="Landing_Header">
            <div class="Logo_Pill">
                <div class="Logo_Circle">
                    <img src="../assets/pasted-20260204-200612-dffb96f0.png" alt="School Logo">
                </div>
                <div class="Logo_Text">
                    <div class="Logo_Title">Iloilo National High School</div>
                    <div class="Logo_Subtitle">Luna St., La Paz, Iloilo City</div>
                </div>
            </div>
        </header>

        <!-- Main Landing Content wrapped in a card for visual separation -->
        <main class="Landing_Content">
            <div class="Landing_Info_Card">
                <h1 class="Landing_Title">CLICK TO VOTE</h1>
                <p class="Landing_Description">
                    This portal is currently a <strong>PROTOTYPE MODEL</strong> under active development to demonstrate the digital election process. 
                    Please be aware that all features are in a testing phase and interactions are for demonstration purposes only. We are 
                    continuously refining the system to ensure a seamless experience for the final implementation.
                </p>
                <button id="Open_Login_Btn" class="Btn_Landing_Login">
                    <i class="fas fa-sign-in-alt"></i> LOGIN
                </button>
            </div>
        </main>

        <footer class="Landing_Footer">
            &copy; 2026 Click to Vote System [PROTOTYPE]. All Rights Reserved.
        </footer>
    </div>

    <!-- Login Modal -->
    <div id="Login_Modal" class="Modal_Overlay">
        <div class="Login_Card">
            <button class="Btn_Close_Modal" id="Close_Login_Btn" title="Close">
                <i class="fas fa-times"></i>
            </button>
            <div style="text-align: center; margin-bottom: 24px;">
                <div style="width: 64px; height: 64px; background: var(--Primary_Light); color: var(--Primary_Color); border-radius: 16px; display: flex; justify-content: center; align-items: center; font-size: 2rem; margin: 0 auto 16px;">
                    <i class="fas fa-vote-yea"></i>
                </div>
                <h1 class="Title_Large">Welcome Back</h1>
                <p class="Text_Muted">Sign in to manage the school election</p>
            </div>

            <div id="Error_Alert" class="Alert Alert_Error" style="padding: 12px; border-radius: 10px; font-size: 0.85rem; margin-bottom: 20px; display: none; background: #FEE2E2; color: #EF4444; border: 1px solid #FCA5A5;">
                <i class="fas fa-exclamation-circle"></i> <span id="Error_Message"></span>
            </div>

            <form action="../Logic/Backend/Authentication_Handler.php" method="POST">
                <div class="Form_Group">
                    <label class="Label" for="User_ID">User ID</label>
                    <div style="position: relative;">
                        <i class="fas fa-id-card" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--Text_Secondary);"></i>
                        <input class="Input" type="text" name="User_ID" id="User_ID" placeholder="e.g. 00-0000" required style="padding-left: 48px;">
                    </div>
                </div>
                <div class="Form_Group">
                    <label class="Label" for="Password">Password</label>
                    <div style="position: relative;">
                        <i class="fas fa-lock" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--Text_Secondary);"></i>
                        <input class="Input" type="password" name="Password" id="Password" placeholder="••••••••" required style="padding-left: 48px;">
                    </div>
                </div>
                <div class="Form_Group" style="margin-top: 10px;">
                    <label class="Label" style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer; text-transform: none; letter-spacing: normal;">
                        <input type="checkbox" name="User_Type" value="Voter" style="width: 16px; height: 16px;"> 
                        Sign in as Voter
                    </label>
                </div>
                <button type="submit" class="Button_Primary">
                    Sign In
                </button>
            </form>
        </div>
    </div>

    <script>
        const loginModal = document.getElementById('Login_Modal');
        const openLoginBtn = document.getElementById('Open_Login_Btn');
        const closeLoginBtn = document.getElementById('Close_Login_Btn');

        openLoginBtn.addEventListener('click', () => {
            loginModal.classList.add('Active');
        });

        closeLoginBtn.addEventListener('click', () => {
            loginModal.classList.remove('Active');
        });

        // Close modal when clicking outside the card
        loginModal.addEventListener('click', (e) => {
            if (e.target === loginModal) {
                loginModal.classList.remove('Active');
            }
        });

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('error')) {
            loginModal.classList.add('Active');
            const errorAlert = document.getElementById('Error_Alert');
            const errorMessage = document.getElementById('Error_Message');
            errorAlert.style.display = 'block';
            errorMessage.textContent = urlParams.get('error');
        }
    </script>
</body>
</html>