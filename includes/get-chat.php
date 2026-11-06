<?php 
    session_start();
    if(isset($_SESSION['id'])){
        $hostname = "localhost";
        $username = "root";
        $password = "";
        $dbname = "muzickifestival";
    
        $conn = mysqli_connect($hostname, $username, $password, $dbname);
        if(!$conn){
            echo "Database connection error".mysqli_connect_error();
        }
        $outgoing_id = $_SESSION['id'];

        if(isset($_GET['user_id'])) {
            $incoming_id = $_GET['user_id'];  // ID primaoca preuzet iz URL-a
        } else {
            echo "No recipient selected.";
            exit;
        }

        // Ažuriraj nepročitane poruke kao pročitane
        $update_query = "UPDATE poruka SET procitano = 1 WHERE posiljalac_id = {$incoming_id} AND primalac_id = {$outgoing_id} AND procitano = 0";
        mysqli_query($conn, $update_query); // Izvrši upit za ažuriranje

        $output = "";
        $sql = "SELECT * FROM poruka LEFT JOIN korisnici ON korisnici.korisnik_id = poruka.posiljalac_id
                WHERE (primalac_id = {$outgoing_id} AND posiljalac_id = {$incoming_id})
                OR (primalac_id = {$incoming_id} AND posiljalac_id = {$outgoing_id}) ORDER BY poruka_id";

        $query = mysqli_query($conn, $sql);
        if(mysqli_num_rows($query) > 0){
            while($row = mysqli_fetch_assoc($query)){
                if($row['primalac_id'] === $incoming_id){
                    $output .= '<div class="chat outgoing">
                                <div class="details">
                                    <p>'. $row['poruka'] .'</p>
                                </div>
                                </div>';
                }else{
                    $output .= '<div class="chat incoming">
                                <img src="Slike/'.$row['profilna_slika'].'" alt="slika">
                                <div class="details">
                                    <p>'. $row['poruka'] .'</p>
                                </div>
                                </div>';
                }
            }
        }else{
            $output .= '<div class="text">Nema dostupnih poruka. Kada posaljete poruku ona ce se pojaviti ovde.</div>';
        }
        echo $output;
    }else{
        header("location: ../login.php");
    }
?>
<script>
   function scrollToBottom() {
        var chatBox = document.querySelector('.chat-box');
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    var chatBox = document.querySelector('.chat-box');

    // Detektovanje da li je korisnik na dnu
    chatBox.addEventListener('scroll', function() {
        if (chatBox.scrollTop + chatBox.clientHeight >= chatBox.scrollHeight) {
            // Korisnik je na dnu, automatski skrolujemo na poslednju poruku
            scrollToBottom();
        }
    });

</script>