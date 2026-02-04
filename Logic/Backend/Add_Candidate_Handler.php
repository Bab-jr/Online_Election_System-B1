<?php
require_once __DIR__ . '/Authentication_Handler.php';
require_once __DIR__ . '/../../db/config.php';

$Auth = new Authentication_Handler();
$Auth->Check_Auth();

if ($_SESSION['Access_Level'] < 1) {
    header('Location: ../../Screens/Voting_Screen.php');
    exit;
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
    
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $position = $_POST['position'] ?? '';
    $party = $_POST['party'] ?? '';
    $grade = $_POST['grade'] ?? '';
    $track = $_POST['track'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // User ID generation if empty
    if (empty($user_id)) {
        $userId = generateUserID($db);
    } else {
        $userId = $user_id;
    }

    // Default password
    if (empty($password)) {
        $password = "School@123";
    }
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Handle Photo Upload
    $photoPath = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../assets/images/candidates/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }
        
        $fileExt = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $fileName = $userId . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            $photoPath = 'assets/images/candidates/' . $fileName;
        }
    }
    
    try {
        $stmt = $db->prepare("INSERT INTO Candidates (User_ID, Name, Email, Position, Party, Grade_Level, Track_Cluster, Photo, Password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $name, $email, $position, $party, $grade, $track, $photoPath, $hashedPassword]);
        
        // Log the action
        $Auth->Log_Action($_SESSION['User_ID'], $_SESSION['User_Role'], 'Add Candidate', "Added candidate $name for $position. ID: $userId");
        
        header('Location: ../../Screens/Candidates.php?success=Candidate+added+with+ID:+' . $userId);
    } catch (Exception $e) {
        // Redirect with error
        header('Location: ../../Screens/Candidates.php?error=' . urlencode($e->getMessage()));
    }
    exit;
}
