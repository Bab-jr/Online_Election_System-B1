<?php
require_once __DIR__ . '/Authentication_Handler.php';
require_once __DIR__ . '/../../db/config.php';

$Auth = new Authentication_Handler();
$Auth->Check_Auth();

if ($_SESSION['Access_Level'] < 3) {
    die("Unauthorized access");
}

$user_id = $_GET['user_id'] ?? '';

if (empty($user_id)) {
    header("Location: ../../Screens/Officers.php?error=No+user+ID+provided");
    exit;
}

if ($user_id === $_SESSION['User_ID']) {
    header("Location: ../../Screens/Officers.php?error=You+cannot+delete+yourself");
    exit;
}

$db = db();

try {
    // Get officer name for logging
    $stmt = $db->prepare("SELECT Name, Role FROM Officers WHERE User_ID = ?");
    $stmt->execute([$user_id]);
    $officer = $stmt->fetch();

    if (!$officer) {
        header("Location: ../../Screens/Officers.php?error=Officer+not+found");
        exit;
    }

    $stmt = $db->prepare("DELETE FROM Officers WHERE User_ID = ?");
    $stmt->execute([$user_id]);

    // Log to Audit Trail
    $Auth->Log_Action($_SESSION['User_ID'], $_SESSION['User_Role'], 'Delete Officer', "Deleted officer: {$officer['Name']} ({$officer['Role']}). User ID: $user_id");

    header("Location: ../../Screens/Officers.php?success=Officer+deleted+successfully");
} catch (PDOException $e) {
    header("Location: ../../Screens/Officers.php?error=Delete+failed:+" . urlencode($e->getMessage()));
}
