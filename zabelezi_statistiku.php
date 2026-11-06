<?php
include 'core/function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $festival_id = isset($_POST['festival_id']) ? intval($_POST['festival_id']) : null;
    $scena_id = isset($_POST['scena_id']) ? intval($_POST['scena_id']) : null;
    $izvodjac_id = isset($_POST['izvodjac_id']) ? intval($_POST['izvodjac_id']) : null;

    $tip_akcije = "";

    if ($izvodjac_id) {
        $tip_akcije = "poseta_izvodjacu";
    } elseif ($scena_id) {
        $tip_akcije = "poseta_sceni";
    } elseif ($festival_id) {
        $tip_akcije = "poseta_festivalu";
    }

    if ($tip_akcije) {
        $query = "INSERT INTO statistika_poseta (festival_id, scena_id, izvodjac_id, tip_akcije, datum_vreme) 
                  VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiis", $festival_id, $scena_id, $izvodjac_id, $tip_akcije);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Poseta zabeležena"]);
        } else {
            echo json_encode(["success" => false, "message" => "Greška pri upisu u bazu"]);
        }
        $stmt->close();
    }
    exit;
}


$tip_korisnika = $_SESSION['tip_korisnika'] ?? '';
if ($tip_korisnika === 'Izvodjac'){
    include 'includes/overall/header.php'; 
    }
// Ako je zahtev GET, prikazujemo statistiku
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($tip_korisnika != 'Izvodjac'){
            include 'includes/overall/header.php'; 
    echo "<h2 style='text-align: center; color:white;'>Statistika posećenosti</h2>";

    // Prikaz statistike festivala
    $statistika_query = $conn->query("
        SELECT 
            f.naziv_festivala,
            COALESCE(SUM(CASE WHEN s.tip_akcije = 'poseta_festivalu' THEN 1 END), 0) AS posete_festivalu
        FROM festivali f
        LEFT JOIN statistika_poseta s ON f.festival_id = s.festival_id
        GROUP BY f.festival_id
    ");

    echo "<h3 style='color:white; text-align:center;'>Posecenost festivala</h3>
        <table style='margin: 0 auto;' border='1'>
        <tr><th>Festival</th><th>Posete festivalu</th></tr>";

    while ($red = $statistika_query->fetch_assoc()) {
        echo "<tr>
            <td>{$red['naziv_festivala']}</td>
            <td>{$red['posete_festivalu']}</td>
        </tr>";
    }
    echo "</table><br>
        <canvas id='festivalChart'></canvas>
        <br><br>
    ";

    // Prikaz statistike scena
    $scene_statistika_query = $conn->query("
        SELECT 
            f.naziv_festivala,
            sc.naziv AS naziv_scene,
            COALESCE(SUM(CASE WHEN sp.tip_akcije = 'poseta_sceni' THEN 1 END), 0) AS broj_poseta
        FROM scene sc
        LEFT JOIN statistika_poseta sp ON sc.id = sp.scena_id
        LEFT JOIN festivali f ON sc.festival_id = f.festival_id
        GROUP BY sc.id, f.festival_id
    ");

    echo "<h3 style='color:white; text-align:center;'>Posecenost scena na svakom festivalu</h3>
        <table style='margin: 0 auto;' border='1'>
        <tr><th>Festival</th><th>Scena</th><th>Broj poseta</th></tr>";

    while ($red = $scene_statistika_query->fetch_assoc()) {
        echo "<tr>
            <td>{$red['naziv_festivala']}</td>
            <td>{$red['naziv_scene']}</td>
            <td>{$red['broj_poseta']}</td>
        </tr>";
    }
    echo "</table>
        <canvas id='scenaChart'></canvas><br><br>
    ";
    
} 
$posete = isset($_COOKIE['posete_izvodjacima']) ? json_decode($_COOKIE['posete_izvodjacima'], true) : [];

// Dohvati posete iz baze
$izvodjaci_query = $conn->query("
    SELECT izvodjac_id, COUNT(*) AS broj_poseta
    FROM statistika_poseta
    WHERE tip_akcije = 'poseta_izvodjacu'
    GROUP BY izvodjac_id
");

// Ubaci podatke iz baze u niz poseta
while ($red = $izvodjaci_query->fetch_assoc()) {
    $izvodjac_id = $red['izvodjac_id'];
    $broj_poseta = $red['broj_poseta'];

    // Ako izvođač postoji u kolačiću, dodaj njegove posete iz baze
    if (isset($posete[$izvodjac_id])) {
        $posete[$izvodjac_id] += $broj_poseta;
    } else {
        $posete[$izvodjac_id] = $broj_poseta;
    }
}

}


// Proveri da li postoji POST zahtev za beleženje posete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['izvodjac_id'])) {
    $izvodjac_id = intval($_POST['izvodjac_id']);

    // Učitaj prethodne podatke iz kolačića
    $posete = isset($_COOKIE['posete_izvodjacima']) ? json_decode($_COOKIE['posete_izvodjacima'], true) : [];

    // Ako izvođač već postoji u kolačiću, povećaj broj poseta, inače postavi na 1
    if (isset($posete[$izvodjac_id])) {
        $posete[$izvodjac_id]++;
    } else {
        $posete[$izvodjac_id] = 1;
    }

    // Postavi kolačić sa novim podacima (važi 7 dana)
    setcookie('posete_izvodjacima', json_encode($posete), time() + (7 * 24 * 60 * 60), "/");

    echo json_encode(['success' => true, 'message' => 'Poseta izvođaču zabeležena']);
    exit;
}

?>
    
<br>

<?php
//echo "<pre>";
//print_r($_SESSION); // Proveravamo koje sesijske promenljive postoje
//echo "</pre>";

$tip_korisnika = $_SESSION['tip_korisnika'] ?? '';
$korisnik_id = $_SESSION['id'] ?? null; // Ovdje koristimo 'id' umesto 'korisnik_id'

//echo "Tip korisnika: $tip_korisnika <br>";
//echo "ID korisnika: " . ($korisnik_id !== null ? $korisnik_id : "Nije definisan") . "<br>";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($tip_korisnika === 'Izvodjac') {
        echo "<table><tr><th>Ime izvođača</th><th>Broj poseta</th></tr>";

        if (!empty($korisnik_id)) { 
            $korisnik_id = intval($korisnik_id); 
            $izvodjac_query = $conn->query("
                SELECT COUNT(*) AS broj_poseta 
                FROM statistika_poseta 
                WHERE tip_akcije = 'poseta_izvodjacu' 
                AND izvodjac_id = $korisnik_id
            ");

            if ($izvodjac_query && $red = $izvodjac_query->fetch_assoc()) {
                $broj_poseta = $red['broj_poseta'] ?? 0;
            } else {
                $broj_poseta = 0;
            }

            echo "<tr><td>Vaše posete</td><td>$broj_poseta</td></tr>";
        } else {
            echo "<tr><td colspan='2' style='color: red;'>Greška: ID izvođača nije definisan.</td></tr>";
        }

        echo "</table>";
        
    } else {
        echo " <h3 style='color:white; text-align:center;'>Posecenost izvodjaca</h3>
            <table><tr><th>Ime izvođača</th><th>Broj poseta</th></tr>";

        $izvodjaci_query = $conn->query(" 
            SELECT k.ime, k.prezime, COUNT(s.izvodjac_id) AS broj_poseta 
            FROM korisnici k 
            LEFT JOIN statistika_poseta s ON k.korisnik_id = s.izvodjac_id 
            WHERE s.tip_akcije = 'poseta_izvodjacu' 
            GROUP BY k.korisnik_id
        ");
        
        if ($izvodjaci_query->num_rows > 0) {
            while ($red = $izvodjaci_query->fetch_assoc()) {
                echo "<tr><td>{$red['ime']} {$red['prezime']}</td><td>{$red['broj_poseta']}</td></tr>";
            }
        } else {
            echo "<tr><td colspan='2'>Nema podataka o posetama.</td></tr>";
        }

        echo "</table>
        
            <canvas id='izvodjacChart'></canvas><br><br>";
        include 'includes/overall/footer.php'; 
    }
}
if ($tip_korisnika === 'Izvodjac') {
    include 'includes/overall/footer.php';
}

?>









<!-- Chart.js biblioteka -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
// Dohvatanje podataka o festivalima
$festivali_query = $conn->query("
    SELECT f.naziv_festivala, COUNT(s.festival_id) AS posete_festivalu
    FROM festivali f
    LEFT JOIN statistika_poseta s ON f.festival_id = s.festival_id
    WHERE s.tip_akcije = 'poseta_festivalu'
    GROUP BY f.festival_id
");

$festivali_data = [];
while ($red = $festivali_query->fetch_assoc()) {
    $festivali_data[] = $red;
}

// Dohvatanje podataka o izvođačima
$izvodjaci_query = $conn->query("
    SELECT k.ime, k.prezime, COUNT(s.izvodjac_id) AS broj_poseta
    FROM korisnici k
    LEFT JOIN statistika_poseta s ON k.korisnik_id = s.izvodjac_id
    WHERE s.tip_akcije = 'poseta_izvodjacu'
    GROUP BY k.korisnik_id
");

$izvodjaci_data = [];
while ($red = $izvodjaci_query->fetch_assoc()) {
    $izvodjaci_data[] = $red;
}

// Dohvatanje podataka o scenama festivala
$scene_query = $conn->query("
    SELECT f.naziv_festivala, sc.naziv AS naziv_scene, COUNT(sp.scena_id) AS broj_poseta
    FROM scene sc
    LEFT JOIN statistika_poseta sp ON sc.id = sp.scena_id
    LEFT JOIN festivali f ON sc.festival_id = f.festival_id
    WHERE sp.tip_akcije = 'poseta_sceni'
    GROUP BY sc.id, f.festival_id
");

$scene_data = [];
while ($red = $scene_query->fetch_assoc()) {
    $scene_data[] = $red;
}

// Konvertovanje podataka u JavaScript format
echo "<script>
    let sceneLabels = " . json_encode(array_map(function($scene) {
        return $scene['naziv_festivala'] . ' - ' . $scene['naziv_scene'];
    }, $scene_data)) . ";
    let scenePosete = " . json_encode(array_column($scene_data, 'broj_poseta')) . ";
    
    let festivalLabels = " . json_encode(array_column($festivali_data, 'naziv_festivala')) . ";
    let festivalPosete = " . json_encode(array_column($festivali_data, 'posete_festivalu')) . ";

    let izvodjacLabels = " . json_encode(array_map(function($izvodjac) {
        return $izvodjac['ime'] . ' ' . $izvodjac['prezime'];
    }, $izvodjaci_data)) . ";
    let izvodjacPosete = " . json_encode(array_column($izvodjaci_data, 'broj_poseta')) . ";
</script>";
?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Grafikon za scene festivala
        let ctx3 = document.getElementById('scenaChart').getContext('2d');
        new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: sceneLabels,
                datasets: [{
                    label: 'Broj poseta sceni',
                    data: scenePosete,
                    backgroundColor: '#065c06c7',
                    borderColor: 'green',
                    borderWidth: 1.5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        ticks: {
                            color: 'white', // Boja labela na X osi
                            font: {
                                size: 14 // Održava veličinu teksta
                            },
                            padding: 10,  // Povećava razmak između labela i ose
                            maxRotation: 90, // Sprečava rotaciju (možeš staviti 45 ako su dugačke)
                            minRotation: 0
                        }
                    },
                    y: {
                        ticks: {
                            color: 'white', // Boja labela na Y osi
                            font: {
                                size: 14 // Postavi veličinu fonta za X osu
                            },
                            padding: 10,  // Povećava razmak između labela i ose
                            maxRotation: 90, // Sprečava rotaciju (možeš staviti 45 ako su dugačke)
                            minRotation: 0
                        }
                    }
                }
            }
        });
    });
    document.addEventListener("DOMContentLoaded", function () {
        // Grafikon za festivale
        let ctx1 = document.getElementById('festivalChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: festivalLabels,
                datasets: [{
                    label: 'Posete festivalu',
                    data: festivalPosete,
                    backgroundColor: '#065c06c7',
                    borderColor: 'green',
                    borderWidth: 1.5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        ticks: {
                            color: 'white', // Boja labela na X osi
                            font: {
                                size: 14 // Održava veličinu teksta
                            },
                            padding: 10,  // Povećava razmak između labela i ose
                            maxRotation: 90, // Sprečava rotaciju (možeš staviti 45 ako su dugačke)
                            minRotation: 0
                        }
                    },
                    y: {
                        ticks: {
                            color: 'white', // Boja labela na Y osi
                            font: {
                                size: 14 // Postavi veličinu fonta za X osu
                            },
                            padding: 10,  // Povećava razmak između labela i ose
                            maxRotation: 90, // Sprečava rotaciju (možeš staviti 45 ako su dugačke)
                            minRotation: 0
                        }
                    }
                }
            }
        });

        // Grafikon za izvođače
        let ctx2 = document.getElementById('izvodjacChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: izvodjacLabels,
                datasets: [{
                    label: 'Posete izvođačima',
                    data: izvodjacPosete,
                    backgroundColor: '#065c06c7',
                    borderColor: 'green',
                    borderWidth: 1.5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        ticks: {
                            color: 'white', // Boja labela na X osi
                            font: {
                                size: 14 // Održava veličinu teksta
                            },
                            padding: 10,  // Povećava razmak između labela i ose
                            maxRotation: 90, // Sprečava rotaciju (možeš staviti 45 ako su dugačke)
                            minRotation: 0
                        }
                    },
                    y: {
                        ticks: {
                            color: 'white', // Boja labela na Y osi
                            font: {
                                size: 14 // Postavi veličinu fonta za X osu
                            },
                            padding: 10,  // Povećava razmak između labela i ose
                            maxRotation: 90, // Sprečava rotaciju (možeš staviti 45 ako su dugačke)
                            minRotation: 0
                        }
                    }
                }
            }
        });
    });
</script>
<style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #ffffffa1;}
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #2b3b4a; color: white; }
        
</style>
