<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<script type="text/javascript">
    function submitData(){
        var data = {
            fName: $('#fName').val(),
            lName: $('#lName').val(),
            email: $('#email').val(),
            username: $('#username').val(),
            password: $('#password').val(),
            usertype: $('#usertype').val(),
            action: $('#action').val(),
        };

        $.ajax({
            url: 'core/function.php',
            type: 'post',
            data: data,
            success: function(response){
                console.log("Server response: ", response);
                if(response == "Administrator"){
                    window.location.href = "http://localhost/projekat_muzicki_festival/administrator.php";
                } else if(response == "Posetilac"){
                    window.location.href = "http://localhost/projekat_muzicki_festival/posetilac.php";
                } else if(response == "Organizator"){
                    window.location.href = "http://localhost/projekat_muzicki_festival/organizator.php";
                } else if(response == "Izvodjac"){
                    window.location.href = "http://localhost/projekat_muzicki_festival/izvodjac.php";
                } else {
                    alert(response); // Prikazuje poruku poput "Wrong Password" ili "User Not Registered"
                }
            },
            error: function(xhr, status, error){
                console.error("AJAX Error: ", error);
                alert("Došlo je do greške u komunikaciji sa serverom.");
            }
        });


    }
</script>