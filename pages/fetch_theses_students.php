<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-project";

// Σύνδεση με βάση δεδομένων
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Σφάλμα σύνδεσης: " . $conn->connect_error]);
    exit;
}

// Έλεγχος συνεδρίας και `user_id`
if (!isset($_SESSION['user_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Δεν βρέθηκε το user_id στη συνεδρία"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Ανάκτηση `student_id` από τον πίνακα `users` και `students`
$student_id_query = "SELECT s.id AS student_id 
                     FROM users u
                     JOIN students s ON u.student_id = s.id
                     WHERE u.id = ?";
$stmt = $conn->prepare($student_id_query);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Σφάλμα στην προετοιμασία του query: " . $conn->error]);
    exit;
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Δεν βρέθηκε φοιτητής για τον συγκεκριμένο χρήστη"]);
    exit;
}

$student_data = $result->fetch_assoc();
$student_id = $student_data['student_id'];
$stmt->close();

if (isset($_GET['status'])) {
    $status = $_GET['status']; // Παίρνουμε το status από το GET request

    if ($status === 'temporary') {
        // Ερώτημα για προσωρινή ανάθεση (assignments)
        $sql = "SELECT 
                tt.title AS title,
                tt.description AS summary,
                tt.pdf_file,
                a.status,
                NULL AS committee,
                a.assigned_at AS duration
            FROM assignments a
            LEFT JOIN thesis_topics tt ON a.topic = tt.id
            LEFT JOIN students s ON a.student_number = s.student_number
            WHERE s.id = ? AND a.status != 'finalized' -- Εξαιρεί το 'finalized'
            GROUP BY a.id";
    } else { 
        // Ερώτημα για οποιαδήποτε άλλη κατάσταση
        $sql = "SELECT 
                COALESCE(t.title, tt.title) AS title,
                t.description AS summary,
                tt.pdf_file AS pdf_file,
                t.status,
                GROUP_CONCAT(CONCAT(p.name, ' ', p.surname) SEPARATOR ', ') AS committee,
                t.created_at AS duration
            FROM theses t
            LEFT JOIN thesis_topics tt ON t.theses_id = tt.id
            LEFT JOIN thesis_committee tc ON t.theses_id = tc.theses_id
            LEFT JOIN professor p ON tc.professor_id = p.id
            LEFT JOIN students s ON t.student_id = s.id
            WHERE s.id = ? AND t.status = ?
            GROUP BY t.id";
    }
    
} else {
    // Αν δεν δοθεί status, επιστρέφουμε μήνυμα σφάλματος
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Δεν δόθηκε κατάσταση status"]);
    exit;
}

// Προετοιμασία του ερωτήματος
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Σφάλμα στην προετοιμασία του query: " . $conn->error]);
    exit;
}

// Δέσμευση παραμέτρων
if ($status === 'temporary') {
    $stmt->bind_param('i', $student_id);
} else {
    $stmt->bind_param('is', $student_id, $status);
}

// Εκτέλεση του ερωτήματος
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Σφάλμα στην εκτέλεση του query: " . $stmt->error]);
    exit;
}

// Ανάκτηση αποτελεσμάτων
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(["success" => true, "data" => []]);
    exit;
}

// Δημιουργία JSON απάντησης
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Επιστροφή αποτελεσμάτων
echo json_encode(["success" => true, "data" => $data]);

// Κλείσιμο πόρων
$stmt->close();
$conn->close();
exit;


?>
