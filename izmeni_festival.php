<?php
include 'core/function.php';
$host = 'localhost';
$dbname = 'muzickifestival';
$user = 'root';
$password = '';

$conn = new mysqli($host, $user, $password, $dbname);

// Provera konekcije
if ($conn->connect_error) {
    die("Greška pri povezivanju sa bazom: " . $conn->connect_error);
}

// Provera da li je administrator
if ($_SESSION['tip_korisnika'] !== 'Administrator' && $_SESSION['tip_korisnika'] !== 'Organizator') {
    die("Nemate dozvolu za izmenu festivala.");
}

// Provera da li su svi podaci poslati
if (!isset($_POST['festival_id']) || empty($_POST['festival_id'])) {
    die("Greška: Festival nije naveden.");
}

$festival_id = $_POST['festival_id'];
$naziv = $_POST['naziv_festivala'];
$opis = $_POST['opis'];
$datum_pocetka = $_POST['datum_pocetka'];
$datum_zavrsetka = $_POST['datum_zavrsetka'];
$cena_ulaznica = $_POST['cena_ulaznica'];

// Provera da li je poslata nova slika
if (!empty($_FILES['slika']['name'])) {
    $slika = 'slike/' . basename($_FILES['slika']['name']);
    move_uploaded_file($_FILES['slika']['tmp_name'], $slika);

    // Ažuriranje festivala sa slikom
    $query = $conn->prepare("UPDATE festivali SET naziv_festivala=?, opis=?, datum_pocetka=?, datum_zavrsetka=?, cena_ulaznica=?, slika=? WHERE festival_id=?");
    $query->bind_param("ssssisi", $naziv, $opis, $datum_pocetka, $datum_zavrsetka, $cena_ulaznica, $slika, $festival_id);
} else {
    // Ažuriranje festivala bez slike
    $query = $conn->prepare("UPDATE festivali SET naziv_festivala=?, opis=?, datum_pocetka=?, datum_zavrsetka=?, cena_ulaznica=? WHERE festival_id=?");
    $query->bind_param("ssssii", $naziv, $opis, $datum_pocetka, $datum_zavrsetka, $cena_ulaznica, $festival_id);
}

// Izvrši upit
if ($query->execute()) {
    // Ažuriranje scena
    if (!empty($_POST['scene'])) {
        foreach ($_POST['scene'] as $scena_id => $scena_naziv) {
            if (!empty($scena_naziv)) {
                $scene_query = $conn->prepare("UPDATE scene SET naziv = ? WHERE id = ? AND festival_id = ?");
                $scene_query->bind_param("sii", $scena_naziv, $scena_id, $festival_id);
                $scene_query->execute();
            }
        }
    }

    if (!empty($_POST['izvodjaci'])) {
        foreach ($_POST['izvodjaci'] as $izvodjac_id => $novo_ime) {
            $novo_ime = trim($novo_ime);
        
            if (!empty($novo_ime)) {
                // Pronađi ID korisnika na osnovu unetog imena
                $korisnik_query = $conn->prepare("SELECT korisnik_id FROM korisnici WHERE ime = ? LIMIT 1");
                $korisnik_query->bind_param("s", $novo_ime);
                $korisnik_query->execute();
                $korisnik_result = $korisnik_query->get_result();
        
                if ($korisnik_result->num_rows > 0) {
                    $korisnik = $korisnik_result->fetch_assoc();
                    $novi_korisnik_id = $korisnik['korisnik_id'];
        
                    // Ažuriraj ID korisnika u tabeli `izvodjaci`
                    $update_query = $conn->prepare("UPDATE izvodjaci SET korisnik_id = ? WHERE id = ?");
                    $update_query->bind_param("ii", $novi_korisnik_id, $izvodjac_id);
                    
                    // Nedostajao je poziv execute()
                    if ($update_query->execute()) {
                        echo "Izvođač uspešno ažuriran!<br>";
                    } else {
                        echo "Greška pri ažuriranju izvođača: " . $conn->error . "<br>";
                    }
                } else {
                    echo "Korisnik sa imenom '$novo_ime' nije pronađen.<br>";
                }
            }
        }
    }
    
}
// Provera da li su svi podaci poslati za datum i vreme nastupa
if (!empty($_POST['datum_nastupa']) && is_array($_POST['datum_nastupa'])) {
    foreach ($_POST['datum_nastupa'] as $izvodjac_id => $datum_nastupa) {
        $datum_nastupa = trim($datum_nastupa);
        if (!empty($datum_nastupa)) {
            $update_time_query = $conn->prepare("UPDATE izvodjaci SET datum_nastupa = ? WHERE id = ?");
            $update_time_query->bind_param("si", $datum_nastupa, $izvodjac_id);
            if (!$update_time_query->execute()) {
                echo "Greška pri ažuriranju datuma nastupa: " . $conn->error . "<br>";
            }
        }
    }
}

// Provera da li je vreme nastupa poslato
if (!empty($_POST['vreme_nastupa']) && is_array($_POST['vreme_nastupa'])) {
    foreach ($_POST['vreme_nastupa'] as $izvodjac_id => $vreme_nastupa) {
        $vreme_nastupa = trim($vreme_nastupa);
        if (!empty($vreme_nastupa)) {
            $update_time_query = $conn->prepare("UPDATE izvodjaci SET vreme_nastupa = ? WHERE id = ?");
            $update_time_query->bind_param("si", $vreme_nastupa, $izvodjac_id);
            if (!$update_time_query->execute()) {
                echo "Greška pri ažuriranju vremena nastupa: " . $conn->error . "<br>";
            }
        }
    }
}


    // Dodavanje novog izvođača
if (!empty($_POST['novi_izvodjac']) && !empty($_POST['novo_vreme']) && !empty($_POST['scena_id'])) {
    $novi_izvodjac = $_POST['novi_izvodjac'];
    $novi_datum = $_POST['novi_datum'];
    $novo_vreme = $_POST['novo_vreme'];
    $scena_id = $_POST['scena_id'];

    // Provera da li izvođač postoji u tabeli korisnici
    $korisnik_query = $conn->prepare("SELECT korisnik_id FROM korisnici WHERE ime = ? LIMIT 1");
    $korisnik_query->bind_param("s", $novi_izvodjac);
    $korisnik_query->execute();
    $korisnik_query->store_result();

    if ($korisnik_query->num_rows > 0) {
        $korisnik_query->bind_result($korisnik_id);
        $korisnik_query->fetch();

        // Ubacivanje novog izvođača sa imenom iz tabele korisnici
        $insert_query = $conn->prepare("INSERT INTO izvodjaci (korisnik_id, scena_id, vreme_nastupa, datum_nastupa) VALUES (?, ?, ?, ?)");
        $insert_query->bind_param("iiss", $korisnik_id, $scena_id, $novo_vreme, $novi_datum);
        $insert_query->execute();
       
    }
}

$conn->close();

// Preusmeravanje na organizator.php nakon uspešne izmene
header("Location: organizator.php");
exit();

?>
