<?php

session_start();
header("Content-Type: application/json");

// Σύνδεση με βάση δεδομένων
$conn = new mysqli("localhost", "root", "", "web-project");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Σφάλμα σύνδεσης με τη βάση δεδομένων."]);
    exit;
}

// Ανάκτηση `user_id` από συνεδρία
$professor_id = $_SESSION['user_id'] ?? null;
if (!$professor_id) {
    echo json_encode(["success" => false, "message" => "Ο χρήστης δεν είναι συνδεδεμένος."]);
    exit;
}

// Λήψη παραμέτρου κατάστασης από το αίτημα
$status = $_GET['status'] ?? null;

// Ερώτημα για διπλωματικές
$sql = "SELECT 
            t.id, 
            tt.title, 
            tt.description, 
            t.status, 
            s.name AS student_name, 
            s.surname AS student_surname
        FROM theses t
        INNER JOIN students s ON t.student_id = s.id
        INNER JOIN thesis_topics tt ON t.theses_id = tt.id
        WHERE t.professor_id = ?";

if ($status) {
    $sql .= " AND t.status = ?";
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Σφάλμα στη δημιουργία του ερωτήματος για διπλωματικές."]);
    exit;
}

if ($status) {
    $stmt->bind_param("is", $professor_id, $status);
} else {
    $stmt->bind_param("i", $professor_id);
}
$stmt->execute();
$result = $stmt->get_result();

$theses = [];
while ($row = $result->fetch_assoc()) {
    $row['step'] = match ($row['status']) {
        "under_assignment" => 1,
        "active" => 2,
        "under_review" => 3,
        "completed" => 4,
        default => 0,
    };
    $theses[] = $row;
}

echo json_encode(["success" => true, "theses" => $theses]);
$stmt->close();
$conn->close();
?>
