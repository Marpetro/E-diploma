<?php
// get_committees.php den eimai sigouri an e;inai svsto

header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=web_project', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Εκτέλεση του SELECT ερωτήματος
    $stmt = $pdo->query('SELECT id, name, surname, status FROM committee');
    $committees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'committees' => $committees]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Σφάλμα: ' . $e->getMessage()]);
}
?>
