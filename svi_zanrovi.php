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

// Dobavljanje žanrova
$zanrovi_query = mysqli_query($conn, "SELECT * FROM zanrovi");
$zanrovi = [];
while ($row = mysqli_fetch_assoc($zanrovi_query)) {
    $zanrovi[] = $row;
}

include 'includes/overall/header.php';
?>

<style>
    .zanrovi-container {
        text-align: center;
        margin: 30px auto;
        color: white;
    }

    .zanrovi-list {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }

    .zanr {
        background: #15282bbf;
        padding: 15px 25px;
        margin: 10px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 18px;
        transition: 0.3s;
        color: white; /* Dodajte boju teksta u normalnom stanju */
        width: 120px;
        height: 40px;
    }

    .zanr:hover {
        transform: scale(1.2);
        transition: all 0.9s ease;
    }


    #festivali-container {
        margin-top: 20px;
        display: flex;
        flex-wrap: wrap;
        justify-content: left;
        
    }

    .festival {
        background-color: #fff;
        width: 300px;
        border-radius: 12px;
        padding: 15px;
        margin: 15px;
        text-align: center;
        border: 1px solid #2b3b4a;
        box-shadow: 0 20px 35px rgba(0, 0, 1, 0.9);
    }
    .festival:hover{
        transform: scale(1.2);
        transition: all 0.9s ease;
    }
    .festival img {
        width: 100%;
        height: 200px;
        border-radius: 12px;
        object-fit: cover;
    }

    .festival h3 {
        margin: 10px 0;
        font-size: 20px;
    }

    .festival p {
        font-size: 14px;
        opacity: 0.8;
    }
</style>
<aside class="strana">
        <a href="posetilac.php" class="lista-strana" style="top: 180px;">FESTIVALI</a><br>
        <a href="svi_izvodjaci.php" class="lista-strana" style="top: 250px;">IZVODJACI</a><br>
        <a href="svi_zanrovi.php" class="lista-strana" style="top: 320px;">ZANROVI</a><br>
        <a href="sve_scene.php" class="lista-strana" style="top: 390px;">SCENE</a>
</aside>
<div style="width: 90%; position:relative; left: 115px; top: -90px;">
    <h1 class="zanrovi-container" >Odaberi žanr</h1>

    <div class="zanrovi-list">
        <?php foreach ($zanrovi as $zanr): ?>
            <div class="zanr" onclick="prikaziFestivale(<?php echo $zanr['zanr_id']; ?>)">
                <?php echo htmlspecialchars($zanr['naziv_zanra']); ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="festivali-container">
        <!-- Ovde će se dinamički prikazivati festivali -->
    </div>
</div>
<script>
    function prikaziFestivale(zanr_id) {
        let xhr = new XMLHttpRequest();
        xhr.open("GET", "festivali_po_zanru.php?zanr_id=" + zanr_id, true);
        xhr.onload = function() {
            if (this.status == 200) {
                document.getElementById('festivali-container').innerHTML = this.responseText;
            }
        };
        xhr.send();
    }
</script>

<?php include 'includes/overall/footer.php'; ?>
