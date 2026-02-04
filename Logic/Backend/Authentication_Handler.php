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
        
        $sql = "SELECT * FROM $Table WHERE Email = ? AND User_ID = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$Email, $User_ID]);
        $User = $stmt->fetch();

        if ($User && password_verify($Password, $User['Password'])) {
            $_SESSION['User_ID'] = $User['User_ID'];
            $_SESSION['User_Role'] = ($User_Type === 'Voter') ? 'Voter' : $User['Role'];
            $_SESSION['Access_Level'] = ($User_Type === 'Voter') ? 0 : $User['Access_Level'];
            $_SESSION['User_Name'] = $User['Name'] ?? 'Voter';

            // Log to Audit_Trail if Access_Level >= 1
            if ($_SESSION['Access_Level'] >= 1) {
                $this->Log_Action($User['User_ID'], $_SESSION['User_Role'], 'Login', 'Successful login to the system');
            }

            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Invalid credentials'];
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
        session_destroy();
        header('Location: ../../Screens/Login.php');
        exit;
    }
}
