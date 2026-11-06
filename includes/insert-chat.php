<?php 
    session_start();
    var_dump($_GET); // Proveri da li je user_id ispravno prosleđen
    var_dump($_POST);
    
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
        $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
        $message = mysqli_real_escape_string($conn, $_POST['poruka']);
        if (!empty($message)) {
            // Priprema SQL upita
            $sql = "INSERT INTO poruka (primalac_id, posiljalac_id, poruka) VALUES (?, ?, ?)";
            
            // Priprema izjava
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "iis", $incoming_id, $outgoing_id, $message);
        
                // Izvrši upit
                if (mysqli_stmt_execute($stmt)) {
                    echo "Poruka je uspešno poslata!";
                } else {
                    echo "Greška prilikom slanja poruke.";
                }
        
                // Zatvori statement
                mysqli_stmt_close($stmt);
            } else {
                echo "Greška u pripremi SQL upita.";
            }
        }        
    }else{
        header("location: ../login.php");
    }


    
?>