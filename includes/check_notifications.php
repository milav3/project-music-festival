<?php
include_once("../core/function.php"); 
if (!isset($_SESSION["id"])) {
    echo 0;
    exit;
}

$korisnik_id = $_SESSION["id"];
$query = "SELECT COUNT(*) AS count FROM obavestenja WHERE procitano = 0 AND korisnik_id = $korisnik_id";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

echo $row['count']; // Vraćamo broj nepročitanih obaveštenja
?>
