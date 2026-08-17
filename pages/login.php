
<?php

session_start();
// Καταγραφή στη συνεδρία
if (isset($_SESSION['user_id'])) {
    error_log("Η συνεδρία είναι ενεργή για τον χρήστη: " . $_SESSION['user_id']);
} else {
    error_log("Η συνεδρία δεν είναι ενεργή.");
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ρυθμίσεις σύνδεσης με βάση δεδομένων
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web-project";

// Σύνδεση στη βάση δεδομένων
$conn = new mysqli($servername, $username, $password, $dbname);

// Έλεγχος σύνδεσης
if ($conn->connect_error) {
    error_log("Σφάλμα σύνδεσης: " . $conn->connect_error);  // Καταγραφή σφάλματος στο error log
    die("Σφάλμα σύνδεσης: " . $conn->connect_error);
} else {
    error_log("Σύνδεση στη βάση δεδομένων επιτυχής!");  // Καταγραφή επιτυχούς σύνδεσης
}


// Λήψη των στοιχείων από τη φόρμα
$email = $_POST['email'];
$password = $_POST['password']; // Κωδικός που εισάγεται από τον χρήστη

// Ερώτημα για αναζήτηση του χρήστη με βάση το email
$sql = "SELECT id, email, password, role FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Απλή σύγκριση του κωδικού
    if ($password === $user['password']) {
        // Αποθήκευση δεδομένων στη συνεδρία
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        // Ανακατεύθυνση στη σωστή σελίδα ανάλογα με το ρόλο
        if ($user['role'] == 'professor') {
            header("Location: http://localhost/web-project/pages/tabs-prof.html"); // Ανακατεύθυνση στη σελίδα του Διδάσκοντα
        } elseif ($user['role'] == 'student') {
            header("Location: http://localhost/web-project/pages/tabs-students.html");  // Σελίδα για Φοιτητή
        } elseif ($user['role'] == 'secretary') {
            header("Location: http://localhost/web-project/pages/tabs-secretary.html");  // Σελίδα για Γραμματεία
        }
        exit;
    } else {
        echo "Λάθος κωδικός!";
    }
} else {
    echo "Δεν βρέθηκε χρήστης με αυτό το email!";
}

$stmt->close();
$conn->close();

?>

