<?php
require_once __DIR__ . '/Authentication_Handler.php';
require_once __DIR__ . '/../../db/config.php';

$Auth = new Authentication_Handler();
$Auth->Check_Auth();

if ($_SESSION['Access_Level'] < 1) {
    die("Unauthorized access");
}

function generateUserID($db) {
    $tables = ['Officers', 'Voters', 'Candidates'];
    do {
        $id = sprintf("%02d-%04d", rand(0, 99), rand(0, 9999));
        $exists = false;
        foreach ($tables as $table) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM $table WHERE User_ID = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                $exists = true;
                break;
            }
        }
    } while ($exists);
    return $id;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = db();
    
    $email = $_POST['email'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    $track = $_POST['track'] ?? '';
    $grade = $_POST['grade'] ?? '';
    $section = $_POST['section'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($track) || empty($grade) || empty($section)) {
        header("Location: ../../Screens/Voters.php?error=Missing+fields");
        exit;
    }

    // User ID logic
    if (empty($user_id)) {
        $user_id = generateUserID($db);
    }

    // Password logic
    if (empty($password)) {
        $password = "School@123";
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $db->prepare("INSERT INTO Voters (User_ID, Email, Password, Track_Cluster, Grade_Level, Section) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $email, $hashedPassword, $track, $grade, $section]);

        // Log to Audit Trail
        $Auth->Log_Action($_SESSION['User_ID'], $_SESSION['User_Role'], 'Add Voter', "Registered new voter: $email. User ID: $user_id");

        header("Location: ../../Screens/Voters.php?success=Voter+registered+with+ID:+$user_id");
    } catch (PDOException $e) {
        header("Location: ../../Screens/Voters.php?error=Registration+failed:+" . urlencode($e->getMessage()));
    }
} else {
    header("Location: ../../Screens/Voters.php");
}
