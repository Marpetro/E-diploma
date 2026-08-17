<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json"); // Ρυθμίζει την απάντηση σε JSON

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Σφάλμα σύνδεσης: " . $conn->connect_error]));
}
if (isset($_SESSION['user_id'])) {
    $professor_id = $_SESSION['user_id'];
} else {
    error_log('User ID δεν βρέθηκε στη συνεδρία');
    // Ή να κάνεις κάτι άλλο αν το ID δεν υπάρχει
}

// Ανάκτηση του professor_id από τη συνεδρία (ή τον πίνακα χρηστών, αν είναι συνδεδεμένος)

$professor_id = $_SESSION['user_id']; // Βεβαιώσου ότι παίρνεις το σωστό ID

$sql = "SELECT id, title, description, pdf_file
FROM thesis_topics
WHERE professor_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $theses = [];
    while ($row = $result->fetch_assoc()) {
        $theses[] = [
            "id" => $row["id"],
            "title" => $row["title"],
            "description" => $row["description"],
            "pdf_file" => $row["pdf_file"]
        ];
    }
    echo json_encode(["success" => true, "theses" => $theses]);
} else {
    // Εδώ είναι το νέο μήνυμα χωρίς σφάλμα
    echo json_encode(["success" => false, "message" => "Αυτή τη στιγμή δεν υπάρχουν θέματα για αυτόν τον καθηγητή."]);
}



$stmt->close();
$conn->close();
?>
