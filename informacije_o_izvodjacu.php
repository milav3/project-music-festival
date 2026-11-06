<?php 
include 'core/function.php'; 

if(isset($_SESSION["id"])){
    updateUserStatus($_SESSION['id'], 'active now');
    $id = $_SESSION["id"];
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnik_id = $id"));
    if($user['tip_korisnika'] != 'Izvodjac'){
        $stranica = strtolower($user['tip_korisnika']) . '.php';
        header("Location: $stranica");
    }
} else {
    header("Location: login.php");
}

$id_izvodjaca = $_SESSION["id"];
$izvodjac = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnik_id = $id_izvodjaca"));
$nastupi = mysqli_query($conn, "SELECT * FROM izvodjaci WHERE korisnik_id = $id_izvodjaca");
$broj_nastupa = mysqli_num_rows($nastupi);


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["opis"])) {
    $opis = mysqli_real_escape_string($conn, $_POST["opis"]);
    
    $query = "INSERT INTO informacije_o_izvodjacima (id_izvodjaca, opis) VALUES ('$id_izvodjaca', '$opis') ON DUPLICATE KEY UPDATE opis='$opis'";
    if (mysqli_query($conn, $query)) {
        echo "Podaci su uspešno ažurirani!";
    } else {
        echo "Greška: " . mysqli_error($conn);
    }
    
    exit(); // Završava skriptu da ne bi učitavao ostatak stranice
}


include 'includes/overall/header.php'; 
?>

<div style="position: relative;">
    <div class="profilna-div">
        <img src="Slike/<?php echo $izvodjac['profilna_slika'] ?>" alt="" class="profilna_slika">
    </div>
    <div class="informacije">
        <h3 style="color: white;"><?php echo $izvodjac['ime'] . ' ' . $izvodjac['prezime']; ?></h3>
        <br>
        <p style="color: white;">Vremenski raspored nastupa:</p>
        
        <?php
            while ($nastup = mysqli_fetch_assoc($nastupi)) {
                $id_scene = $nastup['scena_id'];
                $scena = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM scene WHERE id = $id_scene"));
                echo "<p style='margin-left: 40px; color:white;'>" . "• " . htmlspecialchars($scena['naziv']) . ' ' . htmlspecialchars($nastup['datum_nastupa']) . ' ' . htmlspecialchars($nastup['vreme_nastupa']) . "</p>";
            }
            $info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM informacije_o_izvodjacima WHERE id_izvodjaca = $id_izvodjaca"));
        ?>
        
        <p id="opis" style="width: 80%; text-align: justify;border-radius:10px; border: 1px solid white; padding: 10px; color: white;" contenteditable="true"> 
            <?php 
            if(isset($info['opis']) && $info['opis']!=''){
                echo($info['opis']); 
            }else{
                echo('Nema dodatnih informacija');
            }
            ?> 
        </p>
        
        <button onclick="posaljiPodatke()" class="btn" style="width:30%; position: relative; left: 25%;">Sačuvaj izmene</button>  
        <p id="poruka" style="color: green; display: none;">Podaci su uspešno ažurirani!</p>
    </div>
</div>

<?php
include 'includes/overall/footer.php'; 
?>

<style>
    .profilna-div{
        display: flex;
        position: absolute;
        top: 0px;
    }
    .profilna_slika{
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
    }
    .informacije{
        position: relative;
        left: 190px;
        font-size: 15px;
    }
</style>

<script>
    function posaljiPodatke() {
    let element = document.getElementById('opis');
    let poruka = document.getElementById('poruka'); // Preuzimamo <p> za poruku
    if (element) {
        let opis = element.innerText.trim();  // Uzima tekst iz <p> taga i briše prazne prostore

        fetch('', {  // Prazan string znači slanje na istu stranicu
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'  // Pravi format za PHP
            },
            body: 'opis=' + encodeURIComponent(opis)  // Pravilno formatiran body
        })
        .then(response => response.text())  // Dobijanje odgovora od servera
        .then(data => {
            console.log(data); // Možeš videti odgovor u konzoli

            // Prikazujemo poruku
            poruka.style.display = "block";

            // Sakrijemo poruku nakon 3 sekunde
            setTimeout(() => {
                poruka.style.display = "none";
            }, 3000);
        })
        .catch(error => console.error("Greška:", error));
        
    } else {
        console.log("Element sa id 'opis' nije pronađen.");
    }
}

</script>
