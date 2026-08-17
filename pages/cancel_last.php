<?php
session_start();
header("Content-Type: application/json");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Σύνδεση με βάση δεδομένων
$conn = new mysqli("localhost", "root", "", "web-project");
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Σφάλμα σύνδεσης στη βάση δεδομένων."]));
}

// Λήψη δεδομένων από το αίτημα
$data = json_decode(file_get_contents("php://input"), true);
$theses_id = $data['theses_id'] ?? null;

if (!$theses_id) {
    die(json_encode(["success" => false, "message" => "Λείπει το ID της διπλωματικής."]));
}

// Ξεκινάμε συναλλαγή για να διασφαλίσουμε την ατομικότητα των αλλαγών
$conn->begin_transaction();

try {
    // Διαγραφή προσκλήσεων από τον πίνακα `committee_requests`
    $delete_committees_sql = "DELETE FROM committee_requests WHERE theses_id = ?";
    $stmt = $conn->prepare($delete_committees_sql);
    $stmt->bind_param("i", $theses_id);
    $stmt->execute();
    $stmt->close();

    // Ενημέρωση του πίνακα `assignments` για να ακυρωθεί η ανάθεση
    $update_assignment_status_sql = "UPDATE assignments SET status = 'cancelled' WHERE topic = ?";
    $stmt = $conn->prepare($update_assignment_status_sql);
    $stmt->bind_param("i", $theses_id);
    $stmt->execute();
    $stmt->close();

    // Ενημέρωση του πίνακα `theses` για να ακυρωθεί η ανάθεση
    $update_theses_sql = "UPDATE theses SET status = 'cancelled' WHERE id = ?";
    $stmt = $conn->prepare($update_theses_sql);
    $stmt->bind_param("i", $theses_id);
    $stmt->execute();
    $stmt->close();

    // Ολοκλήρωση συναλλαγής
    $conn->commit();

    echo json_encode(["success" => true, "message" => "Η ανάθεση ακυρώθηκε με επιτυχία."]);
} catch (Exception $e) {
    // Ακύρωση αλλαγών σε περίπτωση σφάλματος
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();
?>
