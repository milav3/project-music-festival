<?php 
include 'core/function.php'; 

if(isset($_SESSION["id"])) {
    updateUserStatus($_SESSION['id'], 'active now');
    $id = $_SESSION["id"];
    $result = mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnik_id = $id");
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Check if the user is blocked
        if ($user['blokiran'] == 1) {
            session_destroy(); // Destroy the session for a blocked user
            die("Vaš nalog je blokiran. Obratite se administratoru.");
        }

        // Check user's type to allow access
        if ($user['tip_korisnika'] != 'Posetilac') {
            $stranica = strtolower($user['tip_korisnika']) . '.php';
            header("Location: $stranica");
            exit; // Stop further execution
        }
    } else {
        header("Location: login.php");
        exit; // Ensure further execution is stopped
    }
} else {
    header("Location: login.php");
    exit; // Ensure further execution is stopped
}

// Fetch festival data
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

include 'includes/overall/header.php'; 
?>


<div class="home-page">

    <aside class="strana">
        <a href="posetilac.php" class="lista-strana" style="top: 180px;">FESTIVALI</a><br>
        <a href="svi_izvodjaci.php" class="lista-strana" style="top: 250px;">IZVODJACI</a><br>
        <a href="svi_zanrovi.php" class="lista-strana" style="top: 320px;">ZANROVI</a><br>
        <a href="sve_scene.php" class="lista-strana" style="top: 390px;">SCENE</a>
    </aside>

    
<div style="width: 100%;">        
    <div style="text-align: center;">
        <input type="search" name="search" id="live_search" placeholder="Pretrazi..."><br>
        <i class="fas fa-search" id=iSearch></i>
    </div>

    <div id="searchresult"></div>

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
                    <div class="unutrasnji">
                        <a href="fest.php?id=${festival.festival_id}">
                            <div class="images">
                                <img src="${festival.slika}" class="festival-images" alt="">
                            </div>    
                            <div>
                                <h2>${festival.naziv_festivala}</h2>
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
    </div>

</div>
    
<?php 

include 'includes/overall/footer.php'; 
?>


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