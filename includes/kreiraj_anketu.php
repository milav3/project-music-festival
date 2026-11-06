<?php
include_once __DIR__ . '/../core/function.php';
if ($_SESSION['tip_korisnika'] !== 'Administrator') {
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['naziv_ankete'])) {
    $naziv = $_POST['naziv_ankete'];
    $opis = $_POST['opis'];

    // 1️⃣ Unos ankete
    $stmt = $conn->prepare("INSERT INTO ankete (naziv_ankete, opis) VALUES (?, ?)");
    $stmt->bind_param("ss", $naziv, $opis);
    $stmt->execute();
    $anketa_id = $stmt->insert_id;

    $korisnici_query = $conn->query("SELECT korisnik_id FROM korisnici WHERE tip_korisnika = 'Posetilac'");

    // Dodavanje obaveštenja u bazu
    if ($korisnici_query->num_rows > 0) {
        echo '<script>console.log("rado");</script>';
        $stmt = $conn->prepare("INSERT INTO obavestenja (korisnik_id, izvodjac_id, izvodjac, poruka, procitano) VALUES (?, 0, ?, ?, 0)");

        while ($korisnik = $korisnici_query->fetch_assoc()) {
            $korisnik_id = $korisnik['korisnik_id'];
            $poruka = "Nova anketa: <a class='anketa' href='includes/popuni_anketu.php?id=$anketa_id'>$naziv</a> - Pogledajte i učestvujte!";
            $izvodjac = "Anketa";
            $stmt->bind_param("iss", $korisnik_id, $izvodjac, $poruka);
            $stmt->execute();
        }
        $stmt->close();
    }

    // 2️⃣ Unos pitanja
    $mapaPitanja = []; // Da bismo pravilno povezali pitanja i odgovore
    foreach ($_POST['pitanja'] as $index => $tekst_pitanja) {
        $tip_pitanja = $_POST['tip_pitanja'][$index];

        if (empty($tekst_pitanja) || empty($tip_pitanja)) {
            continue;
        }

        $stmt = $conn->prepare("INSERT INTO pitanja (anketa_id, tekst_pitanja, tip_pitanja) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $anketa_id, $tekst_pitanja, $tip_pitanja);
        $stmt->execute();
        $pitanje_id = $stmt->insert_id;

        $mapaPitanja[$index] = $pitanje_id; // Mapiramo frontend index na ID iz baze
    }

    // 3️⃣ Unos mogućih odgovora
    foreach ($_POST['odgovori'] as $index => $odgovoriArray) {
        if (isset($mapaPitanja[$index])) {
            $pitanje_id = $mapaPitanja[$index]; // Dobijamo ID pravog pitanja iz baze
            
            foreach ($odgovoriArray as $odgovor) {
                $odgovor = trim($odgovor);
                if (!empty($odgovor)) {
                    $stmt_odgovor = $conn->prepare("INSERT INTO moguci_odgovori (pitanje_id, tekst_odgovora) VALUES (?, ?)");
                    $stmt_odgovor->bind_param("is", $pitanje_id, $odgovor);
                    $stmt_odgovor->execute();
                }
            }
        }
    }

    header("Location: ../index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Kreiraj anketu</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Cinzel', serif;
        }
        body {
            background-image: url('../Slike/b5.jpg');
            background-repeat: repeat-y;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        h2 {
            text-align: center;
            color: white;
        }
        #anketa {
            color: green !important;
        }
        form {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 20px 20px rgba(0, 0, 0, 0.9);
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        input[type="text"], textarea, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        #pitanja-container {
            margin-top: 20px;
        }
        #pitanja-container div {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h2>Kreiraj novu anketu</h2>
    <form method="POST">
        <label>Naziv ankete:</label>
        <input type="text" name="naziv_ankete" required>
        
        <label>Opis ankete:</label>
        <textarea name="opis"></textarea>

        <div id="pitanja-container">
            <h3>Pitanja</h3>
        </div>
        
        <button type="button" onclick="dodajPitanje()">Dodaj pitanje</button>
        <button type="submit">Sačuvaj anketu</button>
    </form>

    <script>
    let pitanjeIndex = 0;

function dodajPitanje() {
    let container = document.getElementById("pitanja-container");
    let index = pitanjeIndex++;

    let div = document.createElement("div");
    div.setAttribute("data-id", index); // Dodeljujemo ID na osnovu poretka

    div.innerHTML = `
        <label>Pitanje:</label>
        <input type="text" name="pitanja[]" required>

        <label>Tip pitanja:</label>
        <select name="tip_pitanja[]" onchange="prikaziOdgovore(this, ${index})">
            <option value="radio">Višestruki izbor (jedan odgovor)</option>
            <option value="checkbox">Višestruki izbor (više odgovora)</option>
            <option value="text">Tekstualni odgovor</option>
        </select>

        <div class="odgovori-container">
            <input type="text" name="odgovori[${index}][]" placeholder="Odgovor 1">
            <button type="button" onclick="dodajOdgovor(this, ${index})">Dodaj još</button>
        </div>
    `;
    container.appendChild(div);
}



    function prikaziOdgovore(selectElement) {
        let container = selectElement.parentElement.querySelector('.odgovori-container');
        container.style.display = selectElement.value !== "text" ? "block" : "none";
    }

    function dodajOdgovor(button, index) {
        let container = button.parentElement;
        let input = document.createElement("input");
        input.type = "text";
        
        // Setujmo novo ime za odgovor
        input.name = `odgovori[${index}][]`; 
        input.placeholder = "Novi odgovor";
        container.appendChild(input);
    }
    </script>
</body>
</html>