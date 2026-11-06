<?php
    session_start();
        
    $hostname = "localhost";
    $username = "root";
    $password = "";
    $dbname = "muzickifestival";

    $conn = mysqli_connect($hostname, $username, $password, $dbname);
    if(!$conn){
        echo "Database connection error".mysqli_connect_error();
    } 
    $outgoing_id = $_SESSION['id'];
    $query = mysqli_query($conn, "SELECT tip_korisnika FROM korisnici WHERE korisnik_id = '$outgoing_id'");
    $userData = mysqli_fetch_assoc($query);
    $loggedUserType = $userData['tip_korisnika'];

    // Postavljamo koji tip korisnika treba da prikažemo
    if($loggedUserType == 'Izvodjac') {
        $filterType = 'Posetilac';
    } else {
        $filterType = 'Izvodjac';
    }
    $sql = "SELECT k.*, (SELECT MAX(vreme) FROM poruka WHERE (poruka.posiljalac_id = k.korisnik_id AND poruka.primalac_id = {$outgoing_id}) OR (poruka.posiljalac_id = {$outgoing_id} AND poruka.primalac_id = k.korisnik_id)) AS last_message_time, (SELECT COUNT(*) FROM poruka WHERE poruka.posiljalac_id = k.korisnik_id AND poruka.primalac_id = {$outgoing_id} AND poruka.procitano = 0) AS neprocitane_poruke FROM korisnici k WHERE k.korisnik_id != {$outgoing_id} AND k.tip_korisnika = '$filterType' ORDER BY last_message_time DESC";
    $query = mysqli_query($conn, $sql);
    $output = "";
    if(mysqli_num_rows($query) == 0){
        $output .= "No users are available to chat";
    }elseif(mysqli_num_rows($query) > 0){
        include_once "data.php";
    }
    echo $output;
?>