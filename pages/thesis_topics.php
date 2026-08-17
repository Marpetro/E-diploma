<?php
session_start(); // Ενεργοποίηση session

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "web-project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Σφάλμα σύνδεσης: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $professor_id = $_SESSION['user_id']; // Το ID του καθηγητή από το session

    // Ορισμός του φακέλου αποθήκευσης
    $uploadDir = __DIR__ . '/uploads/';
    $pdfFile = $uploadDir . basename($_FILES['pdf']['name']);

    // Ελέγξτε αν ο φάκελος υπάρχει, αν όχι δημιουργήστε τον
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if ($_FILES['pdf']['error'] == 0) {
        // Μεταφορά του αρχείου στον φάκελο uploads
        if (move_uploaded_file($_FILES['pdf']['tmp_name'], $pdfFile)) {
            echo "Το αρχείο ανέβηκε με επιτυχία!";
        } else {
            echo "Σφάλμα κατά την ανέβασμα του αρχείου.";
        }
    } else {
        echo "Σφάλμα κατά την ανέβασμα του αρχείου: " . $_FILES['pdf']['error'];
    }

    // Εισαγωγή δεδομένων στη βάση δεδομένων
    $sql = "INSERT INTO thesis_topics (title, description, pdf_file, professor_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $title, $description, $pdfFile, $professor_id);

    if ($stmt->execute()) {
        // Επιστροφή επιτυχίας στο frontend
        echo "Η καταχώρηση του θέματος ολοκληρώθηκε επιτυχώς.";
    } else {
        echo "Σφάλμα κατά την αποθήκευση του θέματος: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
