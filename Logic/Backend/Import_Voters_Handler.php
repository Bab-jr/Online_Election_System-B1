<?php
require_once __DIR__ . '/Authentication_Handler.php';
require_once __DIR__ . '/../../db/config.php';

$Auth = new Authentication_Handler();
$Auth->Check_Auth();

if ($_SESSION['Access_Level'] < 1) {
    header('Location: ../../Screens/Login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    
    if (!is_uploaded_file($file)) {
        header("Location: ../../Screens/Voters.php?error=No file uploaded");
        exit;
    }

    $handle = fopen($file, "r");
    if ($handle === false) {
        header("Location: ../../Screens/Voters.php?error=Could not open file");
        exit;
    }

    $db = db();
    $db->beginTransaction();

    try {
        // Skip header
        $header = fgetcsv($handle);
        
        $importedCount = 0;
        while (($data = fgetcsv($handle)) !== false) {
            // Mapping: User_ID, Email, Track_Cluster, Grade_Level, Section, Password
            $userId = $data[0] ?: 'V-' . bin2hex(random_bytes(4));
            $email = $data[1];
            $track = $data[2];
            $grade = intval($data[3]);
            $section = $data[4];
            $password = !empty($data[5]) ? password_hash($data[5], PASSWORD_DEFAULT) : password_hash('Voter123', PASSWORD_DEFAULT);

            if (empty($email)) continue;

            $stmt = $db->prepare("INSERT INTO Voters (User_ID, Email, Password, Track_Cluster, Grade_Level, Section) VALUES (?, ?, ?, ?, ?, ?) 
                                 ON DUPLICATE KEY UPDATE Email = VALUES(Email), Track_Cluster = VALUES(Track_Cluster), Grade_Level = VALUES(Grade_Level), Section = VALUES(Section)");
            $stmt->execute([$userId, $email, $password, $track, $grade, $section]);
            $importedCount++;
        }

        $db->commit();
        fclose($handle);

        $Auth->Log_Action($_SESSION['User_ID'], $_SESSION['User_Role'], 'Import Voters', "Imported $importedCount voters via CSV");
        header("Location: ../../Screens/Voters.php?success=Successfully imported $importedCount voters");
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        fclose($handle);
        header("Location: ../../Screens/Voters.php?error=Error importing CSV: " . $e->getMessage());
        exit;
    }
} else {
    header("Location: ../../Screens/Voters.php");
    exit;
}
