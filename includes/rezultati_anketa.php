<?php
include_once __DIR__ . '/../core/function.php';
// Provera da li je korisnik prijavljen
$korisnicko_ime = $_SESSION['username'] ?? '';
$tip_korisnika = $_SESSION['tip_korisnika'] ?? '';
// Ako je korisnik prijavljen, postavljamo njegov tip korisnika
if (!empty($korisnicko_ime) && empty($tip_korisnika)) {
    $stmt = $conn->prepare("SELECT tip_korisnika FROM korisnici WHERE korisnicko_ime = ?");
    $stmt->bind_param("s", $korisnicko_ime);
    $stmt->execute();
    $result = $stmt->get_result();
    $korisnik = $result->fetch_assoc();

    if ($korisnik) {
        $_SESSION['tip_korisnika'] = $korisnik['tip_korisnika'];
        $tip_korisnika = $korisnik['tip_korisnika'];
    }
}
// Provera da li je korisnik administrator
if (!isset($_SESSION['tip_korisnika']) || $_SESSION['tip_korisnika'] !== 'Administrator') {
    die("Nemate dozvolu za pristup!");
}
$host = 'localhost';
$dbname = 'muzickifestival';
$user = 'root';
$password = '';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Greška pri povezivanju sa bazom: " . $conn->connect_error);
}

// Dohvati sve ankete
$ankete_query = "SELECT id, naziv_ankete, opis FROM ankete";
$ankete = $conn->query($ankete_query);
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Rezultati anketa</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="icon" type="slike/jpg" href="../Slike\logob.png">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            max-width: 800px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 20px 20px rgba(0, 0, 0, 0.9);
            width: 100%;
            margin-top: auto;
        }   
        h2 {
            text-align: center;
            color: #333;
            padding-top: 20px;
        }
        h3 {
            color: #4CAF50;
            text-align: center;
            font-size: 18px;
        }
        p {
            color: #555;
        }
        ul {
            list-style-type: none;
            padding-left: 0;
        }
        li {
            background-color: #fff;
            margin: 10px 0;
            padding: 10px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        li strong {
            color: #333;
        }
        li:hover {
            background-color: #f1f1f1;
        }
        .odgovori {
            background-color: #f9f9f9;
            padding: 5px;
            margin-top: 10px;
            border-radius: 4px;
        }
        .odgovori li {
            background-color: #e7f7e7;
            padding: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Rezultati anketa</h2>

        <?php while ($anketa = $ankete->fetch_assoc()): ?>
            <h3><?php echo htmlspecialchars($anketa['naziv_ankete']); ?></h3>
            <p><?php echo htmlspecialchars($anketa['opis']); ?></p>
            <ul>
                <?php
                $sql = "SELECT o.tekst_odgovora, COUNT(o.id) as broj_odgovora 
                FROM odgovori o
                WHERE o.pitanje_id = ?
                GROUP BY o.tekst_odgovora";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $pitanje_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            echo $row['tekst_odgovora'] . ": " . $row['broj_odgovora'] . " glasova<br>";
        }
        
                // Dohvati pitanja za datu anketu
                $pitanja_query = "SELECT id, tekst_pitanja FROM pitanja WHERE anketa_id = ?";
                $stmt_pitanja = $conn->prepare($pitanja_query);
                $stmt_pitanja->bind_param("i", $anketa['id']);
                $stmt_pitanja->execute();
                $pitanja = $stmt_pitanja->get_result();

                while ($pitanje = $pitanja->fetch_assoc()):
                    echo "<li><strong>" . htmlspecialchars($pitanje['tekst_pitanja']) . "</strong></li>";

                    // Dohvati odgovore i broj glasova za svako pitanje
                    $odgovori_query = "SELECT mo.tekst_odgovora, COUNT(o.moguci_odgovor_id) AS broj 
                   FROM odgovori o
                   JOIN moguci_odgovori mo ON o.moguci_odgovor_id = mo.id
                   WHERE o.pitanje_id = ?
                   GROUP BY mo.tekst_odgovora";

$stmt_odgovori = $conn->prepare($odgovori_query);
$stmt_odgovori->bind_param("i", $pitanje['id']);
$stmt_odgovori->execute();
$odgovori = $stmt_odgovori->get_result();

$prikazano = false; // Proveravamo da li ima odgovora

if ($odgovori->num_rows > 0) {
    echo "<ul class='odgovori'>";
    while ($odgovor = $odgovori->fetch_assoc()):
        echo "<li>" . htmlspecialchars($odgovor['tekst_odgovora']) . " - " . $odgovor['broj'] . " glasova</li>";
        $prikazano = true;
    endwhile;
    echo "</ul>";
}

// **Dohvati i prikaži tekstualne odgovore**
$tekstualni_query = "SELECT tekst_odgovora FROM odgovori WHERE pitanje_id = ? AND moguci_odgovor_id IS NULL";
$stmt_tekstualni = $conn->prepare($tekstualni_query);
$stmt_tekstualni->bind_param("i", $pitanje['id']);
$stmt_tekstualni->execute();
$tekstualni = $stmt_tekstualni->get_result();

if ($tekstualni->num_rows > 0) {
    echo "<ul class='odgovori'>";
    while ($row = $tekstualni->fetch_assoc()):
        echo "<li>" . htmlspecialchars($row['tekst_odgovora']) . "</li>";
        $prikazano = true;
    endwhile;
    echo "</ul>";
}

// Ako nema nijednog odgovora, prikaži poruku
if (!$prikazano) {
    echo "<p>Nema odgovora za ovo pitanje.</p>";
}

                endwhile;
                ?>
            </ul>
        <?php endwhile; ?>
    </div>
</body>

</html>
