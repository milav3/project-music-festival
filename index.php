<?php
include 'core/function.php';
if(isset($_SESSION["id"])){
    $id = $_SESSION["id"];
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnik_id = $id"));
    $stranica = strtolower($user['tip_korisnika']) . '.php';
    header("Location: $stranica");
}
else{
 

$festivali = mysqli_query($conn, "SELECT * FROM festivali");

$broj_festivala_sql = "SELECT COUNT(*) AS ukupno FROM festivali";
$broj_festivala_result = $conn->query($broj_festivala_sql);
$broj_festivala = $broj_festivala_result->fetch_assoc()['ukupno'];

if ($festivali->num_rows > 0) {
    echo "<script>";
    echo "var festivali = " . json_encode(mysqli_fetch_all($festivali, MYSQLI_ASSOC)) . ";";
    echo "var brojFestivala = " . $broj_festivala . ";";
    echo "</script>";
} else {
    echo "<script>var festivali = [];</script>";
    echo "<script>var brojFestivala = 0;</script>";
}



include 'includes/overall/header.php'; ?>

<style>
        #poruka {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(150, 0, 7, 0.9);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            display: none;
            animation: fadein 0.5s;
            z-index: 9999; 
        }

        @keyframes fadein {
            from { opacity: 0; transform: translateX(-50%) translateY(20px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }
</style>

<div id="poruka">Potrebno je ulogovati se da biste videli detaljan prikaz festivala !!!</div>

<script>
    function prikaziPoruku() {
        let poruka = document.getElementById("poruka");
        poruka.style.display = "block";
        setTimeout(() => {
            poruka.style.display = "none";
        }, 5000); // Nestaje posle 3 sekunde
    }
</script>

<div class="home-page" style="display: block;">

<br>
    <div id="pregled-festivala">
    <script>
    const container = document.getElementById('pregled-festivala');
    function formatDate(dateString) {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            const date = new Date(dateString);
            return date.toLocaleDateString('sr-Latn-RS', options);
    }
    function kreirajDivove(brojFestivala) {
        for (let i = 0; i < brojFestivala; i++) {
            const festival = festivali[i];
            const startDateFormatted = formatDate(festival.datum_pocetka);
            const endDateFormatted = formatDate(festival.datum_zavrsetka);
            const div = document.createElement('div');
            div.className = 'tri_diva';
            div.innerHTML = `
                <div class="unutrasnji" onclick="prikaziPoruku()">
                    <div class="images">
                        <img src="${festival.slika}" class="festival-images" alt="">
                    </div>    
                    <div>
                        <br>
                        <h2> ${festival.naziv_festivala} </h2>
                        
                        <br><br>
                        ${startDateFormatted} - ${endDateFormatted}
                        <br>
                        <br>
                            <i class="fas fa-location-dot"></i>
                            ${festival.lokacija}
                        <br><br>
                        Ulaz: ${festival.cena_ulaznica} RSD
                    </div>
                </div>`;
            container.appendChild(div);
        }
    }

    kreirajDivove(brojFestivala);

    </script>
    </div>
</div>
<?php 
}
include 'includes/overall/footer.php'; ?>
