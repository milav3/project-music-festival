<?php
include_once 'core/function.php';
//SCENE RADE IZVODKACI NE
// Učitavanje žanrova iz baze
$sql = "SELECT zanr_id, naziv_zanra FROM zanrovi";
$result = $conn->query($sql);
$zanrovi = [];
while ($row = $result->fetch_assoc()) {
    $zanrovi[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $naziv = $_POST['naziv'];
    $opis = $_POST['opis'];
    $datum_pocetka = $_POST['datum_pocetka'];
    $datum_zavrsetka = $_POST['datum_zavrsetka'];
    $lokacija = $_POST['lokacija'];
    $cena_ulaznica = $_POST['cena_ulaznica'];
    $broj_ulaznica = $_POST['broj_ulaznica'];
    $zanr_id = $_POST['zanr_id'];
    $stream = $_POST['stream'];
    $link_lokacije = $_POST['link_lokacije'];

    $slika = "";
    if (isset($_FILES['slika']) && $_FILES['slika']['error'] == 0) {
        $slika = 'Slike/' . basename($_FILES['slika']['name']);
        move_uploaded_file($_FILES['slika']['tmp_name'], $slika);
    }

    $sql = "INSERT INTO festivali (naziv_festivala, opis, datum_pocetka, datum_zavrsetka, lokacija, cena_ulaznica, broj_preostalih_ulaznica, slika, zanr_id, stream, link_lokacije) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssdissss", $naziv, $opis, $datum_pocetka, $datum_zavrsetka, $lokacija, $cena_ulaznica, $broj_ulaznica, $slika, $zanr_id, $stream, $link_lokacije);

    if ($stmt->execute()) {
        $festival_id = $stmt->insert_id;
        echo "<p>Festival uspešno dodat!</p>";

        if (!empty($_POST['scena'])) {
            foreach ($_POST['scena'] as $scena_index => $scena_naziv) {
                $sql = "INSERT INTO scene (naziv, festival_id) VALUES (?, ?)";
                $stmt_scene = $conn->prepare($sql);
                $stmt_scene->bind_param("si", $scena_naziv, $festival_id);
                $stmt_scene->execute();
                $scena_id = $stmt_scene->insert_id;
                $stmt_scene->close();
            
                if (!empty($_POST['nastup_izvodjac'][$scena_index])) {
                    $korisnik_query = $conn->prepare("SELECT korisnik_id FROM korisnici WHERE ime = ?");
                
                    foreach ($_POST['nastup_izvodjac'][$scena_index] as $izvodjac_index => $izvodjac_ime) {
                        $korisnik_query->bind_param("s", $izvodjac_ime);
                        $korisnik_query->execute();
                        $result = $korisnik_query->get_result();
                        
                        if ($row = $result->fetch_assoc()) {
                            $korisnik_id = $row['korisnik_id'];
                
                            $nastup_datum = $_POST['nastup_datum'][$scena_index][$izvodjac_index];
                            $nastup_vreme = $_POST['nastup_vreme'][$scena_index][$izvodjac_index];
                
                            $insert_query = $conn->prepare("INSERT INTO izvodjaci (korisnik_id, festival_id, scena_id, datum_nastupa, vreme_nastupa) VALUES (?, ?, ?, ?, ?)");
                            $insert_query->bind_param("iiiss", $korisnik_id, $festival_id, $scena_id, $nastup_datum, $nastup_vreme);
                            if ($insert_query->execute()) {
                                echo "<p>Izvođač '$izvodjac_ime' uspešno dodat.</p>";
                            } else {
                                echo "<p>Greška pri dodavanju izvođača: " . $insert_query->error . "</p>";
                            }
                            $insert_query->close();
                        } else {
                            echo "<p>Greška: Izvođač '$izvodjac_ime' nije pronađen.</p>";
                        }
                    }
                    $korisnik_query->close();
                }
                
                
            
                
            
            }
        }
    } else {
        echo "<p>Greška pri dodavanju festivala: " . $stmt->error . "</p>";
    }
    $stmt->close();
}
?>

<style>
    /* CSS ostaje isti */
</style>

<button id="toggle-button" onclick="toggleFestivalView()">+</button>

<div id="festival-form">
    <h2>Dodaj novi festival</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="text" name="naziv" required>
        <label for="naziv">Naziv festivala</label>

        <textarea name="opis" required></textarea>
        <label for="opis">Opis</label>

        <input type="date" name="datum_pocetka" required>
        <label for="datum_pocetka">Datum početka</label>

        <input type="date" name="datum_zavrsetka" required>
        <label for="datum_zavrsetka">Datum završetka</label>

        <input type="text" name="lokacija" required>
        <label for="lokacija">Lokacija</label>

        <input type="number" name="cena_ulaznica" required>
        <label for="cena_ulaznica">Cena ulaznica</label>

        <input type="number" name="broj_ulaznica" required>
        <label for="broj_ulaznica">Broj preostalih ulaznica</label>

        <select name="zanr_id" required>
            <option value="">Izaberite žanr</option>
            <?php foreach ($zanrovi as $zanr) { ?>
                <option value="<?php echo $zanr['zanr_id']; ?>"><?php echo $zanr['naziv_zanra']; ?></option>
            <?php } ?>
        </select>
        <label for="zanr_id">Žanr</label>

        <input type="text" name="stream">
        <label for="stream">Stream link</label>

        <input type="text" name="link_lokacije">
        <label for="link_lokacije">Link lokacije</label>

        <div id="scene-container"></div>
        <button type="button" onclick="dodajScenu()">Dodaj scenu</button>

        <input type="file" name="slika" accept="image/*" required>
        <label for="slika">Slika festivala</label>

        <button type="submit">Dodaj Festival</button>
    </form>
</div>

<script>
    let sceneCount = 0; // Brojač scena

function dodajScenu() {
    let container = document.getElementById("scene-container");
    let scenaDiv = document.createElement("div");
    scenaDiv.setAttribute("data-scene-index", sceneCount); // Dodaj indeks sceni
    scenaDiv.innerHTML = `
        <input type="text" name="scena[]" placeholder="Naziv scene" required>
        <button type="button" onclick="dodajNastup(this, ${sceneCount})">Dodaj nastup</button>
        <div class="nastupi-container"></div>
    `;
    container.appendChild(scenaDiv);
    sceneCount++; // Povećaj broj scena
}

function dodajNastup(button, sceneIndex) {
    let nastupiContainer = button.nextElementSibling;
    let nastupDiv = document.createElement("div");
    nastupDiv.innerHTML = `
        <input type="text" name="nastup_izvodjac[${sceneIndex}][]" placeholder="Ime izvođača" required>
        <input type="date" name="nastup_datum[${sceneIndex}][]" required>
        <input type="time" name="nastup_vreme[${sceneIndex}][]" required>
    `;
    nastupiContainer.appendChild(nastupDiv);
}

</script>

<style>
    body {
        font-family: Arial, sans-serif;
    }
    #toggle-button {
        font-size: 24px;
        cursor: pointer;
        background-color: #16292b;;
        color: white;
        border: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    }
    #festival-form {
        display: none;
        margin-top: 20px;
        max-width: 500px;
        margin: auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 10px;
        background-color: #f9f9f9;
    }
    form label {
        color: #2b3b4a;
        position: relative;
        top: -2.5em;
        left: -3.2em;
        cursor: auto;
        transition: 0.3s ease all;    
        background: white;
    }
    form input, form textarea, form select {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
        height: 35px;
        background: white;
    }
    form select {
        cursor: pointer;
    }
    form input::placeholder {
        color: #aaa;
        font-style: italic;
    }
    form button {
        margin-top: 15px;
        background-color: #28a745;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
    }
</style>


<?php  $conn->close();
 ?>
