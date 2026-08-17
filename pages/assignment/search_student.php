<?php
header("Content-Type: application/json");

header("Access-Control-Allow-Origin: *"); // Επιτρέπει αιτήματα από οποιοδήποτε origin
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE"); // Επιτρέπει τις HTTP μεθόδους
header("Access-Control-Allow-Headers: Content-Type"); // Επιτρέπει συγκεκριμένα headers

// Σύνδεση στη βάση δεδομένων
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-project";  // Βάση δεδομένων που χρησιμοποιείς

$conn = new mysqli($servername, $username, $password, $dbname);

// Έλεγχος σύνδεσης
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

$studentId = $_GET['studentId'] ?? '';

if ($studentId) {
    $sql = "SELECT * FROM students WHERE student_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        
        // Καταγραφή μηνύματος ότι ο φοιτητής βρέθηκε στο αρχείο καταγραφής σφαλμάτων
        error_log("Ο φοιτητής με ID $studentId βρέθηκε επιτυχώς.");
        
        echo json_encode(["success" => true, "student" => $student]);
    } else {
        echo json_encode(["success" => false, "message" => "No student found"]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid student ID"]);
}

$conn->close();
?>
