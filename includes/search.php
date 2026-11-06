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
    $searchTerm = mysqli_real_escape_string($conn, $_POST['searchTerm']);
    
    $query = mysqli_query($conn, "SELECT tip_korisnika FROM korisnici WHERE korisnik_id = '$outgoing_id'");
    $userData = mysqli_fetch_assoc($query);
    $loggedUserType = $userData['tip_korisnika'];
    if($loggedUserType == 'Izvodjac') {
        $filterType = 'Posetilac';
    } else {
        $filterType = 'Izvodjac';
    }
    $sql = "SELECT * FROM korisnici WHERE NOT korisnik_id = {$outgoing_id} AND tip_korisnika = '$filterType' AND (ime LIKE '%{$searchTerm}%' OR prezime LIKE '%{$searchTerm}%') ";
    $output = "";
    $query = mysqli_query($conn, $sql);
    if(mysqli_num_rows($query) > 0){
        include_once "data.php";
    }else{
        $output .= 'Korisnik nije pronadjen';
    }
    echo $output;
?>