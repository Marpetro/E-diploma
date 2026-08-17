<?php
session_start();
header("Content-Type: application/json");

// Σύνδεση με βάση δεδομένων
$conn = new mysqli("localhost", "root", "", "web-project");
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Σφάλμα σύνδεσης στη βάση δεδομένων."]));
}

// Λήψη δεδομένων
$data = json_decode(file_get_contents('php://input'), true);
$thesis_id = $data['thesis_id'] ?? null;
$reason = "Από Διδάσκοντα";
$general_assembly_number = $data['assembly_number'] ?? null;
$year = $data['year'] ?? null;

if (!$theses_id || !$general_assembly_number || !$year) {
    die(json_encode(["success" => false, "message" => "Μη έγκυρα δεδομένα εισόδου."]));
}

// Έλεγχος 2 ετών
$sql = "SELECT created_at FROM theses WHERE theses_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $theses_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die(json_encode(["success" => false, "message" => "Δεν βρέθηκε ανάθεση για τη διπλωματική."]));
}

$assignment = $result->fetch_assoc();
$assigned_at = new DateTime($assignment['assigned_at']);
$two_years_ago = (new DateTime())->modify('-2 years');

if ($assigned_at > $two_years_ago) {
    die(json_encode(["success" => false, "message" => "Δεν έχουν περάσει 2 έτη από την ανάθεση."]));
}

// Ακύρωση ανάθεσης

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Η ανάθεση ακυρώθηκε επιτυχώς."]);
} else {
    echo json_encode(["success" => false, "message" => "Αποτυχία ακύρωσης ανάθεσης."]);
}

$stmt->close();
$conn->close();
?>
