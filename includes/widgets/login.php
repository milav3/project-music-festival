<div class="widget">
    <div class="inner">
        <h2 class="form-title">Prijavite se</h2>
        <form autocomplete="off" action="" method="post">
            <input type="hidden" id="action" value="login">
            <ul id="login">
                <li>
                    <input type="text" id="username" name="username" autocomplete="off">
                    <label for="username" id=login-username>Korisnicko ime</label>
                </li>
                <li id="pass-li">
                    <input type="password" id="password" name="password" >
                    <label for="password" id="login-password">Lozinka</label>
                    <span id="togglePassword"><i class="fa fa-eye toggle-password" id="prikazi"></i><i class="fa fa-eye-slash toggle-password" style="display:none;" id="sakriji"></i></span>
                </li>
                <li>
                    <button type="button" class="btn" onclick="submitData();">Prijavi se</button>
                </li>
                <li>
                    <p class="account">Nemate nalog? </p><a href="registracija.php" id="register-link">Registrujte se</a>
                </li>
            </ul>
        </form>
    </div>
</div> 

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
    .toggle-password {
            position: absolute;
            right: 0pc;
            top: 14px;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 15px;
            color: #666;
    }
    #pass-li{
        position: relative;
    }
</style>