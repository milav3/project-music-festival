<?php
include 'core/function.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Nevažeći ID festivala.";
    exit;
}

$festival_id = intval($_GET['id']);
$festival_query = $conn->prepare("SELECT * FROM festivali WHERE festival_id = ?");
$festival_query->bind_param("i", $festival_id);
$festival_query->execute();
$festival_result = $festival_query->get_result();
$festival = $festival_result->fetch_assoc();

if (!$festival) {
    echo "Festival nije pronađen.";
    exit;
}

$tip_korisnika = $_SESSION['tip_korisnika'] ?? '';

// Dohvatanje scena
$scene_query = $conn->prepare("SELECT * FROM scene WHERE festival_id = ?");
$scene_query->bind_param("i", $festival_id);
$scene_query->execute();
$scene_result = $scene_query->get_result();
$statistika_query = $conn->prepare("INSERT INTO statistika_poseta (festival_id, korisnik_id, tip_akcije) VALUES (?, ?, 'poseta_festivalu')");
$statistika_query->bind_param("ii", $festival_id, $korisnik_id);
$statistika_query->execute();
include 'includes/overall/header.php';
?>

<div class="festival-details">
    <h2 id="naziv-festivala"><?php echo htmlspecialchars($festival['naziv_festivala']); ?></h2>
    <img src="<?php echo htmlspecialchars($festival['slika']); ?>" style="width:560px; height:315px; border-radius:5px" alt="Festival Image" class="festival-images">
    <div class="description">    
        <p style="text-align: justify;"><?php echo nl2br(htmlspecialchars($festival['opis'])); ?></p>
        <p><strong>📅 </strong> 
        <?php 
            $formatter = new IntlDateFormatter(
                'sr', 
                IntlDateFormatter::LONG, 
                IntlDateFormatter::NONE
            );
            
            $datumPocetka = new DateTime($festival['datum_pocetka']);
            echo $formatter->format($datumPocetka);
            
        ?> 
        <strong>-</strong> 
        <?php 
            $formatter = new IntlDateFormatter(
                'sr', 
                IntlDateFormatter::LONG, 
                IntlDateFormatter::NONE
            );

            $datum_zavrsetka = new DateTime($festival['datum_zavrsetka']);
            echo $formatter->format($datum_zavrsetka);
        ?>
        </p>
        <p><strong>📍 Lokacija: </strong> <?php echo htmlspecialchars($festival['lokacija']); ?></p>
        <p><strong> 🎟️ Ulaz: </strong><?php echo htmlspecialchars($festival['cena_ulaznica']); ?> RSD</p>
    </div>
    <?php if (!empty($festival['stream'])): ?>
        <?php 
        if (preg_match('/v=([^&]+)/', $festival['stream'], $matches)) {
            $youtube_id = htmlspecialchars($matches[1]);
        ?>
        <iframe width="560" height="315" 
                src="https://www.youtube.com/embed/<?php echo $youtube_id; ?>" 
                frameborder="0" allowfullscreen>
        </iframe>
        <?php } else {
            echo "Nema validnog streama.";
        }
        ?>
    <?php endif; ?>
    <?php while ($scena = $scene_result->fetch_assoc()): ?>
    <div class="scena" onclick="prikaziIzvodjace(<?php echo $scena['id']; ?>); zabeleziPosetuSceni(<?php echo $scena['id']; ?>, <?php echo $festival_id; ?>);">
        <h3><?php echo htmlspecialchars($scena['naziv']); ?></h3>
    </div>
        <ul id="izvodjaci_<?php echo $scena['id']; ?>" class="izvodjaci">
            <?php
            $izvodjaci_query = $conn->prepare(
                "SELECT i.datum_nastupa, k.ime, k.prezime, i.vreme_nastupa FROM izvodjaci i 
                JOIN korisnici k ON i.korisnik_id = k.korisnik_id
                WHERE i.scena_id = ?"
            );
            $izvodjaci_query->bind_param("i", $scena['id']);
            $izvodjaci_query->execute();
            $izvodjaci_result = $izvodjaci_query->get_result();
            while ($izvodjac = $izvodjaci_result->fetch_assoc()): 
                $formatter = new IntlDateFormatter(
                    'sr_RS', 
                    IntlDateFormatter::LONG, 
                    IntlDateFormatter::NONE
                );
                
                $datumnastupa = new DateTime($izvodjac['datum_nastupa']);                
                ?>
                <li><?php echo htmlspecialchars($formatter->format($datumnastupa)); ?> <?php echo htmlspecialchars($izvodjac['ime']); ?> <?php echo htmlspecialchars($izvodjac['prezime']); ?> - <?php echo htmlspecialchars($izvodjac['vreme_nastupa']); ?></li>
            <?php endwhile; ?>
        </ul>
    <?php endwhile; ?>

    <?php include 'includes/share.php'; ?>

    <iframe width="100%" height="350" style="border:0" loading="lazy" allowfullscreen
        src="<?php echo htmlspecialchars($festival['link_lokacije']); ?>">
    </iframe>

    <script>
function zabeleziPosetuSceni(scena_id, festival_id) {
    let cookieName = "scena_" + scena_id;
    
    // Pošalji AJAX zahtev ka PHP fajlu za beleženje posete
    fetch('zabelezi_statistiku.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `scena_id=${scena_id}&festival_id=${festival_id}`
    })
    .then(response => response.json())
    .then(data => console.log(data.message))
    .catch(error => console.error('Greška:', error));
}

</script>
 
<?php 
if ($tip_korisnika === 'Posetilac') {
    
    
    // Provera da li korisnik već ima rezervaciju i koji je status
    $rezervacija_query = $conn->prepare("SELECT broj_mesta, status 
                                     FROM rezervacije 
                                     WHERE korisnik_id = ? 
                                     AND festival_id = ? 
                                     ORDER BY id DESC 
                                     LIMIT 1");

    $rezervacija_query->bind_param("ii", $korisnik_id, $festival_id);
    $rezervacija_query->execute();
    $rezervacija_result = $rezervacija_query->get_result();
    $rezervacija = $rezervacija_result->fetch_assoc();
    
    // Provera da li korisnik ima aktivnu rezervaciju
    $status_rezervacije = isset($rezervacija['status']) ? trim(strtolower($rezervacija['status'])) : 'N/A';
    $korisnik_ima_rezervaciju = $rezervacija && $status_rezervacije === 'rezervisano';


    // Dohvatanje broja preostalih ulaznica
    $upit = $conn->prepare("SELECT broj_preostalih_ulaznica FROM festivali WHERE festival_id = ?");
    $upit->bind_param("i", $festival_id);
    $upit->execute();
    $rezultat = $upit->get_result();
    $festival = $rezultat->fetch_assoc();
?>

<div class="rezervacija-forma">
    <h2><?php echo $korisnik_ima_rezervaciju ? 'Vaša rezervacija' : 'Rezervacija ulaznica'; ?></h2>

    <p>Preostalo mesta: <strong><?php echo $festival['broj_preostalih_ulaznica']; ?></strong></p>

    <?php if (!$korisnik_ima_rezervaciju): ?>
        <form method="post" action="">
            <label for="broj_mesta">Broj mesta:</label>
            <input type="number" name="broj_mesta" id="broj_mesta" min="1" required>
            <button type="submit" name="rezervisi">✅ Rezerviši</button>
        </form>
    <?php else: ?>
        <p>Imate rezervaciju za <strong><?php echo $rezervacija['broj_mesta']; ?></strong> mesta.</p>
    <?php endif; ?>

    <?php if ($korisnik_ima_rezervaciju): ?>
    <form method="post" action="">
        <button type="submit" name="otkazi">❌ Otkazivanje rezervacije</button>
    </form>
<?php endif; ?>

</div>

<?php
    // Obrada rezervacije
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rezervisi'])) {
        $broj_mesta = intval($_POST['broj_mesta']);

        // Provera dostupnih mesta
        if ($festival['broj_preostalih_ulaznica'] >= $broj_mesta) {
            // Dodaj ili ažuriraj rezervaciju
            $insert_query = $conn->prepare("
    INSERT INTO rezervacije (korisnik_id, festival_id, broj_mesta, status) 
    VALUES (?, ?, ?, 'rezervisano') 
    ON DUPLICATE KEY UPDATE broj_mesta = broj_mesta + VALUES(broj_mesta), status = 'rezervisano';
");


            $insert_query->bind_param("iii", $korisnik_id, $festival_id, $broj_mesta);
            $insert_query->execute();

            // Ažuriraj broj preostalih ulaznica
            $update_query = $conn->prepare("UPDATE festivali SET broj_preostalih_ulaznica = broj_preostalih_ulaznica - ? WHERE festival_id = ?");
            $update_query->bind_param("ii", $broj_mesta, $festival_id);
            $update_query->execute();

            header("Location: fest.php?id=$festival_id");
            exit;
        }
    }

    // Obrada otkazivanja rezervacije
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otkazi'])) {
    if ($korisnik_ima_rezervaciju && $rezervacija['status'] !== 'otkazano') {
        $broj_mesta = intval($rezervacija['broj_mesta']);

        $update_reservation = $conn->prepare("UPDATE rezervacije SET status = 'otkazano' WHERE korisnik_id = ? AND festival_id = ?");
        $update_reservation->bind_param("ii", $korisnik_id, $festival_id);
        $update_reservation->execute();

        $update_query = $conn->prepare("UPDATE festivali SET broj_preostalih_ulaznica = broj_preostalih_ulaznica + ? WHERE festival_id = ?");
        $update_query->bind_param("ii", $broj_mesta, $festival_id);
        $update_query->execute();

        header("Location: fest.php?id=$festival_id");
        exit;
    }
}
}
?>

</div>


<?php if ($tip_korisnika === 'Organizator'): ?>
    <button id="toggleEdit" class="toggle-edit"><i class="fas fa-pencil-alt"></i></button>

    <div id="editFestivalForm" class="edit-form">
        <h3>Izmeni festival</h3><br>
        <form action="izmeni_festival.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="festival_id" value="<?php echo $festival['festival_id']; ?>">
            
            
            <input type="text" id="naziv" name="naziv_festivala" value="<?php echo htmlspecialchars($festival['naziv_festivala']); ?>" required>
            <label for="naziv">Naziv:</label><br>
            
            <textarea id="opis" name="opis" required><?php echo htmlspecialchars($festival['opis']); ?></textarea>
            <label for="opis" style="top: -8.5em;">Opis:</label>
            
            <input type="date" id="datum_pocetka" name="datum_pocetka" value="<?php echo $festival['datum_pocetka']; ?>" required>
            <label for="datum_pocetka">Datum početka:</label>
            
            <input type="date" id="datum_zavrsetka" name="datum_zavrsetka" value="<?php echo $festival['datum_zavrsetka']; ?>" required>
            <label for="datum_zavrsetka">Datum završetka:</label>
            
            <input type="number" id="cena" name="cena_ulaznica" value="<?php echo $festival['cena_ulaznica']; ?>" required>
            <label for="cena">Cena ulaznice:</label>
            <div>
                <h3>Izmeni scene</h3><br>
                <?php
                $scene_query->execute(); // Ponovno izvršavanje upita jer smo ga već koristili iznad
                $scene_result = $scene_query->get_result();
                while ($scena = $scene_result->fetch_assoc()): ?>
                    <label for="scena_<?php echo $scena['id']; ?>">Scena:</label>
                    <input type="text" id="scena_<?php echo $scena['id']; ?>" name="scene[<?php echo $scena['id']; ?>]" value="<?php echo htmlspecialchars($scena['naziv']); ?>">
                <?php endwhile; ?>
                <br>
            </div>
            <div>
                <br><h3>Izmeni izvođače</h3>
                <?php
                $scene_query->execute();
                $scene_result = $scene_query->get_result();

                while ($scena = $scene_result->fetch_assoc()):
                    echo "<h4>" . htmlspecialchars($scena['naziv']) . "</h4><br>";

                    $izvodjaci_query = $conn->prepare(
                        "SELECT i.datum_nastupa, i.id, k.ime, i.vreme_nastupa FROM izvodjaci i 
                        JOIN korisnici k ON i.korisnik_id = k.korisnik_id
                        WHERE i.scena_id = ?"
                    );
                    $izvodjaci_query->bind_param("i", $scena['id']);
                    $izvodjaci_query->execute();
                    $izvodjaci_result = $izvodjaci_query->get_result();

                    
                    while ($izvodjac = $izvodjaci_result->fetch_assoc()): ?>
                    
                    <input type="text" id="izvodjac_<?php echo $izvodjac['id']; ?>" name="izvodjaci[<?php echo $izvodjac['id']; ?>]" value="<?php echo htmlspecialchars($izvodjac['ime']); ?>">
                    <label for="izvodjac_<?php echo $izvodjac['id']; ?>">Izvođač:</label>
                    
                    <input type="date" id="datum_<?php echo $izvodjac['id']; ?>" name="datum_nastupa[<?php echo $izvodjac['id']; ?>]" value="<?php echo $izvodjac['datum_nastupa']; ?>">
                    <label for="datum_<?php echo $izvodjac['id']; ?>">Datum nastupa:</label>
                        
                    <input type="time" id="vreme_<?php echo $izvodjac['id']; ?>" name="vreme_nastupa[<?php echo $izvodjac['id']; ?>]" value="<?php echo $izvodjac['vreme_nastupa']; ?>"><br>
                    <label for="vreme_<?php echo $izvodjac['id']; ?>">Vreme nastupa:</label>
                    <?php endwhile;
                endwhile;
                ?>
            </div>
            <div>
                <h3>Dodaj novog izvođača</h3><br>
                
                <input type="text" id="novi_izvodjac" name="novi_izvodjac">
                <label for="novi_izvodjac">Ime izvođača:</label>
                
                <input type="date" id="novi_datum" name="novi_datum">
                <label for="novi_datum">Datum nastupa:</label>
                
                <input type="time" id="novo_vreme" name="novo_vreme">
                <label for="novo_vreme">Vreme nastupa:</label><br><br>
                
                
                <select id="scena_id" name="scena_id">
                    <?php
                    $scene_query->execute();
                    $scene_result = $scene_query->get_result();
                    while ($scena = $scene_result->fetch_assoc()):
                        echo "<option value='{$scena['id']}'>" . htmlspecialchars($scena['naziv']) . "</option>";
                    endwhile;
                    ?>
                </select><br>
                <label for="scena_id">Scena:</label>
                <br>
            </div>
            <button type="submit">Sačuvaj izmene</button>
        </form>
    </div>
<?php endif; ?>


<div class="ocene-i-komentari">
    <?php include 'includes/ocena.php'; ?>
    <?php include 'includes/kom.php'; ?>
</div>

<script>
    function prikaziIzvodjace(id) {
        let div = document.getElementById("izvodjaci_" + id);
        div.style.display = div.style.display === "none" ? "block" : "none";
    }
    document.getElementById('toggleEdit').addEventListener('click', function() {
        var form = document.getElementById('editFestivalForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });
</script>

<style>
    .toggle-edit {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        font-size: 20px;
        cursor: pointer;
    }
    .edit-form {
        display: none;
        max-height: 80vh; 
        overflow-y: auto; 
        position: fixed;
        bottom: 80px;
        right: 20px;
        background: white;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        width: 400px;
    }
    .izvodjaci {
        display: none;
        margin-left: 20px;
    }
    .scena {
        cursor: pointer;
        font-weight: bold;
        color: blue;
        margin: 10px 0;
    }
    .ocene-i-komentari{
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        color: #333;
        background: #f8f9fa;
        border-left: 5px solid #4caf50;
        border-radius: 0px 0px 8px 8px;
        box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.1);
    }
    #naziv-festivala{
        font-size: 2.5rem;
        font-weight: bold;
        text-align: center;
        color: #16292b;
        text-shadow: 0 0 2px #16292b, 0 0 2px #16292b, 0 0 32px #16292b;
        text-transform: uppercase;
    }
    .festival-details{
        font-size: 1.2rem;
        line-height: 1.4;
        text-align: center;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        color: #333;
        background: #f8f9fa;
        border-left: 5px solid #4caf50;
        border-radius: 8px 8px 0px 0px;
        box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.1);

    }
    .description{
        text-align: left;
        width: 560px;
        justify-self: center;
    }

    .rezervacija-forma {
    width: 350px;
    margin: 20px auto;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 10px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    text-align: center;
    }

    .rezervacija-forma h2 {
        font-size: 22px;
        margin-bottom: 15px;
    }

    .rezervacija-forma form {
        margin-top: 10px;
    }

    input[type="number"] {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        font-size: 16px;
        text-align: center;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    button[name="rezervisi"] {
        background-color: #28a745; /* Zelena za rezervaciju */
        color: white;
    }

    button[name="otkazi"] {
        background-color: #dc3545; /* Crvena za otkazivanje */
        color: white;
    }

</style>    
<!-- Dugme za rezervaciju -->

<?php include 'includes/overall/footer.php'; ?> 