<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Έλεγχος για την ορθή ανάγνωση δεδομένων
$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->studentId)) {
    echo json_encode(["success" => false, "message" => "Σφάλμα στα δεδομένα εισόδου"]);
    exit;
}

$studentId = $data->studentId;


// Σύνδεση στη βάση δεδομένων
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-project";

$conn = new mysqli($servername, $username, $password, $dbname);

// Έλεγχος σύνδεσης
if ($conn->connect_error) {
    $response = ["success" => false, "message" => "Connection failed: " . $conn->connect_error];
echo json_encode($response);
exit();

}

// Εκτέλεση του ερωτήματος για ακύρωση της ανάθεσης
$sql = "DELETE FROM assignments WHERE student_number = ? AND status = 'temporary'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $studentId);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Η ανάθεση διαγράφηκε επιτυχώς"]);
} else {
    echo json_encode(["success" => false, "message" => "Αποτυχία εκτέλεσης ερωτήματος"]);
}


$stmt->close();
$conn->close();
?>
