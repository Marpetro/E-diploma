<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json"); // Ρυθμίζει την απάντηση σε JSON

// Σύνδεση με τη βάση δεδομένων
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Η σύνδεση απέτυχε: " . $conn->connect_error);
}

// Ανάκτηση των διπλωματικών εργασιών με καταστάσεις "active" και "under_assignment"
$sql = "SELECT id, title, description, status, created_at FROM theses WHERE status IN ('active', 'under_assignment')";
$result = $conn->query($sql);

$theses = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Ανάκτηση των μελών της τριμελούς επιτροπής
        $committee_sql = "
            SELECT p.name AS professor_name, tc.role 
            FROM thesis_committee tc
            JOIN professor p ON p.id = tc.professor_id
            WHERE tc.theses_id = " . $row['id'];
        $committee_result = $conn->query($committee_sql);

        $committee = [];
        if ($committee_result->num_rows > 0) {
            while ($committee_row = $committee_result->fetch_assoc()) {
                $committee[] = $committee_row['professor_name'] . " (" . $committee_row['role'] . ")";
            }
        }

        // Υπολογισμός του χρόνου από την ανάθεση (δημιουργία)
        $timeElapsed = date_diff(date_create($row['created_at']), date_create())->format('%a ημέρες');

        // Προσθήκη των δεδομένων στο πίνακα
        $theses[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'status' => $row['status'],
            'created_at' => $row['created_at'],
            'committee' => implode(", ", $committee),  // Σύνδεση των μελών της επιτροπής
            'timeElapsed' => $timeElapsed
        ];
    }
} else {
    echo json_encode(["error" => "Δεν βρέθηκαν διπλωματικές εργασίες"]);
    exit;
}

$conn->close();

// Επιστροφή των δεδομένων σε μορφή JSON
echo json_encode($theses, JSON_PRETTY_PRINT);
?>
