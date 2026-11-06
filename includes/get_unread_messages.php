<?php
include_once '../core/function.php';
$unread_count = 0;

// Proverite da li je korisnik prijavljen
if (isset($_SESSION['id'])) {
    $outgoing_id = $_SESSION['id'];
    
    

    // Upit za brojanje nepročitanih poruka
    $sql = "SELECT COUNT(DISTINCT posiljalac_id) AS unread_count 
            FROM poruka 
            WHERE primalac_id = {$outgoing_id} AND procitano = 0";
    
    $query = mysqli_query($conn, $sql);
    if ($query) {
        $row = mysqli_fetch_assoc($query);
        $unread_count = $row['unread_count'];
    }
    mysqli_close($conn);
}

// Vraćanje broja nepročitanih poruka kao JSON odgovor
echo json_encode(['unread_count' => $unread_count]);
?>
