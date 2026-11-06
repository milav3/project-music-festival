<?php
session_start();
$host = 'localhost';
$dbname = 'muzickifestival';
$user = 'root';
$password = '';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(array('message' => 'Greška pri povezivanju sa bazom')));
}

// Validacija unosa
$stars = isset($_POST['stars']) ? (int)$_POST['stars'] : 0;
$korisnik_id = isset($_POST['korisnik_id']) ? (int)$_POST['korisnik_id'] : 0;
$festival_id = isset($_POST['festival_id']) ? (int)$_POST['festival_id'] : 0;

if ($stars < 1 || $stars > 5 || $korisnik_id <= 0 || $festival_id <= 0) {
    die(json_encode(array('message' => 'Nevažeći unos')));
}

// Provera da li korisnik već ocenjuje ovaj festival
$sqlCheck = "SELECT * FROM ocenjivanje WHERE korisnik_id = ? AND festival_id = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param('ii', $korisnik_id, $festival_id);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows > 0) {
    $sqlUpdate = "UPDATE ocenjivanje SET ocena = ? WHERE korisnik_id = ? AND festival_id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param('iii', $stars, $korisnik_id, $festival_id);
    $stmtUpdate->execute();
    echo json_encode(array('message' => 'Ocena ažurirana uspešno'));
} else {
    $sqlInsert = "INSERT INTO ocenjivanje (korisnik_id, korisnicko_ime, ocena, festival_id) VALUES (?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $korisnicko_ime = $_SESSION['username'];
    $stmtInsert->bind_param('isii', $korisnik_id, $korisnicko_ime, $stars, $festival_id);
    $stmtInsert->execute();
    echo json_encode(array('message' => 'Ocena sačuvana uspešno'));
}

$conn->close();
?>
