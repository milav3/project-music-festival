<?php
include 'core/function.php'; 
if(isset($_SESSION["id"])){
    $id = $_SESSION["id"];
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnik_id = $id"));
    if($user['tip_korisnika']!='Izvodjac'){
        $stranica = strtolower($user['tip_korisnika']) . '.php';
        header("Location: $stranica");
    }
}
else{
    header("Location: login.php");
}

$izvodjac_id = $_SESSION['id']; 
$poruka_status = "";
$izvodjac_result = mysqli_query($conn, "SELECT ime,prezime FROM korisnici WHERE korisnik_id = $izvodjac_id");
if ($izvodjac_result) {
    $izvodjac_row = mysqli_fetch_assoc($izvodjac_result);
    $izvodjac = $izvodjac_row['ime'] . " " . $izvodjac_row['prezime']; 
} else {
    $izvodjac = "Nepoznato ime"; 
}



// Ako je forma poslata
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['poruka'])) {
    $poruka = trim($_POST['poruka']);

    if (!empty($poruka)) {
        // Pronalazak svih korisnika koji prate izvođača
        $sql_followers = "SELECT posetilac_id FROM omiljeni WHERE izvodjac_id = ?";
        $stmt_followers = $conn->prepare($sql_followers);
        $stmt_followers->bind_param("i", $izvodjac_id);
        $stmt_followers->execute();
        $result = $stmt_followers->get_result();

        // Ubacivanje obaveštenja za svakog posetioca
        $sql_insert = "INSERT INTO obavestenja (korisnik_id, izvodjac_id, izvodjac, poruka) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);

        while ($row = $result->fetch_assoc()) {
            $korisnik_id = $row['posetilac_id'];
            $stmt_insert->bind_param("iiss", $korisnik_id, $izvodjac_id, $izvodjac, $poruka);
            $stmt_insert->execute();
        }

        $poruka_status = "Obaveštenje uspešno poslato!";
    } else {
        $poruka_status = "Poruka ne može biti prazna!";
    }
}


?>
<style>
    .obavestenja {
        max-width: 500px; 
        margin: auto; 
    }
    textarea { 
        width: 100%; 
        height: 100px; 
        margin-bottom: 10px; 
    }
    .status { 
        margin-top: 10px; 
        color: green; 
    }
</style>

<?php include 'includes/overall/header.php'; ?>

<div class="obavestenja">
    <h2 style="color:white;">Pošalji obaveštenje pratiocima</h2>
    <form method="POST">
        <textarea name="poruka" placeholder="Unesi obaveštenje..." required></textarea>
        <button type="submit" class="btn" style="width: 100px; position: relative; left: 160px;">Pošalji</button>
    </form>
    <?php if (!empty($poruka_status)): ?>
        <p class="status"><?php echo htmlspecialchars($poruka_status); ?></p>
    <?php endif; ?>
</div>
<?php include 'includes/overall/footer.php'; ?>
