<?php
include 'core/function.php';

if (!isset($_GET['id'])) {
    header("Location: sve_scene.php");
    exit();
}

$scena_id = intval($_GET['id']);

// Dobavljanje naziva scene
$scena_query = mysqli_query($conn, "SELECT naziv FROM scene WHERE id = $scena_id");
$scena = mysqli_fetch_assoc($scena_query);
if (!$scena) {
    echo "Scena nije pronađena.";
    exit();
}

// Dobavljanje izvođača i vremena nastupa
$izvodjaci_query = mysqli_query($conn, 
    "SELECT k.korisnik_id, k.ime, k.prezime, k.profilna_slika, i.vreme_nastupa, i.datum_nastupa
    FROM izvodjaci i
    JOIN korisnici k ON i.korisnik_id = k.korisnik_id
    WHERE i.scena_id = $scena_id
    ORDER BY i.datum_nastupa, i.vreme_nastupa"
);

include 'includes/overall/header.php';
?>

<style>
    body {
        background-color: #1a1a1a;
        font-family: Arial, sans-serif;
    }

    .scena-naslov {
        text-align: center;
        color: white;
        font-size: 28px;
        margin-top: 20px;
        text-transform: uppercase;
        font-weight: bold;
    }

    #izvodjaci {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 20px;
    }

    .izvodjac {
        width: 280px;
        /*background-color: #15282b;*/
        background: #15282bbf;
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

    .vreme-nastupa {
        color: #ffcc00;
        font-size: 16px;
        margin-top: 5px;
    }

    .izvodjac a {
        text-decoration: none;
    }
</style>

<h2 class="scena-naslov">Scena: <?php echo htmlspecialchars($scena['naziv']); ?></h2>

<div id="izvodjaci">
    <?php while ($izvodjac = mysqli_fetch_assoc($izvodjaci_query)): ?>
        <div class="izvodjac">
            <a href="profil_izvodjaca.php?id=<?php echo $izvodjac['korisnik_id']; ?>">
                <img src="Slike/<?php echo htmlspecialchars($izvodjac['profilna_slika']); ?>" class="profile-images" alt="">
                <h2 class="ime-prezime-izvodjaca">
                    <?php echo htmlspecialchars($izvodjac['ime'] . " " . $izvodjac['prezime']); ?>
                </h2>
                <p class="vreme-nastupa">Datum i vreme nastupa: <br><?php echo $izvodjac['datum_nastupa']; ?><br> <?php echo $izvodjac['vreme_nastupa']; ?></p>
            </a>
        </div>
    <?php endwhile; ?>
</div>

<?php include 'includes/overall/footer.php'; ?>
