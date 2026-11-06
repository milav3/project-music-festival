<?php
include_once __DIR__ . '/../core/function.php';


if (!isset($_SESSION['id'])) {
    die("Morate biti prijavljeni da biste popunili anketu.");
}

$host = 'localhost';
$dbname = 'muzickifestival';
$user = 'root';
$password = '';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Greška pri povezivanju sa bazom: " . $conn->connect_error);
}

$anketa_id = $_GET['id'] ?? 0;
$korisnik_id = $_SESSION['id'];

// Proveri da li anketa postoji
$query = $conn->prepare("SELECT * FROM ankete WHERE id = ?");
$query->bind_param("i", $anketa_id);
$query->execute();
$anketa = $query->get_result()->fetch_assoc();

if (!$anketa) {
    die("Anketa ne postoji.");
}
// Proveri da li je korisnik već popunio anketu
$provera_query = $conn->prepare("SELECT COUNT(*) FROM odgovori WHERE anketa_id = ? AND korisnik_id = ?");
$provera_query->bind_param("ii", $anketa_id, $korisnik_id);
$provera_query->execute();
$provera_query->bind_result($broj_odgovora);
$provera_query->fetch();
$provera_query->close();

if ($broj_odgovora > 0) {
    die("Već ste popunili ovu anketu.");
}

// Dohvati sva pitanja za anketu
$pitanja = $conn->prepare("SELECT * FROM pitanja WHERE anketa_id = ? ORDER BY id ASC");
$pitanja->bind_param("i", $anketa_id);
$pitanja->execute();
$pitanja = $pitanja->get_result();


?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="slike/jpg" href="..\Slike\logob.png">
    <title><?php echo htmlspecialchars($anketa['naziv_ankete']); ?></title>
    <style>
        /* Opšti stil */
       
            body {
            font-family: Arial, sans-serif;
            background-image: url('../Slike/b5.jpg');
            background-repeat: repeat-y;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        

        /* Kontejner za anketu */
        .container {
            max-width: 600px;
            background: rgba(255, 255, 255, 0.9);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }

        p {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        /* Stil za pitanja */
        .pitanje {
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
            background: #f9f9f9;
            border-left: 4px solid #4CAF50;
        }

        /* Stil za odgovore */
        .odgovori label {
            display: flex;
            align-items: center;
            margin: 8px 0;
            cursor: pointer;
        }

        .odgovori input {
            margin-right: 8px;
        }

        /* Dugme za slanje */
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background-color: #388E3C;
        }

    </style>
</head>

<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($anketa['naziv_ankete']); ?></h2>
        <p><?php echo htmlspecialchars($anketa['opis']); ?></p>

        <form method="POST" action="sacuvaj_odgovore.php">
    <input type="hidden" name="anketa_id" value="<?php echo $anketa_id; ?>">

    <?php while ($pitanje = $pitanja->fetch_assoc()): ?>
        <div class="pitanje">
            <strong><?php echo htmlspecialchars($pitanje['tekst_pitanja']); ?></strong>
            <br>

            <?php 
            // PROVERA: Prikazujemo pitanje da vidimo da li se ispravno dohvaća
            //var_dump($pitanje); 
            if ($pitanje['tip_pitanja'] !== 'text') {
                $odgovori_query = $conn->prepare("SELECT id, tekst_odgovora FROM moguci_odgovori WHERE pitanje_id = ? ORDER BY redni_broj ASC");
                $odgovori_query->bind_param("i", $pitanje['id']);
                $odgovori_query->execute();
                $odgovori_result = $odgovori_query->get_result();
                $moguci_odgovori = $odgovori_result->fetch_all(MYSQLI_ASSOC);
            } else {
                $moguci_odgovori = []; // Prazan niz jer nema ponuđenih odgovora za tekstualno pitanje
            }
            
           
            
            // PROVERA: Prikazujemo moguće odgovore
           // var_dump($odgovori_result->fetch_all(MYSQLI_ASSOC));
            ?>

            <?php if ($pitanje['tip_pitanja'] === 'radio' || $pitanje['tip_pitanja'] === 'checkbox'): ?>
                <?php 
                $odgovori_query->execute();
                $odgovori_result = $odgovori_query->get_result();
                while ($odgovor = $odgovori_result->fetch_assoc()): ?>
                    <label>
                        <input type="<?php echo $pitanje['tip_pitanja']; ?>" 
                               name="odgovor_<?php echo $pitanje['id']; ?><?php echo ($pitanje['tip_pitanja'] === 'checkbox') ? '[]' : ''; ?>" 
                               value="<?php echo htmlspecialchars($odgovor['id']); ?>">
                        <?php echo htmlspecialchars($odgovor['tekst_odgovora']); ?>
                    </label>
                    <br>
                <?php endwhile; ?>
            <?php else: ?>
                <input type="text" name="odgovor_<?php echo $pitanje['id']; ?>">
            <?php endif; ?>
        </div>
        <br>
    <?php endwhile; ?>

    <button type="submit">Pošaljite odgovore</button>
</form>

    </div>
</body>
</html>
