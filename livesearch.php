<?php
include 'core/function.php'; 
if(isset($_POST['input'])){
    $input = $_POST['input'];
    if (preg_match('/^[a-zA-Z0-9\s]*$/', $input)) {
        // Ako je validno (samo slova i razmaci)
            
        $query = "SELECT DISTINCT f.* FROM festivali f LEFT JOIN zanrovi z ON f.zanr_id = z.zanr_id LEFT JOIN scene s ON f.festival_id = s.festival_id WHERE f.naziv_festivala LIKE '%$input%' OR z.naziv_zanra LIKE '%$input%' OR s.naziv LIKE '%$input%'";

        $result = mysqli_query($conn, $query);
        //$broj_festivala_sql = "SELECT COUNT(*) AS ukupno FROM festivali WHERE naziv_festivala LIKE '{$input}%' OR opis LIKE '{$input}%'";
        //$broj_festivala_result = $conn->query($broj_festivala_sql);
        //$broj_festivala = $broj_festivala_result->fetch_assoc()['ukupno'];

        if(mysqli_num_rows($result) > 0){
            $festivali = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $festivaliJSON = json_encode($festivali);
            echo "<script>";
            echo "document.getElementById('searchresult').innerHTML = '';"; // Briše stare rezultate
            echo "var festivali = $festivaliJSON;"; // Prosleđujemo JSON u JavaScript promenljivu
            echo "festivali.forEach(festival => kreirajDiv(festival));"; // Pozivamo funkciju za svaki festival
            echo "</script>";
        } else{
            echo "<h3 style='color:white;'>Nema festivala koji odgovaraju pretrazi 😢 </h3>";
        }
    } else {
        // Ako tekst sadrži bilo koji znak koji nije slovo
        echo "<h3 style='color:white;'>Uneti tekst nije u ispravnom formatu  😢 </h3>";
    }
}
?>

zzzz