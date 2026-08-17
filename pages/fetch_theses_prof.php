<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json; charset=UTF-8");

$conn = new mysqli("localhost", "root", "", "web-project");

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Σφάλμα σύνδεσης: " . $conn->connect_error]));
}

// Έλεγχος συνεδρίας
$professor_id = $_SESSION['user_id'] ?? null;
if (!$professor_id) {
    die(json_encode(["success" => false, "message" => "Ο χρήστης δεν είναι συνδεδεμένος."]));
}

// Λήψη φίλτρων από το GET request
$status_filter = $_GET['status'] ?? '';
$role_filter = $_GET['role'] ?? '';

// Δημιουργία βασικού SQL query
$sql = "SELECT 
    t.id AS thesis_id,
    t.title AS thesis_title,
    t.description AS thesis_description,
    s.name AS student_name,
    s.surname AS student_surname,
    t.status AS thesis_status,
    GROUP_CONCAT(DISTINCT CONCAT(p.name, ' ', p.surname) SEPARATOR ', ') AS committee_members,
    t.created_at AS thesis_created_date
FROM 
    theses t
LEFT JOIN 
    thesis_committee tc ON t.theses_id = tc.theses_id
LEFT JOIN 
    professor p ON tc.professor_id = p.id
LEFT JOIN 
    students s ON t.student_id = s.id
WHERE 1=1"; // Ξεκινάμε με έγκυρο WHERE για να προσθέσουμε φίλτρα

// Διατήρηση φίλτρων
$params = [];
$types = '';

// Φιλτράρισμα βάσει ρόλου
if ($role_filter === 'supervisor') {
    $sql .= " AND t.professor_id = ?";
    $params[] = $professor_id;
    $types .= 'i';
} elseif ($role_filter === 'committee_member') {
    $sql .= " AND tc.professor_id = ?";
    $params[] = $professor_id;
    $types .= 'i';
}

// Φιλτράρισμα βάσει κατάστασης
if (!empty($status_filter)) {
    $sql .= " AND t.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

// Προσθήκη GROUP BY και ORDER BY
$sql .= " GROUP BY t.theses_id ORDER BY t.created_at DESC";

// Καταγραφή του SQL query για debugging
error_log("Generated SQL: $sql");

// Προετοιμασία του statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(["success" => false, "message" => "Σφάλμα προετοιμασίας του query: " . $conn->error]));
}

// Δέσμευση παραμέτρων αν υπάρχουν
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Εκτέλεση του query
if (!$stmt->execute()) {
    die(json_encode(["success" => false, "message" => "Σφάλμα εκτέλεσης του query: " . $stmt->error]));
}

// Ανάκτηση αποτελεσμάτων
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Επιστροφή δεδομένων σε JSON
if (empty($data)) {
    echo json_encode(["success" => false, "message" => "Δεν βρέθηκαν δεδομένα."]);
} else {
    echo json_encode(["success" => true, "data" => $data]);
}
if (isset($_GET['export']) && in_array($_GET['export'], ['csv', 'json'])) {
    $export_format = $_GET['export'];

    // Εκτέλεση του query για εξαγωγή δεδομένων
    if (!$stmt->execute()) {
        die(json_encode(["success" => false, "message" => "Σφάλμα εκτέλεσης του query: " . $stmt->error]));
    }

    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    if ($export_format === 'csv') {
        // Εξαγωγή σε CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=theses_export.csv');
        $output = fopen('php://output', 'w');
        
        // Γράψτε την κεφαλίδα (header) του CSV
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }

        // Γράψτε τα δεδομένα
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    } elseif ($export_format === 'json') {
        // Εξαγωγή σε JSON
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Κλείσιμο statement και σύνδεσης
$stmt->close();
$conn->close();
?>
