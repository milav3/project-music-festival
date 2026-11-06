<?php
include_once __DIR__ . '/../core/function.php';

if (!isset($_SESSION['id'])) {
    die("Morate biti prijavljeni da biste popunili anketu.");
}

$conn = new mysqli("localhost", "root", "", "muzickifestival");
if ($conn->connect_error) {
    die("Greška pri povezivanju sa bazom: " . $conn->connect_error);
}

$korisnik_id = $_SESSION['id'];
$anketa_id = $_POST['anketa_id'] ?? 0;

foreach ($_POST as $key => $value) {
    if (strpos($key, "odgovor_") === 0) {
        $pitanje_id = str_replace("odgovor_", "", $key);

        // Provera tipa pitanja
        $tip_pitanja = "";
        $stmt = $conn->prepare("SELECT tip_pitanja FROM pitanja WHERE id = ?");
        $stmt->bind_param("i", $pitanje_id);
        $stmt->execute();
        $stmt->bind_result($tip_pitanja);
        $stmt->fetch();
        $stmt->close();

        if ($tip_pitanja == "text") {  
            // Čuvamo tekstualni odgovor
            $stmt = $conn->prepare("INSERT INTO odgovori (pitanje_id, korisnik_id, tekst_odgovora, anketa_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iisi", $pitanje_id, $korisnik_id, $value, $anketa_id);
            $stmt->execute();
        } else {
            // Radio/Checkbox - unosimo ID odgovora
            if (is_array($value)) { 
                foreach ($value as $moguci_odgovor_id) {
                    $stmt = $conn->prepare("INSERT INTO odgovori (pitanje_id, korisnik_id, moguci_odgovor_id, anketa_id) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiii", $pitanje_id, $korisnik_id, $moguci_odgovor_id, $anketa_id);
                    $stmt->execute();
                }
            } else { 
                $stmt = $conn->prepare("INSERT INTO odgovori (pitanje_id, korisnik_id, moguci_odgovor_id, anketa_id) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiii", $pitanje_id, $korisnik_id, $value, $anketa_id);
                $stmt->execute();
            }
        }
    }
}

$conn->close();
header("Location: ../posetilac.php");
exit();
?>
