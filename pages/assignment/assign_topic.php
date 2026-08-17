<?php
session_start();  // Ξεκινάμε τη συνεδρία

// Ενεργοποίηση σφαλμάτων
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ρυθμίσεις CORS και JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$response = ["success" => false, "message" => "Unknown error"];

// Διαβάζουμε τα δεδομένα από το αίτημα
$input = json_decode(file_get_contents("php://input"), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    $response["message"] = "Invalid JSON input";
    echo json_encode($response);
    exit();
}

$studentId = $input['studentId'] ?? '';
$topic = $input['topic'] ?? '';

// Έλεγχος αν τα πεδία είναι κενά
if (empty($studentId) || empty($topic)) {
    $response["message"] = "Invalid student ID or topic";
    echo json_encode($response);
    exit();
}

// Σύνδεση με βάση δεδομένων
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-project";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    $response["message"] = "Connection failed: " . $conn->connect_error;
    echo json_encode($response);
    exit();
}

// Έλεγχος αν ο φοιτητής υπάρχει
$sql = "SELECT * FROM students WHERE student_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $response["message"] = "No student found";
    echo json_encode($response);
    $stmt->close();
    $conn->close();
    exit();
}

// Έλεγχος για προηγούμενη ανάθεση
$sql_check = "SELECT * FROM assignments WHERE student_number = ? AND status IN ('temporary', 'approved')";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $studentId);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    $response["message"] = "Ο φοιτητής έχει ήδη ανάθεση.";
    echo json_encode($response);
    $stmt_check->close();
    $conn->close();
    exit();
}

$stmt_check->close();


// Ανάθεση θέματος με προσωρινή κατάσταση και καταχώρηση του καθηγητή
$professor_id = $_SESSION['user_id'] ?? '';
if (empty($professor_id)) {
    $response["message"] = "Δεν βρέθηκε καθηγητής στη συνεδρία!";
    echo json_encode($response);
    exit();
}
// Λήψη του ID του καθηγητή από τη συνεδρία

$sql = "INSERT INTO assignments (student_number, topic,  professor_id, status) VALUES (?, ?, ?, 'temporary')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $studentId, $topic, $professor_id);

if ($stmt->execute()) {
    $response = ["success" => true, "message" => "Το θέμα ανατέθηκε προσωρινά."];
} else {
    $response["message"] = "Error assigning topic: " . $stmt->error;
}

echo json_encode($response);
$stmt->close();
$conn->close();
?>
