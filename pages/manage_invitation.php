<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");

// Σύνδεση με βάση δεδομένων
$conn = new mysqli("localhost", "root", "", "web-project");
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Σφάλμα σύνδεσης με τη βάση δεδομένων: " . $conn->connect_error]));
}

// Ανάκτηση `user_id` από τη συνεδρία
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die(json_encode(["success" => false, "message" => "Ο χρήστης δεν είναι συνδεδεμένος."]));
}

// Ανάκτηση `professor_id` από τον πίνακα users
$sql = "SELECT professor_id FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $professor_id = $row['professor_id'];
} else {
    die(json_encode(["success" => false, "message" => "Δεν βρέθηκε καθηγητής για τον συνδεδεμένο χρήστη."]));
}
$stmt->close();

// Λήψη δεδομένων από το αίτημα
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    die(json_encode(["success" => false, "message" => "Μη έγκυρα δεδομένα JSON."]));
}

$invitation_id = $data['id'] ?? null;
$action = $data['action'] ?? null;

if (!$invitation_id || !in_array($action, ['accept', 'reject'])) {
    die(json_encode(["success" => false, "message" => "Μη έγκυρα δεδομένα εισόδου."]));
}

// Έλεγχος για την ύπαρξη και την κατάσταση της πρόσκλησης
$sql = "SELECT cr.id, cr.status, cr.professor_id, cr.student_id, cr.theses_id
    FROM committee_requests cr
    WHERE cr.professor_id = ? AND cr.id = ? AND cr.status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $professor_id, $invitation_id);
$stmt->execute();
$result = $stmt->get_result();

$request = $result->fetch_assoc();
if (!$request) {
    die(json_encode(["success" => false, "message" => "Η αίτηση δεν βρέθηκε ή έχει ήδη επεξεργαστεί."]));
}
$theses_id = $request['theses_id'];
$stmt->close();

$conn->begin_transaction();
try {
    if ($action == 'accept') {
        // Αποδοχή πρόσκλησης
        $update_sql = "UPDATE committee_requests 
                       SET status = 'approved', accept_date = NOW(), reject_date = NULL 
                       WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $invitation_id);
        $update_stmt->execute();
        $update_stmt->close();

        // Εισαγωγή στον πίνακα thesis_committee
        $role = "committee_member";
        $insert_sql = "INSERT INTO thesis_committee (theses_id, professor_id, role) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iis", $theses_id, $professor_id, $role);
        $insert_stmt->execute();
        $insert_stmt->close();
    } elseif ($action == 'reject') {
        // Απόρριψη πρόσκλησης
        $update_sql = "UPDATE committee_requests 
                       SET status = 'rejected', reject_date = NOW(), accept_date = NULL 
                       WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $invitation_id);
        $update_stmt->execute();
        $update_stmt->close();
    }

    // Έλεγχος για αρκετές αποδοχές
    $check_stmt = $conn->prepare("SELECT COUNT(*) as accepted_count 
        FROM committee_requests 
        WHERE theses_id = ? AND status = 'approved'");
    $check_stmt->bind_param("i", $theses_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['accepted_count'] >= 2) {
       
          // Ενημέρωση του status σε active στον πίνακα theses
    $update_thesis_stmt = $conn->prepare("
    UPDATE theses
    SET status = 'active'
    WHERE theses_id = ?
    ");

        // Δέσμευση παραμέτρων και εκτέλεση του update
        $update_thesis_stmt->bind_param("i", $theses_id);
        $update_thesis_stmt->execute();
        $update_thesis_stmt->close();
    
        // Ακύρωση αιτήσεων που είναι σε κατάσταση 'pending'
        $cancel_stmt = $conn->prepare("
            UPDATE committee_requests 
            SET status = 'cancelled' 
            WHERE theses_id = ? AND status = 'pending'
        ");
        $cancel_stmt->bind_param("i", $theses_id);
        $cancel_stmt->execute();
        $cancel_stmt->close();
    }
    
    $conn->commit();
    echo json_encode(["success" => true, "message" => "Η ενέργεια ολοκληρώθηκε επιτυχώς."]);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Transaction Error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Σφάλμα: " . $e->getMessage()]);
}

$conn->close();
?>
