<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *"); // Επιτρέπει αιτήματα από οποιοδήποτε origin
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE"); // Επιτρέπει τις HTTP μεθόδους
header("Access-Control-Allow-Headers: Content-Type"); // Επιτρέπει συγκεκριμένα headers

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-project";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Σφάλμα σύνδεσης: " . $conn->connect_error);
}

$id = $_GET['id'];
$sql = "SELECT title, description, pdf_file FROM thesis_topics WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(["success" => true, "title" => $row['title'], "description" => $row['description'], "pdf_file" => $row['pdf_file']]);
} else {
    echo json_encode(["success" => false, "message" => "Δεν βρέθηκε το θέμα"]);
}

$conn->close();
?>
