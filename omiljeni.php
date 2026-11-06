<?php
include 'core/function.php'; 
include 'includes/overall/header.php';

// Provera da li je korisnik ulogovan
if (!isset($_SESSION['id'])) {
    echo "Greška: Korisnik nije ulogovan.";
    exit;
}

$posetilac_id = $_SESSION['id'];

// Dohvatanje podataka o korisniku
$user_query = mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnik_id = $posetilac_id");
if (mysqli_num_rows($user_query) == 0) {
    echo "Greška: Korisnik nije pronađen.";
    exit;
}
$user = mysqli_fetch_assoc($user_query);

// Provera tipa korisnika
if ($user['tip_korisnika'] !== 'Posetilac') {
    $stranica = strtolower($user['tip_korisnika']) . '.php';
    header("Location: $stranica");
    exit;
}

// Dodavanje izvođača u omiljene
if (isset($_POST['dodaj_omiljenog'])) {
    $izvodjac_id = intval($_POST['izvodjac_id']);
    $check_query = "SELECT * FROM omiljeni WHERE posetilac_id = $posetilac_id AND izvodjac_id = $izvodjac_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        $insert_query = "INSERT INTO omiljeni (posetilac_id, izvodjac_id) VALUES ($posetilac_id, $izvodjac_id)";
        mysqli_query($conn, $insert_query);
    }
}

// Uklanjanje izvođača iz omiljenih
if (isset($_POST['ukloni_omiljenog'])) {
    $izvodjac_id = intval($_POST['izvodjac_id']);
    $delete_query = "DELETE FROM omiljeni WHERE posetilac_id = $posetilac_id AND izvodjac_id = $izvodjac_id";
    mysqli_query($conn, $delete_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omiljeni izvođači</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Postavite body kao flex container */
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f9;
        display: flex; /* Dodajte flexbox */
        flex-direction: column; /* Stavite elemente u kolonu */
        min-height: 100vh; /* Postavite minimalnu visinu na 100% visine prozora */
    }

    /* Kontejner zadržava sve ostale stilove */
    .container {
        flex: 1; /* Uzimamo sav preostali prostor između headera i footera */
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background: #fff;
        border-radius: 0;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    /* Stilizovanje omiljenih izvođača i izvođača */
    .omiljeni-lista, .izvodjaci-lista {
        margin: 20px 0;
        padding: 10px;
        border: 1px solid #ccc;
        background-color: #f9f9f9;
        border-radius: 5px;
    }

    .omiljeni-lista h3, .izvodjaci-lista h3 {
        margin-bottom: 10px;
    }

    .omiljeni-lista div, .izvodjaci-lista div {
        margin-bottom: 5px;
    }

    button {
        background-color: transparent;
        border: none;
        color: #e74c3c;
        font-size: 1.5rem;
        cursor: pointer;
        transition: transform 0.2s ease, color 0.3s ease;
    }

    button:hover {
        transform: scale(1.2);
        color: #c0392b;
    }

    .poruka {
        margin: 10px 0;
        color: green;
    }

    .izvodjaci-lista div {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Footer */
    footer {

        text-align: center;
        padding: 10px 0;
        margin-top: auto; /* Ovo je ključno da footer bude na dnu */
    }

    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($user['ime']); ?>, dobrodošli na Vašu listu omiljenih izvođača!</h2>

        <!-- Prikaz omiljenih izvođača -->
        <div class="omiljeni-lista">
            <h3>Vaši omiljeni izvođači:</h3>
            <?php
            $omiljeni_query = "
                SELECT k.korisnik_id, k.ime, k.prezime
                FROM omiljeni o
                JOIN korisnici k ON o.izvodjac_id = k.korisnik_id
                WHERE o.posetilac_id = $posetilac_id";
            $omiljeni_result = mysqli_query($conn, $omiljeni_query);

            if (mysqli_num_rows($omiljeni_result) > 0) {
                while ($row = mysqli_fetch_assoc($omiljeni_result)) {
                    echo "<div style='display: flex;'>
                            <p style='width: 89%;'>" . htmlspecialchars($row['ime']) . " " . htmlspecialchars($row['prezime']) . "</p>
                            <form method='post' style='display:inline;'>
                                <input type='hidden' name='izvodjac_id' value='" . $row['korisnik_id'] . "'>
                                <button type='submit' name='ukloni_omiljenog'>
                                    <i class='fas fa-times'></i>
                                </button>
                            </form>
                          </div>";
                }
            } else {
                echo "<p>Nemate omiljenih izvođača.</p>";
            }
            ?>
        </div>

        <!-- Prikaz svih izvođača -->
        <div class="izvodjaci-lista">
            <h3>Dodajte izvođače u omiljene:</h3>
            <?php
            $izvodjaci_query = "SELECT * FROM korisnici WHERE tip_korisnika = 'Izvodjac'";
            $izvodjaci_result = mysqli_query($conn, $izvodjaci_query);

            if (mysqli_num_rows($izvodjaci_result) > 0) {
                while ($row = mysqli_fetch_assoc($izvodjaci_result)) {
                    echo "<div>
                            <p>" . htmlspecialchars($row['ime']) . " " . htmlspecialchars($row['prezime']) . "</p>
                            <form method='post' style='display:inline;'>
                                <input type='hidden' name='izvodjac_id' value='" . $row['korisnik_id'] . "'>
                                <button type='submit' name='dodaj_omiljenog'>
                                    <i class='fas fa-heart'></i>
                                </button>
                            </form>
                          </div>";
                }
            } else {
                echo "<p>Nema dostupnih izvođača.</p>";
            }
            ?>
        </div>
    </div>
    <?php include 'includes/overall/footer.php'; ?>
</body>
</html>

