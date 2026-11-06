<style>
    .indikator {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 10px;
    }

    .zeleno {
        background-color:rgb(22, 190, 36); /* Zeleni kružić - nepročitane poruke */
    }

    .sivo {
        background-color:rgb(202, 199, 199); /* Sivi kružić - sve poruke pročitane */
    }
</style>

<?php
while ($row = mysqli_fetch_assoc($query)) {
    // Dohvatanje poslednje poruke između korisnika
    $sql2 = "SELECT * FROM poruka 
             WHERE (posiljalac_id = {$row['korisnik_id']} OR primalac_id = {$row['korisnik_id']}) 
             AND (primalac_id = {$outgoing_id} OR posiljalac_id = {$outgoing_id}) 
             ORDER BY poruka_id DESC LIMIT 1";
    $query2 = mysqli_query($conn, $sql2);
    $row2 = mysqli_fetch_assoc($query2);

    // Dohvatanje broja nepročitanih poruka
    $sql3 = "SELECT COUNT(*) AS neprocitane FROM poruka 
             WHERE posiljalac_id = {$row['korisnik_id']} 
             AND primalac_id = {$outgoing_id} 
             AND procitano = 0";
    $query3 = mysqli_query($conn, $sql3);
    $row3 = mysqli_fetch_assoc($query3);
    
    $neprocitane_poruke = ($row3) ? $row3['neprocitane'] : 0; // Ako nema rezultata, postavi na 0
    
    // Postavljamo poslednju poruku ako postoji
    $result = ($row2 && isset($row2['poruka'])) ? $row2['poruka'] : "Nema poruka";
    $msg = (strlen($result) > 28) ? substr($result, 0, 28) . '...' : $result;

    // Provera da li je poruku poslao trenutno ulogovan korisnik
    if ($row2 && isset($row2['primalac_id'])) {
        $you = ($outgoing_id == $row2['primalac_id']) ? "" : "You: ";
    } else {
        $you = "";
    }

    // Status online/offline
    $offline = ($row['status'] == "0") ? "offline" : "";
    
    // Sakrivanje trenutnog korisnika iz liste
    $hid_me = ($outgoing_id == $row['korisnik_id']) ? "hide" : "";

    // Dodaj indikator nepročitanih poruka
    $indikator = ($neprocitane_poruke > 0) ? "<span class='indikator zeleno'></span>" : "<span class='indikator sivo'></span>";

    $output .= '<a href="chat.php?user_id='. $row['korisnik_id'] .'">
                    <div class="content">
                        <img src="Slike/'. $row['profilna_slika'] .'" alt="">
                        <div class="details">
                            <span>'. $row['ime']. " " . $row['prezime'] .'</span> 
                            <p>'. $you . $msg .'</p>
                        </div>
                    </div>
                    ' . $indikator . '
                </a>';
}
?>