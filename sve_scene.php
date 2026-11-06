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

// Povlačimo sve scene zajedno sa nazivima festivala
$scene = mysqli_query($conn, "SELECT scene.id, scene.naziv AS scena_naziv, festivali.naziv_festivala AS naziv_festivala
                              FROM scene 
                              JOIN festivali ON scene.festival_id = festivali.festival_id");

$broj_scena_sql = "SELECT COUNT(*) AS ukupno FROM scene";
$broj_scena_result = $conn->query($broj_scena_sql);
$broj_scena = $broj_scena_result->fetch_assoc()['ukupno'];

if ($scene->num_rows > 0) {
    echo "<script>";
    echo "var scene = " . json_encode(mysqli_fetch_all($scene, MYSQLI_ASSOC)) . ";";
    echo "var brojScena = " . $broj_scena . ";";
    echo "</script>";
} else {
    echo "<script>var scene = [];</script>";
    echo "<script>var brojScena = 0;</script>";
}

include 'includes/overall/header.php';
?>

<style>
    .scena {
    width: 200px; /* Postavite fiksnu širinu kvadrata */
    height: 200px; /* Postavite fiksnu visinu kvadrata */
    text-align: center; /* Poravnavanje teksta */
    margin: 15px;
    background-color: #fff;
    border-radius: 20px; /* Zaobljene ivice */
    border: 1px solid #15282bbf;
    box-shadow: 0 20px 35px rgba(0, 0, 1, 0.9);
    background: #15282bbf;
    display: flex;
    flex-direction: column; /* Vertikalno poravnavanje elemenata */
    justify-content: center; /* Centriranje elemenata unutar kvadrata */
    align-items: center; /* Horizontally centering text */
    padding: 15px; /* Prostor unutar kvadrata */
}
.scena:hover {
    transform: scale(1.2);
    transition: all 0.9s ease;
    /*color: black;  Boja teksta pri hoveru */
}
#scene {
    display: flex;
    flex-wrap: wrap; /* Omogućava prelamanje kvadrata na sledeći red */
    justify-content: left; /* Centriranje kvadrata */
    gap: 15px; /* Razmak između kvadrata */
    margin-left: 120px;
}

.naziv-scene {
    color: white;
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px; /* Razmak između naziva scene i drugih elemenata */
}


</style>
<aside class="strana">
        <a href="posetilac.php" class="lista-strana" style="top: 180px;">FESTIVALI</a><br>
        <a href="svi_izvodjaci.php" class="lista-strana" style="top: 250px;">IZVODJACI</a><br>
        <a href="svi_zanrovi.php" class="lista-strana" style="top: 320px;">ZANROVI</a><br>
        <a href="sve_scene.php" class="lista-strana" style="top: 390px;">SCENE</a>
</aside>

<div id="scene"></div>

<script>
    const container = document.getElementById('scene');

    function kreirajDivove(brojScena) {
        for (let i = 0; i < brojScena; i++) {
            const scena = scene[i];
            
            const div = document.createElement('div');
            div.className = 'scena';
            div.innerHTML = `
                <a href="detalji_scene.php?id=${scena.id}">
                    <h2 class="naziv-scene">${scena.scena_naziv} - ${scena.naziv_festivala}</h2>
                </a>
            `;
            
            container.appendChild(div);
        }
    }

    kreirajDivove(scene.length);


</script>

<?php include 'includes/overall/footer.php'; ?>
