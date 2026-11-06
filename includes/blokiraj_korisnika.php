<?php
session_start(); // Osiguraj da sesija započne

$host = 'localhost';
$dbname = 'muzickifestival';
$user = 'root';
$password = '';

$conn = new mysqli($host, $user, $password, $dbname);

// Provera konekcije
if ($conn->connect_error) {
    die("Greška pri povezivanju sa bazom: " . $conn->connect_error);
}

// Provera da li je korisnik administrator
$tip_korisnika = $_SESSION['tip_korisnika'] ?? '';

if ($tip_korisnika !== 'Administrator') {
    die("Nemate dozvolu za ovu akciju!");
}

// Obrada blokiranja korisnika
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['korisnik_id'])) {
    $korisnik_id = $_POST['korisnik_id'];

    // Provera trenutnog statusa
    $query = $conn->prepare("SELECT blokiran FROM korisnici WHERE korisnik_id = ?");
    $query->bind_param("i", $korisnik_id);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $novi_status = $row['blokiran'] ? 0 : 1; // Menja status
        $update = $conn->prepare("UPDATE korisnici SET blokiran = ? WHERE korisnik_id = ?");
        $update->bind_param("ii", $novi_status, $korisnik_id);
        $update->execute();
    }
}

// Preusmeravanje nazad na istu stranicu
if (!empty($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: index.php"); // Ako HTTP_REFERER ne postoji, preusmeri na početnu stranicu
}
exit;