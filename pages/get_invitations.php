<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Ρυθμίσεις σύνδεσης με βάση δεδομένων
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "web-project";

// Δημιουργία σύνδεσης
$conn = new mysqli($servername, $username, $password, $dbname);

// Έλεγχος σύνδεσης
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Σφάλμα σύνδεσης: " . $conn->connect_error]);
    exit;
}

$user_id = $_SESSION['user_id'];  // Το ID του διδάσκοντα που είναι συνδεδεμένος

// Ανάκτηση των προσκλήσεων για το συγκεκριμένο διδάσκοντα
$query = "SELECT ci.invitation_date, t.title AS thesis_title, s.name AS student_name
          FROM committee_invitations AS ci
          JOIN theses AS t ON ci.thesis_id = t.id
          JOIN students AS s ON t.student_id = s.id
          WHERE ci.professor_id = ? AND ci.status = 'pending'";  // Οι προσκλήσεις που είναι σε εκκρεμότητα
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Δημιουργία πίνακα για αποθήκευση των προσκλήσεων
$invitations = [];
while ($row = $result->fetch_assoc()) {
    $invitations[] = $row;
}

// Επιστροφή των δεδομένων σε μορφή JSON
echo json_encode($invitations);
?>
