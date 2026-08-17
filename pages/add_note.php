<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json; charset=UTF-8");

// Σύνδεση με βάση δεδομένων
$conn = new mysqli("localhost", "root", "", "web-project");

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Σφάλμα σύνδεσης: " . $conn->connect_error]));
}

// Έλεγχος συνεδρίας
$professor_id = $_SESSION['user_id'] ?? null;
if (!$professor_id) {
    die(json_encode(["success" => false, "message" => "Ο χρήστης δεν είναι συνδεδεμένος."]));
}

// Λήψη δεδομένων από την αίτηση
$data = json_decode(file_get_contents('php://input'), true);
$note = $data['note'] ?? null;
$theses_id = $data['theses_id'] ?? null;

// Έλεγχος αν τα δεδομένα είναι έγκυρα
if (!$note || !$theses_id) {
    die(json_encode(["success" => false, "message" => "Μη έγκυρα δεδομένα εισόδου."]));
}

// Έλεγχος μήκους σημείωσης
if (strlen($note) > 300) {
    die(json_encode(["success" => false, "message" => "Η σημείωση πρέπει να είναι έως 300 χαρακτήρες."]));
}

// Καταχώρηση σημείωσης
$sql = "INSERT INTO notes (theses_id, professor_id, note, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(json_encode(["success" => false, "message" => "Σφάλμα προετοιμασίας query: " . $conn->error]));
}

// Σύνδεση παραμέτρων
$stmt->bind_param("iis", $theses_id, $professor_id, $note);

// Εκτέλεση query
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Η σημείωση καταχωρήθηκε επιτυχώς."]);
} else {
    echo json_encode(["success" => false, "message" => "Αποτυχία καταχώρησης σημείωσης: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
