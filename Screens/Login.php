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
        :root {
            --Modal_Header_Bg: #4E66D1; /* Specific blue from screenshot */
        }

        .Landing_Wrapper {
            background-image: url('../assets/pasted-20260204-200305-388a4105.jpg');
        }
        
        .Landing_Info_Card {
            background: white;
            padding: 0;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            max-width: 850px;
            width: 90%;
            margin: 0 auto;
            border: none;
        }

        .Landing_Card_Header {
            background: var(--Modal_Header_Bg);
            padding: 20px 30px;
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .Landing_Card_Body {
            padding: 40px 48px;
            color: var(--Text_Primary);
            text-align: left;
        }

        .Landing_Title {
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            margin-bottom: 16px;
            font-weight: 800;
            color: var(--Text_Primary);
            text-transform: none;
            letter-spacing: normal;
        }

        .Landing_Description {
            margin-bottom: 32px;
            font-size: 1.1rem;
            color: var(--Text_Secondary);
        }

        /* Modal Redesign */
        .Login_Card {
            padding: 0;
            border-radius: 20px;
            overflow: hidden;
            border: none;
            max-width: 480px;
        }

        .Modal_Header {
            background: var(--Modal_Header_Bg);
            padding: 24px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }

        .Modal_Header_Content {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
            justify-content: center;
        }

        .Modal_Header_Title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .Modal_Header_Icon {
            font-size: 1.8rem;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 6px;
            border-radius: 8px;
        }

        .Btn_Close_Modal_New {
            background: rgba(15, 23, 42, 0.4);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 0.9rem;
        }

        .Btn_Close_Modal_New:hover {
            background: rgba(15, 23, 42, 0.6);
        }

        .Modal_Body {
            padding: 32px 40px;
        }

        .Form_Group_New {
            margin-bottom: 24px;
        }

        .Label_New {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #475569;
            text-transform: uppercase;
        }

        .Input_Wrapper {
            position: relative;
        }

        .Input_Icon_Left {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748B;
            font-size: 1.1rem;
        }

        .Input_New {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #E2E8F0;
            border-radius: 12px;
            font-size: 1rem;
            color: #1E293B;
            transition: all 0.2s;
            background: white;
        }

        .Input_New::placeholder {
            color: #94A3B8;
        }

        .Input_New:focus {
            outline: none;
            border-color: var(--Modal_Header_Bg);
            box-shadow: 0 0 0 4px rgba(78, 102, 209, 0.1);
        }

        .Select_New {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748B'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 18px;
        }

        .Btn_Password_Toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748B;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            font-size: 1.1rem;
        }

        .Btn_Login_Submit {
            width: 100%;
            padding: 16px;
            background: var(--Modal_Header_Bg);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 8px;
            text-transform: uppercase;
        }

        .Btn_Login_Submit:hover {
            background: #3D54B8;
            transform: translateY(-1px);
            box-shadow: 0 8px 15px rgba(78, 102, 209, 0.3);
        }

        .Forgot_Password {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #4E66D1;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .Forgot_Password:hover {
            text-decoration: underline;
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
                <div class="Landing_Card_Header">
                    <i class="fas fa-info-circle" style="font-size: 1.5rem;"></i>
                    <span style="font-weight: 700; letter-spacing: 1px;">SYSTEM INFORMATION</span>
                </div>
                <div class="Landing_Card_Body">
                    <h1 class="Landing_Title">CLICK TO VOTE</h1>
                    <p class="Landing_Description">
                        This portal is currently a <strong>PROTOTYPE MODEL</strong> under active development to demonstrate the digital election process. 
                        Please be aware that all features are in a testing phase and interactions are for demonstration purposes only. We are 
                        continuously refining the system to ensure a seamless experience for the final implementation.
                    </p>
                    <button id="Open_Login_Btn" class="Btn_Landing_Login" style="background: var(--Modal_Header_Bg); color: white;">
                        <i class="fas fa-sign-in-alt"></i> LOGIN
                    </button>
                </div>
            </div>
        </main>

        <footer class="Landing_Footer">
            &copy; 2026 Click to Vote System [PROTOTYPE]. All Rights Reserved.
        </footer>
    </div>

    <!-- Login Modal -->
    <div id="Login_Modal" class="Modal_Overlay">
        <div class="Login_Card">
            <div class="Modal_Header">
                <div class="Modal_Header_Content">
                    <i class="fas fa-users-viewfinder Modal_Header_Icon"></i>
                    <h2 class="Modal_Header_Title">Login</h2>
                </div>
                <button class="Btn_Close_Modal_New" id="Close_Login_Btn" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="Modal_Body">
                <div id="Error_Alert" class="Alert Alert_Error" style="padding: 12px; border-radius: 10px; font-size: 0.85rem; margin-bottom: 20px; display: none; background: #FEE2E2; color: #EF4444; border: 1px solid #FCA5A5;">
                    <i class="fas fa-exclamation-circle"></i> <span id="Error_Message"></span>
                </div>

                <form action="../Logic/Backend/Authentication_Handler.php" method="POST">
                    <div class="Form_Group_New">
                        <label class="Label_New" for="User_Type">User Type</label>
                        <div class="Input_Wrapper">
                            <i class="fas fa-user-check Input_Icon_Left"></i>
                            <select class="Input_New Select_New" name="User_Type_Select" id="User_Type_Select">
                                <option value="Voter">Voter</option>
                                <option value="Adviser">Adviser</option>
                                <option value="Officer">Officer</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="Form_Group_New">
                        <label class="Label_New" for="User_ID">UID</label>
                        <div class="Input_Wrapper">
                            <i class="fas fa-id-card Input_Icon_Left"></i>
                            <input class="Input_New" type="text" name="User_ID" id="User_ID" placeholder="00-0000" required>
                        </div>
                    </div>

                    <div class="Form_Group_New">
                        <label class="Label_New" for="Email">Email Account</label>
                        <div class="Input_Wrapper">
                            <i class="fas fa-envelope Input_Icon_Left"></i>
                            <input class="Input_New" type="email" name="Email" id="Email" placeholder="firstname.lastname@iloilonhs.edu.ph">
                        </div>
                    </div>

                    <div class="Form_Group_New">
                        <label class="Label_New" for="Password">Password</label>
                        <div class="Input_Wrapper">
                            <i class="fas fa-lock Input_Icon_Left"></i>
                            <input class="Input_New" type="password" name="Password" id="Password" placeholder="Enter your password" required>
                            <button type="button" id="Toggle_Password" class="Btn_Password_Toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="User_Type" id="User_Type_Hidden" value="Voter">

                    <button type="submit" class="Btn_Login_Submit">
                        <i class="fas fa-sign-in-alt"></i> LOGIN
                    </button>

                    <a href="#" class="Forgot_Password">Forgot Password?</a>
                </form>
            </div>
        </div>
    </div>

    <script>
        const loginModal = document.getElementById('Login_Modal');
        const openLoginBtn = document.getElementById('Open_Login_Btn');
        const closeLoginBtn = document.getElementById('Close_Login_Btn');
        const userTypeSelect = document.getElementById('User_Type_Select');
        const userTypeHidden = document.getElementById('User_Type_Hidden');
        const togglePassword = document.getElementById('Toggle_Password');
        const passwordInput = document.getElementById('Password');

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

        // Sync Select to Hidden Input (if backend expects specific User_Type field)
        userTypeSelect.addEventListener('change', (e) => {
            userTypeHidden.value = e.target.value;
        });

        // Toggle Password Visibility
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.querySelector('i').classList.toggle('fa-eye');
            togglePassword.querySelector('i').classList.toggle('fa-eye-slash');
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