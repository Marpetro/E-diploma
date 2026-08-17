<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *"); // Επιτρέπει αιτήματα από οποιοδήποτε origin
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE"); // Επιτρέπει τις HTTP μεθόδους
header("Access-Control-Allow-Headers: Content-Type"); // Επιτρέπει συγκεκριμένα headers


// Ρυθμίσεις σύνδεσης με βάση δεδομένων
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-project";

// Σύνδεση στη βάση δεδομένων
$conn = new mysqli($servername, $username, $password, $dbname);

// Έλεγχος σύνδεσης
if ($conn->connect_error) {
    die("Σφάλμα σύνδεσης: " . $conn->connect_error);
}

// Παίρνουμε το user_id από τη συνεδρία
$user_id = $_SESSION['user_id'];

// Ερώτημα για να βρούμε το professor_id στον πίνακα users
$sql = "SELECT professor_id FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Λήψη του professor_id
    $user = $result->fetch_assoc();
    $professor_id = $user['professor_id'];

    // Τώρα αναζητούμε τις αιτήσεις από το committee_requests
    $sql = "SELECT 
    cr.id,
    s.surname AS student_surname,
    s.student_number AS student_am,
    tt.title AS thesis_title
FROM 
    committee_requests cr
JOIN 
    students s ON cr.student_id = s.id
JOIN 
    assignments a ON cr.theses_id = a.topic -- Assuming `committee_requests.theses_id` references `assignments.id`
JOIN 
    thesis_topics tt ON a.topic = tt.id -- `assignments.topic` references `thesis_topics.id`
WHERE 
    cr.professor_id = ? AND cr.status = 'pending'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $professor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $committeeRequests = [];
        while ($row = $result->fetch_assoc()) {
            $committeeRequests[] = $row;
        }

        // Επιστρέφουμε τα αποτελέσματα σε μορφή JSON
        echo json_encode(['success' => true, 'data' => $committeeRequests]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Δεν βρέθηκαν αιτήσεις για αυτόν τον καθηγητή.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Ο καθηγητής δεν βρέθηκε.']);
}

$stmt->close();
$conn->close();

?>