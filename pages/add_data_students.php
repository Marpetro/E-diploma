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
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "web-project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Επεξεργασία και εισαγωγή δεδομένων για τους φοιτητές
if (isset($data['students']) && is_array($data['students'])) {
    foreach ($data['students'] as $student) {
        // Ορίζουμε όλες τις στήλες του πίνακα students
        $name = isset($student['name']) ? $conn->real_escape_string($student['name']) : '';
        $surname = isset($student['surname']) ? $conn->real_escape_string($student['surname']) : '';
        $student_number = isset($student['student_number']) ? $conn->real_escape_string($student['student_number']) : '';
        $street = isset($student['street']) ? $conn->real_escape_string($student['street']) : '';
        $number = isset($student['number']) ? $conn->real_escape_string($student['number']) : '';
        $city = isset($student['city']) ? $conn->real_escape_string($student['city']) : '';
        $postcode = isset($student['postcode']) ? $conn->real_escape_string($student['postcode']) : '';
        $father_name = isset($student['father_name']) ? $conn->real_escape_string($student['father_name']) : '';
        $landline_telephone = isset($student['landline_telephone']) ? $conn->real_escape_string($student['landline_telephone']) : '';
        $mobile_telephone = isset($student['mobile_telephone']) ? $conn->real_escape_string($student['mobile_telephone']) : '';
        $email = isset($student['email']) ? $conn->real_escape_string($student['email']) : '';
        $topic_title = isset($student['topic_title']) ? $conn->real_escape_string($student['topic_title']) : '';
        $password = isset($student['password']) ? $conn->real_escape_string($student['password']) : '';

        $sql = "INSERT INTO students (name, surname, student_number, street, number, city, postcode, father_name, landline_telephone, mobile_telephone, email, topic_title, password) 
                VALUES ('$name', '$surname', '$student_number', '$street', '$number', '$city', '$postcode', '$father_name', '$landline_telephone', '$mobile_telephone', '$email', '$topic_title', '$password')";
        
        if (!$conn->query($sql)) {
            echo json_encode(['status' => 'error', 'message' => 'Error inserting student data: ' . $conn->error]);
            exit;
        }
    }
}

$conn->close();
echo json_encode(['status' => 'success', 'message' => 'Data imported successfully!']);
?>
