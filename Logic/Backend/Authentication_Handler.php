<?php
session_start();
require_once __DIR__ . '/../../db/config.php';

class Authentication_Handler {
    private $pdo;

    public function __construct() {
        $this->pdo = db();
    }

    public function Login($User_Type, $Email, $Password, $User_ID) {
        $Table = ($User_Type === 'Voter') ? 'Voters' : 'Officers';
        
        // Try to find user by UID first as it's the primary key
        $sql = "SELECT * FROM $Table WHERE User_ID = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$User_ID]);
        $User = $stmt->fetch();

        // If not found and email is provided, try by Email
        if (!$User && !empty($Email)) {
            $sql = "SELECT * FROM $Table WHERE Email = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$Email]);
            $User = $stmt->fetch();
        }

        if ($User && password_verify($Password, $User['Password'])) {
            $_SESSION['User_ID'] = $User['User_ID'];
            $_SESSION['User_Role'] = ($User_Type === 'Voter') ? 'Voter' : $User['Role'];
            $_SESSION['Access_Level'] = ($User_Type === 'Voter') ? 0 : ($User['Access_Level'] ?? 0);
            $_SESSION['User_Name'] = $User['Name'] ?? ($User_Type === 'Voter' ? 'Voter' : 'User');

            // Log to Audit_Trail if Access_Level >= 1
            if ($_SESSION['Access_Level'] >= 1) {
                $this->Log_Action($User['User_ID'], $_SESSION['User_Role'], 'Login', 'Successful login to the system');
            }

            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Invalid credentials. Please check your UID/Email and Password.'];
    }

    public function Log_Action($User_ID, $User_Role, $Action_Type, $Action_Details) {
        $sql = "INSERT INTO Audit_Trail (User_ID, User_Role, Action_Type, Action_Details) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$User_ID, $User_Role, $Action_Type, $Action_Details]);
    }

    public function Check_Auth() {
        if (!isset($_SESSION['User_ID'])) {
            header('Location: ../../Screens/Login.php');
            exit;
        }
    }

    public function Logout() {
        if (isset($_SESSION['User_ID']) && isset($_SESSION['Access_Level']) && $_SESSION['Access_Level'] >= 1) {
            $this->Log_Action($_SESSION['User_ID'], $_SESSION['User_Role'], 'Logout', 'User logged out of the system');
        }
        session_destroy();
        header('Location: ../../Screens/Login.php');
        exit;
    }
}

// Handle POST request for Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['User_ID']) && isset($_POST['Password'])) {
    $handler = new Authentication_Handler();
    $userType = $_POST['User_Type'] ?? 'Voter';
    $email = $_POST['Email'] ?? '';
    $uid = $_POST['User_ID'];
    $password = $_POST['Password'];

    $result = $handler->Login($userType, $email, $password, $uid);

    if ($result['success']) {
        if ($_SESSION['Access_Level'] >= 1) {
            header('Location: ../../Screens/Election_Dashboard.php');
        } else {
            header('Location: ../../Screens/Voting_Screen.php');
        }
        exit;
    } else {
        header('Location: ../../Screens/Login.php?error=' . urlencode($result['error']));
        exit;
    }
}