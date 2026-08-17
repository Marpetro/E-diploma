<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json"); // Ρυθμίζει την απάντηση σε JSON

// Λήψη των δεδομένων JSON που στάλθηκαν μέσω POST
$data = json_decode(file_get_contents('php://input'), true);

if (is_null($data)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON format.']);
    exit;
}

// Σύνδεση με τη βάση δεδομένων
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Επεξεργασία και εισαγωγή δεδομένων για τους διδάσκοντες
if (isset($data['professor']) && is_array($data['professor'])) {
    foreach ($data['professor'] as $professor) {
        // Ορίζουμε όλες τις στήλες του πίνακα professor
        $name = isset($professor['name']) ? $conn->real_escape_string($professor['name']) : '';
        $surname = isset($professor['surname']) ? $conn->real_escape_string($professor['surname']) : '';
        $email = isset($professor['email']) ? $conn->real_escape_string($professor['email']) : '';
        $topic = isset($professor['topic']) ? $conn->real_escape_string($professor['topic']) : '';
        $department = isset($professor['department']) ? $conn->real_escape_string($professor['department']) : '';
        $university = isset($professor['university']) ? $conn->real_escape_string($professor['university']) : '';
        $mobile = isset($professor['mobile']) ? $conn->real_escape_string($professor['mobile']) : '';
        $landline = isset($professor['landline']) ? $conn->real_escape_string($professor['landline']) : '';
        $password = isset($professor['password']) ? $conn->real_escape_string($professor['password']) : '';

        $sql = "INSERT INTO professor (name, surname, email, topic, department, university, mobile, landline, password) 
                VALUES ('$name', '$surname', '$email', '$topic', '$department', '$university', '$mobile', '$landline', '$password')";
        
        if (!$conn->query($sql)) {
            echo json_encode(['status' => 'error', 'message' => 'Error inserting professor data: ' . $conn->error]);
            exit;
        }
    }
}

$conn->close();
echo json_encode(['status' => 'success', 'message' => 'Professor data imported successfully!']);
?>
