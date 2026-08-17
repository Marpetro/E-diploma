<?php
// Έλεγχος αν ο καθηγητής είναι συνδεδεμένος
session_start();
// Ενεργοποίηση σφαλμάτων για debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ρυθμίσεις CORS και JSON
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8"); // Σωστό charset για JSON

// Ρυθμίσεις σύνδεσης με βάση δεδομένων
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-project";

// Δημιουργία σύνδεσης
$conn = new mysqli($servername, $username, $password, $dbname);

// Έλεγχος σύνδεσης
if ($conn->connect_error) {
    http_response_code(500); // Επιστροφή HTTP 500 σε περίπτωση σφάλματος
    echo json_encode(["success" => false, "message" => "Σφάλμα σύνδεσης: " . $conn->connect_error]);
    exit; // Τερματισμός script
}


$professor_id = $_SESSION['user_id'] ?? null;  // Αν η μεταβλητή της συνεδρίας είναι κενή, δεν είναι συνδεδεμένος

if (!$professor_id) {
    echo json_encode(["success" => false, "message" => "Δεν είστε συνδεδεμένος ως καθηγητής."]);
    exit;
}


// Ερώτημα για ανάκτηση θεμάτων που αφορούν τον συγκεκριμένο καθηγητή
$sql = "SELECT id, title FROM thesis_topics WHERE professor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $professor_id);  // Δέσμευση του καθηγητή ID
$stmt->execute();
$result = $stmt->get_result();

// Έλεγχος αν βρέθηκαν αποτελέσματα
if ($result->num_rows > 0) {
    $theses = [];
    while ($row = $result->fetch_assoc()) {
        $theses[] = [
            "id" => $row["id"],
            "title" => $row["title"],
        ];
    }
    echo json_encode(["success" => true, "theses" => $theses]);
} else {
    http_response_code(404); // Επιστροφή HTTP 404 αν δεν υπάρχουν θέματα
    echo json_encode(["success" => false, "message" => "Δεν βρέθηκαν θέματα για τον καθηγητή."]);
}


// Κλείσιμο σύνδεσης
$conn->close();
?>
