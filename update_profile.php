<?php
include 'core/function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Uzimamo podatke iz forme
    $novo_ime = $_POST['fName'];
    $novo_prezime = $_POST['lName'];
    $nov_email = $_POST['email'];
    $novo_korisnicko_ime = $_POST['username'];
    $nova_lozinka = md5($_POST['password']);
    $nova_profilna = $_POST['profile_pic'];


    // ID korisnika iz sesije
    $korisnik_id = $_SESSION['id'];
    $korisnicko_ime = $_SESSION['username'];
    $user= mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnicko_ime = '$novo_korisnicko_ime' AND korisnicko_ime != '$korisnicko_ime'");
    if(mysqli_num_rows($user) > 0){
        echo "Korisnicko ime je zauzeto!";
        exit;
    }
    if($nova_profilna == ''){
        $query = "UPDATE korisnici SET ime = '$novo_ime', prezime = '$novo_prezime', email = '$nov_email', korisnicko_ime = '$novo_korisnicko_ime', lozinka = '$nova_lozinka' WHERE korisnik_id = '$korisnik_id'";
    }
    else{
        $query = "UPDATE korisnici SET ime = '$novo_ime', prezime = '$novo_prezime', email = '$nov_email', korisnicko_ime = '$novo_korisnicko_ime', lozinka = '$nova_lozinka', profilna_slika = '$nova_profilna' WHERE korisnik_id = '$korisnik_id'";
    }
    mysqli_query($conn, $query);
    
    echo "Edit Successful"; // Ovim obaveštavaš JavaScript o uspehu
    
    header("Location: index.php");
    exit;
}
?>
