<?php
require_once __DIR__ . '/Authentication_Handler.php';
require_once __DIR__ . '/../../db/config.php';

$Auth = new Authentication_Handler();
$Auth->Check_Auth();

if ($_SESSION['Access_Level'] < 3) {
    die("Unauthorized access");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = db();
    
    $original_id = $_POST['original_user_id'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($original_id) || empty($user_id) || empty($name) || empty($email) || empty($role)) {
        header("Location: ../../Screens/Officers.php?error=Missing+fields");
        exit;
    }

    // Determine Access Level
    $accessLevel = 1;
    if ($role === 'Admin') $accessLevel = 3;
    elseif ($role === 'COMEA Adviser') $accessLevel = 2;

    try {
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE Officers SET User_ID = ?, Name = ?, Email = ?, Role = ?, Access_Level = ?, Password = ? WHERE User_ID = ?");
            $stmt->execute([$user_id, $name, $email, $role, $accessLevel, $hashedPassword, $original_id]);
        } else {
            $stmt = $db->prepare("UPDATE Officers SET User_ID = ?, Name = ?, Email = ?, Role = ?, Access_Level = ? WHERE User_ID = ?");
            $stmt->execute([$user_id, $name, $email, $role, $accessLevel, $original_id]);
        }

        // If the user edited their own ID, update the session
        if ($original_id === $_SESSION['User_ID']) {
            $_SESSION['User_ID'] = $user_id;
            $_SESSION['User_Name'] = $name;
            $_SESSION['User_Role'] = $role;
            $_SESSION['Access_Level'] = $accessLevel;
        }

        // Log to Audit Trail
        $Auth->Log_Action($_SESSION['User_ID'], $_SESSION['User_Role'], 'Edit Officer', "Updated officer: $name ($role). User ID: $user_id");

        header("Location: ../../Screens/Officers.php?success=Officer+updated+successfully");
    } catch (PDOException $e) {
        header("Location: ../../Screens/Officers.php?error=Update+failed:+" . urlencode($e->getMessage()));
    }
} else {
    header("Location: ../../Screens/Officers.php");
}
