<?php 
include 'core/function.php'; 
if(isset($_SESSION["id"])){
    updateUserStatus($_SESSION['id'], 'active now');
    $id = $_SESSION["id"];
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnik_id = $id"));
    if($user['tip_korisnika']!='Administrator'){
        $stranica = strtolower($user['tip_korisnika']) . '.php';
        header("Location: $stranica");
    }
}
else{
    header("Location: login.php");
}


//deo pre inkluda je dodat
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
<!--Ovde treba da se radi ***ovaj ceo div je dodat-->
<div class="home-page">

    <div style="width: 100%;">        
        <div style="text-align: center;" id='search-div'>
            <input type="search" name="search" id="live_search" placeholder="Pretrazi..."><br>
            <i class="fas fa-search" id=iSearch></i>
        </div>

        <div id="searchresult" style="width: 85%; position: relative; left: 15%;"></div>

        <div id="pregled-festivala" style="width: 85%; position: relative; left: 15%;">
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
                            <div class="unutrasnji">
                                <a href="fest.php?id=${festival.festival_id}">
                                    <div class="images">
                                        <img src="${festival.slika}" class="festival-images" alt="">
                                    </div>    
                                    <div style="color: #16292b;">
                                        <br>
                                        <h2 style="color: #16292b;">${festival.naziv_festivala}</h2>
                                        <br><br>
                                        ${startDateFormatted} - ${endDateFormatted}
                                        <br><br>
                                        <i class="fas fa-location-dot"></i>
                                        ${festival.lokacija}
                                        <br><br>
                                        Ulaz: ${festival.cena_ulaznica} RSD
                                    </div>
                                </a>
                            </div>`;
                        
                        container.appendChild(div);
                    }
                }

                kreirajDivove(festivali.length);
                document.getElementById('addFestivalForm').addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData(e.target);

                    const response = await fetch('dodaj_festival.php', {
                        method: 'POST',
                        body: formData,
                    });

                    const newFestival = await response.json();

                    if (!newFestival.error) {
                        // Add the new festival to the festivali array
                        festivali.push(newFestival);
                        brojFestivala++;

                        // Create new div for the festival
                        const div = document.createElement('div');
                        div.className = 'tri_diva';
                        div.innerHTML = `
                            <div class="unutrasnji">
                                <a href="fest.php?id=${newFestival.festival_id}">
                                    <div class="images">
                                        <img src="${newFestival.slika}" class="festival-images" alt="">
                                    </div>    
                                    <div>
                                        <br>
                                        <h2> ${newFestival.naziv_festivala} </h2>
                                        ${newFestival.opis}
                                        <br><br>
                                        Od ${newFestival.datum_pocetka} do ${newFestival.datum_zavrsetka}
                                        <br>
                                        ${newFestival.lokacija}
                                        <br>
                                        Ulaz: ${newFestival.cena_ulaznica} RSD
                                    </div>
                                </a>
                            </div>
                        `;
                        container.appendChild(div);
                        alert('Festival uspešno dodat!');
                    } else {
                        alert('Greška pri dodavanju festivala.');
                    }
                });
            </script>
        </div>
        <?php include 'dodaj_festival.php'; ?>
    </div>
</div> 

<script>
    $(document).ready(function(){
        $("#live_search").keyup(function(){
            var input = $(this).val();
            
            if(input != ""){
                $.ajax({
                    url: "livesearch.php",
                    method: "POST",
                    data: {input:input},

                    success: function(data){
                        $("#searchresult").html(data);
                        $("#searchresult").css("display","block");
                        $("#pregled-festivala").css("display","none");
                    
                    }
                });
            } else{
                $("#searchresult").css("display","none");
                $("#pregled-festivala").css("display","block");
            }
        });
    });

    
    function kreirajDiv(festival) {
    const container = document.getElementById('searchresult');

    let startDateFormatted = new Date(festival.datum_pocetka).toLocaleDateString('sr-Latn-RS', { year: 'numeric', month: 'short', day: 'numeric' });
    let endDateFormatted = new Date(festival.datum_zavrsetka).toLocaleDateString('sr-Latn-RS', { year: 'numeric', month: 'short', day: 'numeric' });

    const div = document.createElement('div');
    div.className = 'tri_diva';
    div.innerHTML = `
        <div class="unutrasnji">
            <a href="fest.php?id=${festival.festival_id}">
                <div class="images">
                    <img src="${festival.slika}" class="festival-images" alt="">
                </div>    
                <div>
                    <h2>${festival.naziv_festivala}</h2>
                    <p>${startDateFormatted} - ${endDateFormatted}</p>
                    <p><i class="fas fa-location-dot"></i> ${festival.lokacija}</p>
                    <p>Ulaz: ${festival.cena_ulaznica} RSD</p>
                </div>
            </a>
        </div>
    `;

    container.appendChild(div);
}
</script>
   <!-- <a href="includes/kreiraj_anketu.php" class="btn-anketa">+ Kreiraj anketu</a> -->


<style>
    .btn-anketa {
        position: fixed;
        bottom: 20px;
        left: 15px;
        width: 9%;
        background-color: #28a745;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    }
    .btn-anketa:hover {
        background-color: #218838;
    }
</style>
   <!-- <a href="includes/rezultati_anketa.php" class="btn-rezultati">📊 Pregledaj rezultate anketa</a>-->

<style>
    .btn-rezultati {
        position: fixed;
        bottom: 80px;
        left: 15px;
        width: 9%;
        background-color: #ffc107;
        color: black;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    }
    .btn-rezultati:hover {
        background-color: #e0a800;
    }
    .stat{
        position: fixed;
        bottom: 160px;
        left: 15px;
        width: 9%;
        background-color:#007BFF;
        color: black;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    }
</style>


<script>
    function toggleFestivalView() {
    var pregledFestivala = document.getElementById("pregled-festivala");
    var pretraga = document.getElementById("search-div");
    var festivalForm = document.getElementById("festival-form");
    var toggleButton = document.getElementById("toggle-button");

    if (pregledFestivala.style.display === "none") {
        pregledFestivala.style.display = "block";
        pretraga.style.display = "block";
        festivalForm.style.display = "none";
        toggleButton.innerHTML = "+";
    } else {
        pregledFestivala.style.display = "none";
        pretraga.style.display = "none";
        festivalForm.style.display = "block";
        toggleButton.innerHTML  = "&times";
    }
}

</script>
<aside class="strana" style="top:20px">
        <a href="posetilac.php" class="lista-strana" style="top: 180px;">FESTIVALI</a><br>
        <a href="svi_izvodjaci.php" class="lista-strana" style="top: 250px;">IZVODJACI</a><br>
        <a href="svi_zanrovi.php" class="lista-strana" style="top: 320px;">ZANROVI</a><br>
        <a href="sve_scene.php" class="lista-strana" style="top: 390px;">SCENE</a>
</aside>

<div id="statistika-container" style="display: none;">
    <div id="statistikaPodaci"></div>
</div>
<!-- <button id="toggle-statistika" class="stat" style="color:white;">📊 Statistika</button> -->

<div id="statistika-container" style="display: none;">
    <h2>Statistika</h2>
    <div id="statistikaPodaci"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
 let dugmeStatistika = document.getElementById("toggle-statistika");
if (dugmeStatistika) {
    dugmeStatistika.addEventListener("click", function() {
        console.log("Kliknuto na dugme za statistiku!");
    });
} else {
    console.warn("Element sa ID 'toggle-statistika' nije pronađen!");
}
   
document.getElementById("toggle-statistika").addEventListener("click", function() {
    var statistikaContainer = document.getElementById("statistika-container");
    var pregledFestivala = document.getElementById("pregled-festivala");
    var pretraga = document.getElementById("search-div");

    if (statistikaContainer.style.display === "none" || statistikaContainer.style.display === "") {
        $.ajax({
            url: "zabelezi_statistiku.php",
            type: "GET",
            success: function(data) {
                document.getElementById("statistikaPodaci").innerHTML = data;
                statistikaContainer.style.display = "block";
                pregledFestivala.style.display = "none";
                pretraga.style.display = "none";
            },
            error: function() {
                document.getElementById("statistikaPodaci").innerHTML = "<p>Došlo je do greške pri učitavanju podataka.</p>";
                statistikaContainer.style.display = "block";
                pregledFestivala.style.display = "none";
                pretraga.style.display = "none";
            }
        });
    } else {
        statistikaContainer.style.display = "none";
        pregledFestivala.style.display = "block";
        pretraga.style.display = "block";
    }
});

</script>


<?php include 'includes/overall/footer.php'; ?>
