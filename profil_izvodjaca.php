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
$id_izvodjaca = intval($_GET['id']);
$izvodjac = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnik_id = $id_izvodjaca"));
$nastupi = mysqli_query($conn, "SELECT * FROM izvodjaci WHERE korisnik_id = $id_izvodjaca");
$broj_nastupa = mysqli_num_rows($nastupi);
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
<?php
include 'includes/overall/header.php';
?>

<div style="position: relative;">
    <div class="profilna-div">
        <img src="Slike/<?php echo $izvodjac['profilna_slika'] ?>" alt="" class="profilna_slika">
    </div>
    <div class="informacije">
        <h1 style="color: white; margin-bottom: 0px;"><?php echo $izvodjac['ime'] . ' ' . $izvodjac['prezime']; ?></h1>
        <br>
        <p style="color: white;">Vremenski raspored nastupa:</p>
        
        <?php
            while ($nastup = mysqli_fetch_assoc($nastupi)) {
                $id_scene = $nastup['scena_id'];
                $festival_id = $nastup['festival_id'];
                $scena = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM scene WHERE id = $id_scene"));
                $festival = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM festivali WHERE festival_id = $festival_id"));
                //echo "<p style='margin-left: 40px; color: white;'>" . "• " . htmlspecialchars($scena['naziv']) . " " . htmlspecialchars($nastup['datum_nastupa']) . " " . htmlspecialchars($nastup['vreme_nastupa']) . "</p>";
                echo "<h3 style='margin-left: 40px; color: white;'>" . "• " . htmlspecialchars($festival['naziv_festivala']) . " - " . htmlspecialchars($scena['naziv']) . "</h3><p style='margin-left: 60px; color: white; margin-bottom: 0px;'>" . "Datum: " . htmlspecialchars($nastup['datum_nastupa']) . "</p><p style='margin-left: 60px; color: white;'> Vreme:" . htmlspecialchars($nastup['vreme_nastupa'])  . "</p>";
            }
            $info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM informacije_o_izvodjacima WHERE id_izvodjaca = $id_izvodjaca"));
            if(!empty($info)){
                echo '<br>';
                echo '<p style="color:white; width: 80%; text-align: justify;">' . htmlspecialchars($info['opis']) . '</p>';    
            }
            else{
                echo '<br>';
                echo '<p style="color: white;">' .  'Nema informacija o ovom izvodjacu' . '</p>';
            }
        ?>
    </div>
</div>

<?php include 'includes/overall/footer.php'; ?>