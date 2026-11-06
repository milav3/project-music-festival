<?php
session_start();
include 'database\connect.php';

//LOGIN I REGISTRACIJA
if(isset($_POST["action"])){
    if($_POST["action"] == "register"){
        register();
    }
    else if($_POST["action"] == "login"){
        login();
    }
}
//REGISTRACIJA
function register(){
    global $conn;
    
    $fName=$_POST["fName"];
    $lName=$_POST["lName"];
    $email=$_POST["email"];
    $username=$_POST["username"];
    $password = md5($_POST["password"]);
    $usertype=$_POST["usertype"];
    $profile_picture='default_image.jpg';
    
    
    if(empty($fName) || empty($lName) || empty($email) || empty($username) || empty($password) || empty($usertype)){
        echo "Please Fill Out The Form!";
        exit;
    }
    
    $user= mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnicko_ime = '$username'");
    if(mysqli_num_rows($user) > 0){
        echo "Username has already taken";
        exit;
    }

    $query = "INSERT INTO korisnici VALUES('', '$fName', '$lName', '$email', '$username','$password', '$usertype', '$profile_picture', 'offline', '0')";
    mysqli_query($conn, $query);
    
    echo "Registration Successful"; // Ovim obaveštavaš JavaScript o uspehu
    exit;
}

//LOGIN
function login(){
    global $conn;
    $username=$_POST["username"];
    $password = md5($_POST["password"]);

    $user= mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnicko_ime = '$username'");

    if(mysqli_num_rows($user) > 0){
        $row = mysqli_fetch_assoc($user);

        if($password == $row["lozinka"]){
            $_SESSION["login"] = true;
            $_SESSION["username"] = $username;
            $_SESSION["id"] = $row["korisnik_id"];
            $_SESSION['tip_korisnika'] = $row["tip_korisnika"];

            if($row["tip_korisnika"] == "Administrator"){
                echo "Administrator";
                exit;
            }
            if($row["tip_korisnika"] == "Posetilac"){
                echo "Posetilac";
                exit;
            }
            if($row["tip_korisnika"] == "Izvodjac"){
                echo "Izvodjac";
                exit;
            }
            if($row["tip_korisnika"] == "Organizator"){
                echo "Organizator";
                exit;
            }
        } else {
            echo "Wrong Password";
            exit;
        }
    } else {
        echo "User Not Registered";
        exit;
    
    }
}

function getUserByID(){
    global $conn;

    $user = mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnik_id = '" . $_SESSION["id"] . "'");
    $row = mysqli_fetch_assoc($user);
    return $row;
}


function dodajObavestenje($izvodjac_id, $poruka) {
    global $conn;

    // Pronađi sve korisnike koji prate izvođača
    $query = "SELECT korisnik_id FROM omiljeni WHERE izvodjac_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $izvodjac_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Ubaci notifikacije za svakog korisnika
    while ($row = $result->fetch_assoc()) {
        $korisnik_id = $row['korisnik_id'];
        $insert = "INSERT INTO obavestenja (korisnik_id, izvodjac_id, poruka) VALUES (?, ?, ?)";
        $stmt2 = $conn->prepare($insert);
        $stmt2->bind_param("iis", $korisnik_id, $izvodjac_id, $poruka);
        $stmt2->execute();
    }
}

function updateUserStatus($userId, $status) {
    global $conn;
    
    // Ažuriranje statusa i vremena poslednje aktivnosti
    $stmt = $conn->prepare("UPDATE korisnici SET status = ? WHERE korisnik_id = ?");
    $stmt->bind_param("si", $status, $userId);
    $stmt->execute();
    $stmt->close();
}
?>