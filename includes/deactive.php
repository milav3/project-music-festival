<?php
// Uključivanje fajla sa konekcijom sa bazom
// Podaci za konekciju
$host = 'localhost';      // ili IP adresa servera
$dbname = 'muzickifestival';     // Ime tvoje baze podataka
$username = 'root';   // Korisničko ime
$password = '';    // Lozinka

// Pokušaj konekciju sa bazom
$mysqli = new mysqli($host, $username, $password, $dbname);

// Proveri da li je konekcija uspela
if ($mysqli->connect_error) {
    die("Konekcija sa bazom nije moguća: " . $mysqli->connect_error);
}


// Provera da li je id prosleđen
if (isset($_GET['id'])) {
    echo "ID je prosleđen: " . $_GET['id'];  // Ispisuje ID u browseru

    $notifId = $_GET['id'];

    // Proveri da li je povezivanje sa bazom ispravno
    if ($mysqli) {
        // Priprema SQL upita
        $query = "UPDATE obavestenja SET procitano = 1 WHERE id = ?";
        
        // Pripremi upit sa mysqli
        if ($stmt = $mysqli->prepare($query)) {
            // Bindovanje parametra
            $stmt->bind_param("i", $notifId);
            
            // Izvršavanje upita
            if ($stmt->execute()) {
                echo "Obaveštenje je označeno kao pročitan.";
            } else {
                echo "Došlo je do greške pri označavanju obaveštenja.";
            }

            // Zatvaranje pripremljenog upita
            $stmt->close();
        } else {
            echo "Greška u pripremi upita.";
        }
    } else {
        echo "Konekcija sa bazom nije uspostavljena.";
    }
} else {
    echo "ID obaveštenja nije prosleđen.";
}

// Zatvaranje konekcije sa bazom
$mysqli->close();
?>
