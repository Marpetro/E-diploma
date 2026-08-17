<?php
$servername = "localhost"; //ή localhost
$username = "root"; // Ο χρήστης για τη βάση δεδομένων
$password = ""; // Ο κωδικός πρόσβασης (συνήθως κενός για το XAMPP)
$dbname = "web_project"; // Το όνομα της βάσης δεδομένων

// Δημιουργία σύνδεσης
$conn = new mysqli($servername, $username, $password, $dbname);

// Έλεγχος αν η σύνδεση είναι επιτυχής
if ($conn->connect_error) {
    die("Σφάλμα σύνδεσης με τη βάση δεδομένων: " . $conn->connect_error);
} else {
    echo "Η σύνδεση με τη βάση δεδομένων ήταν επιτυχής!";
}
?>
