<?php 
include 'core/function.php'; 
if(isset($_SESSION["id"])){
    updateUserStatus($_SESSION['id'], 'active now');
    $id = $_SESSION["id"];
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnik_id = $id"));
}
else{
    header("Location: login.php");
}


$izvodjaci = mysqli_query($conn, "SELECT * FROM korisnici WHERE tip_korisnika = 'Izvodjac'");
$broj_izvodjaca_sql = "SELECT COUNT(*) AS ukupno FROM korisnici WHERE tip_korisnika = 'Izvodjac'";
$broj_izvodjaca_result = $conn->query($broj_izvodjaca_sql);
$broj_izvodjaca = $broj_izvodjaca_result->fetch_assoc()['ukupno'];

if ($izvodjaci->num_rows > 0) {
    echo "<script>";
    echo "var izvodjaci = " . json_encode(mysqli_fetch_all($izvodjaci, MYSQLI_ASSOC)) . ";";
    echo "var brojIzvodjaca = " . $broj_izvodjaca . ";";
    echo "</script>";
} else {
    echo "<script>var izvodjaci = [];</script>";
    echo "<script>var brojIzvodjaca = 0;</script>";
}

include 'includes/overall/header.php';
?>

<style>

    #izvodjaci {
        display: flex;
        flex-wrap: wrap;
        justify-content: left;
        margin-top: -60px;
        margin-left: 160px;
    }

    .izvodjac {
        width: 280px;
        background-color: #15282bbf;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.9);
        margin: 15px;
        padding: 15px;
        text-align: center;
        position: relative;
        transition: transform 0.3s ease-in-out;
    }

    .izvodjac:hover {
        transform: scale(1.05);
    }

    .profile-images {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid white;
        margin-bottom: 10px;
    }

    .ime-prezime-izvodjaca {
        color: white;
        font-size: 18px;
        font-weight: bold;
        margin-top: 5px;
    }

    .izvodjac a {
        text-decoration: none;
    }
</style>

<aside class="strana">
        <a href="posetilac.php" class="lista-strana" style="top: 180px;">FESTIVALI</a><br>
        <a href="svi_izvodjaci.php" class="lista-strana" style="top: 250px;">IZVODJACI</a><br>
        <a href="svi_zanrovi.php" class="lista-strana" style="top: 320px;">ZANROVI</a><br>
        <a href="sve_scene.php" class="lista-strana" style="top: 390px;">SCENE</a>
</aside>


<div id="izvodjaci"></div>
<script>
        const container = document.getElementById('izvodjaci');

        function kreirajDivove(brojIzvodjaca) {
            for (let i = 0; i < brojIzvodjaca; i++) {
                const izvodjac = izvodjaci[i];
                
                const div = document.createElement('div');
                div.className = 'izvodjac';
                div.innerHTML = `
                    <a href="profil_izvodjaca.php?id=${izvodjac.korisnik_id}" onclick="zabeleziPosetuIzvodjacu(${izvodjac.korisnik_id})">
                        <div class="images">
                            <img src="Slike/${izvodjac.profilna_slika}" class="profile-images" alt="">
                        </div>    
                        <div style="height: 65px; position: absolute; justify-self: anchor-center; top: 96px;">
                            <br>
                            <h2 class="ime-prezime-izvodjaca">${izvodjac.ime} ${izvodjac.prezime}</h2>
                        </div>
                    </a>`;
                container.appendChild(div);
            }
        }

        kreirajDivove(izvodjaci.length);
        function zabeleziPosetuIzvodjacu(izvodjac_id, festival_id) {
    let cookieName = "izvodjac_" + izvodjac_id;
    
    // Pošalji AJAX zahtev ka PHP fajlu
    fetch('zabelezi_statistiku.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `izvodjac_id=${izvodjac_id}&festival_id=${festival_id}`
    })
    .then(response => response.json())
    .then(data => console.log(data.message))
    .catch(error => console.error('Greška:', error));
}

</script>

<?php include 'includes/overall/footer.php'; ?>