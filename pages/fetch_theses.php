<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);;


// Ρυθμίσεις CORS και JSON
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Ρυθμίσεις σύνδεσης με βάση δεδομένων
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-project";

$conn = new mysqli($servername, $username, $password, $dbname);

// Έλεγχος σύνδεσης
if ($conn->connect_error) {
    http_response_code(500); // Επιστροφή HTTP 500 σε περίπτωση σφάλματος
    echo json_encode(["success" => false, "message" => "Σφάλμα σύνδεσης: " . $conn->connect_error]);
    exit; // Τερματισμός script
}

$professor_id = $_SESSION['user_id']; // Βεβαιώσου ότι παίρνεις το σωστό ID

// Λήψη των φίλτρων από την αίτηση (status και role)
$status = $_GET['status'] ?? 'all'; // Εάν δεν υπάρχει, default: 'all'
$role = $_GET['role'] ?? 'all'; // Εάν δεν υπάρχει, default: 'all'

// Δημιουργία της βασικής query για τη λήψη των διπλωματικών
$query = "SELECT t.id, t.title AS thesis_title, s.name AS student_name, s.surname AS student_surname, t.status AS thesis_status, p.name AS professor_name, p.surname AS professor_surname, tt.title AS topic_title 
          FROM theses t
          JOIN students s ON t.student_id = s.id
          JOIN professor p ON t.professor_id = p.id
          LEFT JOIN thesis_topics tt ON t.theses_topics_id = tt.id
          WHERE 1";

// Προετοιμασία του πίνακα παραμέτρων για τα `bind_param()`
$params = [];
$types = "";  // Χρειάζεται για bind_param() για να καθορίσουμε τον τύπο των παραμέτρων

// Προσθήκη φίλτρων στο query
if ($status !== 'all') {
    $query .= " AND t.status = ?";
    $params[] = $status; // Προσθέτουμε το status στην παράμετρο
    $types .= "s";  // Ο τύπος για το status είναι string (s)
}

if ($role !== 'all') {
    if ($role === 'supervisor') {
        $query .= " AND t.professor_id = ?";
        $params[] = $professor_id; // Προσθέτουμε το professor_id στην παράμετρο
        $types .= "i";  // Ο τύπος για το professor_id είναι integer (i)
    } elseif ($role === 'committee_member') {
        // Προσθήκη λογικής για μέλος τριμελούς επιτροπής
        $query .= " AND EXISTS (
                        SELECT 1 FROM thesis_committee tc 
                        WHERE tc.thesis_id = t.id 
                        AND tc.professor_id = ?
                        AND tc.role = 'committee_member'
                    )";
        $params[] = $professor_id; // Προσθέτουμε το professor_id στην παράμετρο
        $types .= "i";  // Ο τύπος για το professor_id είναι integer (i)
    }
}

// Εκτέλεση του query
$stmt = $conn->prepare($query);

// Εάν υπάρχουν φίλτρα, bind τα στην query
if (!empty($params)) {
    // Δημιουργία του binding για κάθε παράμετρο
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$theses = $result->fetch_all(MYSQLI_ASSOC);

// Επιστροφή των δεδομένων σε μορφή JSON
echo json_encode($theses);
?>
