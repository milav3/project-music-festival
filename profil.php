<?php 
include 'core/function.php';
if(isset($_SESSION["id"])){
    $id = $_SESSION["id"];
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnik_id = $id"));
}
else{
    header("Location: login.php");
}
?>
<style>
    .btn1{
        background: #218921 !important;
    }

    .btn2{
        background: #e93939 !important;
    }

    .btn1:hover{
        background: #185018 !important;
    }

    .btn2:hover{
        background: #bf1818 !important;
    }
</style>


<?php include 'includes/overall/header.php'; ?>
<?php $user=getUserByID() ?>
<div class="edit_profil">
    <form action="update_profile.php" method="POST" >
        <!-- Profilna slika -->
        <div class="input-group">   
            <img src="Slike/<?php echo $user['profilna_slika']?>" alt="Profilna slika" class="profile_image" id="profile_image">
            <label for="profile-pic" style="position: absolute; font-size:40px; top:75px; left:80px">+</label>
            <input type="file" id="profile-pic" class="profile_pic" name="profile_pic" hidden>
            <br><br>
        </div>

        <!-- Ime -->
        <div class="input-group">
            <input type="text" name="fName" class="fName"  value="<?php echo $user['ime']; ?>" required><br>
            <label for="fName">Ime </label>
        </div>
        <br>
        <!-- Prezime -->
        <div class="input-group">
            <input type="text" name="lName" id="lName" value="<?php echo $user['prezime']; ?>" required><br>
            <label for="lName">Prezime </label>
        </div>
        <br>
        <!-- E-mail adresa -->
        <div class="input-group">
            <input type="email" name="email" id="email"  value="<?php echo $user['email']; ?>" required><br>
            <label for="email">Email adresa</label>
        </div>
        <br>
        <!--Korisnicko ime-->
        <div class="input-group">
            <input type="text" name="username" id="Username"  value="<?php echo $_SESSION["username"]; ?>" required><br>
            <label for="username">Korisnicko ime</label>
        </div>
        <br>
        <!-- Lozinka -->
        <div class="input-group">
            <input type="password" name="password" id="password"  value="" required>
            <label for="password">Lozinka</label>
        </div>

        <!-- Dugmad -->
        <div class="input-actions">
            <button type="submit" class="btn btn1">Sacuvaj izmene</button>
            <br>
            <button onclick="window.history.back()" type="reset" class="btn btn2">Otkazi</button>

        </div>
    </form>
</div>

<?php include 'includes/overall/footer.php'; ?>



<script>
document.getElementById('profile-pic').addEventListener('change', function(event) {
    var file = event.target.files[0]; 
    if (file) {
        var reader = new FileReader();

        reader.onload = function(e) {
            document.getElementById('profile_image').src =  e.target.result;
        };

        reader.readAsDataURL(file); 
    }
});

</script>