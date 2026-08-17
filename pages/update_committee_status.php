<?php
session_start();
header("Content-Type: application/json");

// Σύνδεση με βάση δεδομένων
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Σφάλμα σύνδεσης: " . $conn->connect_error]);
    exit;
}

// Λήψη δεδομένων από το JSON body
$data = json_decode(file_get_contents('php://input'), true);
$professor_id = $data['professor_id'] ?? null;
$theses_id = $data['theses_id'] ?? null;

if (!$professor_id || !$theses_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Μη έγκυρα δεδομένα."]);
    exit;
}

// Έλεγχος αν έχουν εγκριθεί 2 καθηγητές
$count_sql = "SELECT COUNT(*) AS accepted_count 
              FROM committee_requests 
              WHERE theses_id = ? AND status = 'approved'";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param("i", $theses_id);
$stmt->execute();
$result = $stmt->get_result();
$accepted_count = $result->fetch_assoc()['accepted_count'];
$stmt->close();

// Αν δύο καθηγητές έχουν αποδεχθεί, ενημερώνουμε το backend
if ($accepted_count >= 2) {
    // Ξεκινάμε συναλλαγή για να διασφαλίσουμε την ατομικότητα των αλλαγών
    $conn->begin_transaction();

    try {
        // Ενημέρωση status στον πίνακα assignments
        $update_assignment_sql = "UPDATE assignments 
                                  SET status = 'finalized' 
                                  WHERE topic = ?";
        $stmt = $conn->prepare($update_assignment_sql);
        $stmt->bind_param("s", $theses_id);
        if (!$stmt->execute()) {
            throw new Exception("Σφάλμα κατά την ενημέρωση του πίνακα assignments.");
        }
        $stmt->close();

        // Εισαγωγή στον πίνακα theses
        $insert_theses_sql = "INSERT INTO theses (title, student_id, status) 
                              SELECT topic, student_number, 'under_assignment' 
                              FROM assignments  
                              WHERE topic = ?";
        $stmt = $conn->prepare($insert_theses_sql);
        $stmt->bind_param("s", $theses_id);
        if (!$stmt->execute()) {
            throw new Exception("Σφάλμα κατά την εισαγωγή στον πίνακα theses.");
        }
        $stmt->close();

        // Ενημέρωση του status στον πίνακα theses
        $update_theses_sql = "UPDATE theses 
                              SET status = 'under_assignment' 
                              WHERE title = ?";
        $stmt = $conn->prepare($update_theses_sql);
        $stmt->bind_param("s", $theses_id);
        if (!$stmt->execute()) {
            throw new Exception("Σφάλμα κατά την ενημέρωση του πίνακα theses.");
        }
        $stmt->close();

        // Ολοκλήρωση συναλλαγής
        $conn->commit();
        echo json_encode(["success" => true, "message" => "Η κατάσταση ανανεώθηκε επιτυχώς."]);
    } catch (Exception $e) {
        // Ακύρωση αλλαγών σε περίπτωση σφάλματος
        $conn->rollback();
        http_response_code(500);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Δεν υπάρχουν αρκετές αποδοχές από καθηγητές."]);
}

$conn->close();
