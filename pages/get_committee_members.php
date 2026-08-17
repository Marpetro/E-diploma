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

// Ανάκτηση του professor_id
$professor_id = $_SESSION['professor_id'] ?? null;

if (!$professor_id) {
    $sql = "SELECT professor_id FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $professor_id = $row['professor_id'];
        $_SESSION['professor_id'] = $professor_id; // Αποθηκεύστε το στη συνεδρία
    } else {
        die(json_encode(["success" => false, "message" => "Δεν βρέθηκε καθηγητής για τον συνδεδεμένο χρήστη."]));
    }
    $stmt->close();
}

// Παράμετροι του αιτήματος
$thesis_id = $_GET['id'] ?? null;

if (!$thesis_id) {
    die(json_encode(["success" => false, "message" => "Το ID της διπλωματικής είναι υποχρεωτικό."]));
}

// SQL για ανάκτηση μελών της επιτροπής
$sql = "SELECT 
            t.id AS thesis_id,
            t.title AS thesis_title,
            p.name AS professor_name,
            p.surname AS professor_surname,
            cr.invitation_date,
            cr.accept_date,
            cr.reject_date
        FROM theses t
        JOIN committee_requests cr ON t.theses_id = cr.theses_id
        JOIN professor p ON cr.professor_id = p.id
        WHERE t.id = ? AND cr.professor_id != ?";

        
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $thesis_id, $professor_id); // Αποφυγή εμφάνισης του συνδεδεμένου καθηγητή
$stmt->execute();
$result = $stmt->get_result();

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}

if (count($members) > 0) {
    echo json_encode(["success" => true, "members" => $members]);
} else {
    echo json_encode(["success" => false, "message" => "Δεν βρέθηκαν μέλη επιτροπής."]);
}

$stmt->close();
$conn->close();
?>
