<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-project";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Σφάλμα σύνδεσης με τη βάση δεδομένων."]));
}

// Fetch `student_id` from session
$student_id = $_SESSION['user_id'] ?? null;
if (!$student_id) {
    die(json_encode(["success" => false, "message" => "Ο φοιτητής δεν είναι συνδεδεμένος."]));
}

// Log session data for debugging
error_log("Session Data: " . print_r($_SESSION, true));

// Fetch incoming data
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    die(json_encode(["success" => false, "message" => "Μη έγκυρα δεδομένα JSON."]));
}

$professors = $data['professors'] ?? [];
if (empty($professors)) {
    die(json_encode(["success" => false, "message" => "Πρέπει να προσθέσετε τουλάχιστον έναν διδάσκοντα."]));
}

// Fetch `student_number` from `students` table using `users.student_id`
$sql = "SELECT s.student_number 
        FROM students s 
        JOIN users u ON u.student_id = s.id 
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id); // Correct variable usage
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die(json_encode(["success" => false, "message" => "Ο φοιτητής δεν βρέθηκε στη βάση δεδομένων."]));
}

$student_data = $result->fetch_assoc();
$student_number = $student_data['student_number'];
$stmt->close();

// Log student number for debugging
error_log("Student Number: $student_number");

// Fetch `theses_id` outside the loop
$theses_sql = "SELECT topic AS theses_id FROM assignments WHERE student_number = ?";
$theses_stmt = $conn->prepare($theses_sql);
$theses_stmt->bind_param("i", $student_number);
$theses_stmt->execute();
$theses_result = $theses_stmt->get_result();

if ($theses_result->num_rows === 0) {
    die(json_encode(["success" => false, "message" => "Δεν βρέθηκε ανάθεση για τον φοιτητή."]));
}

$assignment = $theses_result->fetch_assoc();
$theses_id = $assignment['theses_id'];
$theses_stmt->close();

// Loop through professors and insert invitations
foreach ($professors as $professor) {
    // Fetch professor ID based on name and surname
    $professor_sql = "SELECT id FROM professor WHERE name = ? AND surname = ?";
    $professor_stmt = $conn->prepare($professor_sql);
    $professor_stmt->bind_param("ss", $professor['name'], $professor['surname']);
    $professor_stmt->execute();
    $professor_result = $professor_stmt->get_result();

    if ($professor_result->num_rows === 0) {
        $professor_stmt->close();
        continue; // Skip this professor if not found
    }

    $professor_id = $professor_result->fetch_assoc()['id'];
    $professor_stmt->close();

    // Insert the invitation into `committee_requests`
    $insert_sql = "INSERT INTO committee_requests (student_id, professor_id, theses_id, status, invitation_date) 
                   VALUES (?, ?, ?, 'pending', NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iii", $student_id, $professor_id, $theses_id);

    if (!$insert_stmt->execute()) {
        error_log("Error inserting invitation: " . $insert_stmt->error);
        $insert_stmt->close();
        continue; // Skip this professor if insertion fails
    }
    $insert_stmt->close();
}
// Insert into `theses` if not exists
$insert_theses_sql = "INSERT INTO theses (title, student_id, professor_id, theses_id, status, description)
    SELECT 
        tt.title, 
        cr.student_id, 
        a.professor_id, 
        cr.theses_id, 
        'under_assignment', 
        tt.description
    FROM committee_requests cr
    JOIN thesis_topics tt ON cr.theses_id = tt.id
    JOIN assignments a ON a.topic = tt.id
    WHERE cr.theses_id = ? 
    AND NOT EXISTS (
        SELECT 1 
        FROM theses 
        WHERE theses_id = ?
    )
    LIMIT 1";

$insert_theses_stmt = $conn->prepare($insert_theses_sql);
$insert_theses_stmt->bind_param("ii", $theses_id, $theses_id);

if (!$insert_theses_stmt->execute()) {
    error_log("Error inserting into theses: " . $insert_theses_stmt->error);
    die(json_encode(["success" => false, "message" => "Σφάλμα κατά την εισαγωγή της διπλωματικής."]));
}

$insert_theses_stmt->close();

// Update status in `assignments` to `finalized`
$update_status_sql = "UPDATE assignments SET status = 'finalized' WHERE topic = ?";
$update_status_stmt = $conn->prepare($update_status_sql);
$update_status_stmt->bind_param("i", $theses_id);

if (!$update_status_stmt->execute()) {
    error_log("Error updating assignments status: " . $update_status_stmt->error);
    die(json_encode(["success" => false, "message" => "Σφάλμα κατά την ενημέρωση του status στην ανάθεση."]));
}

$update_status_stmt->close();

// Log success
echo json_encode(["success" => true, "message" => "Οι προσκλήσεις στάλθηκαν με επιτυχία."]);
$conn->close();
?>