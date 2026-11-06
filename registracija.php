<?php
require 'core/function.php';
if(isset($_SESSION["id"])){
    $id = $_SESSION["id"];
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnik_id = $id"));
    $stranica = strtolower($user['tip_korisnika']) . '.php';
    header("Location: $stranica");
}
?>
<?php include 'includes/head.php'; ?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">

<div class="container-register">
    <h1 class="form-title">Registracija</h1>

    <form action="" method="POST" autocomplete="off">
        <input type="hidden" id="action" value="register">
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="fName" id="fName" placeholder="Ime" required><br>
            <label for="fName">Ime</label>
        </div>
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="lName" id="lName" placeholder="Prezime" required>
            <label for="lName">Prezime</label>
        </div>
        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" id="email" placeholder="Email adresa" required>
            <label for="email">Email adresa</label>
        </div>
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="username" id="username" placeholder="Korisnicko ime" required>
            <label for="username">Korisnicko ime </label>
        </div>
        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" id="password" placeholder="Lozinka" required>
            <span id="togglePassword"><i class="fa fa-eye toggle-password" id="prikazi"></i><i class="fa fa-eye-slash toggle-password" style="display:none;" id="sakriji"></i></span>
            <label for="password">Lozinka </label>
        </div>
        <div class="input-group">
            <select name="usertype" name="usertype" id="usertype" required>
                <option value="Administrator">Administrator</option>
                <option value="Izvodjac">Izvodjac</option>
                <option value="Organizator">Organizator</option>
                <option value="Posetilac">Posetilac</option>
            </select>
        </div>
        <button type="button" onclick="submitData();" class="btn">Registruj se</button>
        Vec imate nalog? <a href="login.php" style="color: #16292b !important;">Prijavi se</a>
    </form>
    <?php require 'core/script.php';?>
</div>
<?php //include 'includes/overall/footer.php'; ?>

<script>
        const passwordInput = document.getElementById("password");
        const toggleButton = document.getElementById("togglePassword");
        var prikazi = document.getElementById("prikazi");
        var sakriji = document.getElementById("sakriji");

        toggleButton.addEventListener("click", function () {
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                prikazi.style.display = "none";
                sakriji.style.display = "block";
            } else {
                passwordInput.type = "password";
                prikazi.style.display = "block";
                sakriji.style.display = "none";
            }
        });
</script>

<style>
    input{
        width: 100%;
    }
    #password{
        padding-right: 40px;
    }
    .toggle-password {
            position: absolute;
            right: 10px;
            top: 30%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 15px;
            color: #666;
    }
</style>