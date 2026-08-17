<?php
session_start();

// Ρυθμίσεις σύνδεσης με βάση δεδομένων
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-project";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Σφάλμα σύνδεσης με τη βάση δεδομένων."]));
}

$student_id = $_SESSION['user_id'] ?? null;
if (!$student_id) {
    die(json_encode(["error" => "Ο φοιτητής δεν είναι συνδεδεμένος."]));
}

// Ερώτημα για ανάκτηση των δεδομένων του φοιτητή
$sql = "SELECT name, surname, student_number, father_name, city, street, number, postcode, mobile_telephone, landline_telephone, email FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student_data = $result->fetch_assoc();
    echo json_encode($student_data);  // Επιστρέφουμε τα δεδομένα ως JSON
} else {
    echo json_encode(["error" => "Δεν βρέθηκαν δεδομένα για τον φοιτητή."]);
}

$stmt->close();
$conn->close();
?>
