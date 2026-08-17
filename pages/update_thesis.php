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

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$title = $data['title'];
$description = $data['description'];
$pdf_file = $data['pdf_file'];

$sql = "UPDATE thesis_topics SET title = ?, description = ?, pdf_file = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $title, $description, $pdf_file, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Το θέμα ενημερώθηκε επιτυχώς."]);
} else {
    echo json_encode(["success" => false, "message" => "Προέκυψε σφάλμα κατά την ενημέρωση."]);
}

$conn->close();
?>
