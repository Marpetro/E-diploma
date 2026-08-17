<?php
session_start();
header("Content-Type: application/json");

// Σύνδεση με βάση δεδομένων
$conn = new mysqli("localhost", "root", "", "web-project");
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Σφάλμα σύνδεσης στη βάση δεδομένων."]));
}

// Λήψη δεδομένων από το αίτημα
$data = json_decode(file_get_contents('php://input'), true);
$theses_id = $data['theses_id'] ?? null;

if (!$theses_id) {
    die(json_encode(["success" => false, "message" => "Το ID της διπλωματικής είναι υποχρεωτικό."]));
}

// Logging για debugging
error_log("Received theses_id: " . $theses_id);


// Ενημέρωση κατάστασης
$sql = "UPDATE theses SET status = 'under_review' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $theses_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "Η κατάσταση άλλαξε σε Υπό Εξέταση."]);
} else {
    echo json_encode(["success" => false, "message" => "Δεν έγινε καμία αλλαγή."]);
}

$stmt->close();
$conn->close();
?>
