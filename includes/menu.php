<?php 
ob_start();
if(isset($_SESSION["id"])){
    $user = getUserByID();
    $name = $user['ime'] . ' ' . $user['prezime'];
    $korisnik_id = $_SESSION["id"]; 
}
else{
    $name = '';
    $korisnik_id=0;
} 
$find_notifications = "SELECT * FROM obavestenja WHERE procitano = 0 AND korisnik_id = $korisnik_id";

$result = mysqli_query($conn,$find_notifications);
$count_active = '';
$notifications_data = array(); 
$deactive_notifications_dump = array();
while($rows = mysqli_fetch_assoc($result)){
        $count_active = mysqli_num_rows($result);
        $notifications_data[] = array(
                    "id" => $rows['id'],
                    "korisnik_id" => $rows['korisnik_id'],
                    "izvodjac_id"=>$rows['izvodjac_id'],
                    "izvodjac"=>$rows['izvodjac'],
                    "poruka"=>$rows['poruka']
        );
}
//only five specific posts
$deactive_notifications = "SELECT * FROM obavestenja WHERE procitano = 1 AND korisnik_id = $korisnik_id ORDER BY id DESC LIMIT 0,5";
$result = mysqli_query($conn,$deactive_notifications);
while($rows = mysqli_fetch_assoc($result)){
    $deactive_notifications_dump[] = array(
        "id" => $rows['id'],
        "korisnik_id" => $rows['korisnik_id'],
        "izvodjac_id"=>$rows['izvodjac_id'],
        "izvodjac"=>$rows['izvodjac'],
        "poruka"=>$rows['poruka']
    );
}
?>

<style>
  .round{
    width:11px;
    height:10px;
    border-radius:50%;
    position:relative;
    background:red;
    display:inline-block;
    padding:0.2rem 0.2rem !important;
    margin:0.2rem -0.6rem !important;
    left:-3px;
    top:-8px;
    z-index: 99 !important;
    color: white;
    font-size: 11px;
  }
  .round > span {
    color:white;
    display:block;
    text-align:center;
    font-size:0.7rem !important;
    padding:0 !important;
  }
  #list{
    border-radius: 4%;
    display: none;
    top: 30px;
    position: absolute;
    left:-156px;
    background:#eae9e9;
    z-index:9999 !important;
    width: 25vw;
    margin-left: -37px;
    padding:0 !important;
    margin:0 auto !important;
    color: #2b3b4acf;
    box-shadow: 15px 15px 15px rgba(0.2, 0.2, 0.2, 0.4);

  }

  #list::after {
    content: "";
    position: absolute;
    top: -10px; /* Postavi ga iznad menija */
    right: 50%; /* Centriraj ga ispod imena */
    border-width: 5px;
    border-style: solid;
    border-color: transparent transparent #ebeaea transparent; /* Trougao usmeren ka dole */
  }

  .message > span {
      width:93%;
      display:block;
      color:#2b3b4a;
      text-align:justify;
      margin:0.2rem 0.3rem !important;
      padding:0.3rem !important;
      line-height:1rem !important;
      font-weight:bold;
      border-bottom:1px solid #dfcdcd;
      font-size:1rem !important;
  }
  .message{
    /* background:#ff7f50;
    margin:0.3rem 0.2rem !important;
    padding:0.2rem 0 !important;
    width:100%;
    display:block; */
    
  }
  .message > .msg {
      width:90%;
      margin:0.2rem 0.3rem !important;
      padding:0.2rem 0.2rem !important;
      text-align:justify;
      font-weight:bold;
      display:block;
      word-wrap: break-word;   
  }
  
  .dropdown-menu-vise .dropdown-item-vise{
    color: white;
    background: #20393c;
    box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.30);
    display: block;
    height: 40px;
    font-size: 13px;
    align-content: center;
  }
  .dropdown-menu-vise {
      z-index: 999;
      display: none;
      right: 75px;
      padding: 5px;
      width: 150px;
      text-align: center;
      transition: all 0.3s ease-in-out;
      position: relative; /* Omogućava da trougao bude deo ovog menija */
  }
  .vise-div{
    padding-top:17px;
  }


  #msg-count{
    color: white;
    font-size: 11px;
    position: absolute;
    top: -8px;
    left: 18px;
  }
</style>



<nav>
    <ul>
        <?php
        if(isset($_SESSION["id"]) && ($user['tip_korisnika']=='Posetilac' || $user['tip_korisnika']=='Izvodjac')){?>
          <li class="menu_li" style="width:20px; position:relative;"><a href="users.php" class="menu_link"><i class="fas fa-message" style="z-index:-99 !important;font-size:20px;color:white;margin:0rem 0rem !important;"></i>
            <div class="round" id="msg-count" style="display: none;"><span>0</span></div>
          </a> </li>
        <?php
        }    

        if(isset($_SESSION["id"]) && $user['tip_korisnika']=='Posetilac'){?>
            <li class="menu_li" style="width:20px;"><a href="omiljeni.php" class="menu_link"><i class="fas fa-heart" style="z-index:-99 !important;font-size:20px;color:white;margin:0rem 0rem !important;"></i></a> </li>
            <div style="position:relative;">
            <li class="menu_li" style="width:20px; position:relative;"><i class="fa fa-bell"   id="over" data-value ="<?php echo $count_active;?>" style="z-index:-99 !important;font-size:20px;color:white;margin:0rem 0rem !important;"></i>
            <?php 
            if(!empty($count_active)){?>
              <div class="round" id="bell-count" data-value ="<?php echo $count_active;?>"><span><?php echo $count_active; ?></span></div>
            <?php 
            }?>
            </li>
            <?php
            if(!empty($count_active)){?>
              <div id="list">
                <?php
                foreach($notifications_data as $list_rows){?>
                  <li id="message_items">
                    <div class="message alert alert-warning" data-id=<?php echo $list_rows['id'];?>>
                      <span><?php echo $list_rows['izvodjac'];?></span>
                      <div class="msg">
                        <p><?php echo $list_rows['poruka'];?></p>
                      </div>
                    </div>
                  </li>
                <?php
                }
                ?> 
              </div> 
              <?php 
            }
            else{?>
              <!--old Messages-->
              <div id="list">
                <?php
                foreach($deactive_notifications_dump as $list_rows){?>
                  <li id="message_items">
                    <div class="message alert alert-danger" data-id=<?php echo $list_rows['id'];?>>            
                      <span><?php echo $list_rows['izvodjac'];?></span>
                      <div class="msg">
                        <p><?php echo $list_rows['poruka']; ?></p>
                      </div>
                    </div>
                  </li>
                <?php 
                }
                ?>
                <!--old Messages-->
                <?php 
            } ?>
        <?php
        }
        if(isset($_SESSION["id"]) && $user['tip_korisnika']=='Izvodjac'){?>
          <li class="menu_li" style="width: 15px;"><i class="fas fa-chevron-down" style="color:white;" id="vise"></i>
            <div class="dropdown-menu-vise" id="izvodjac_dropdown">
              <div class="vise-div"><a href="slanje_obavestenja.php" class="dropdown-item-vise">DODAJ OBAVESTENJE</a></div>
              <div class="vise-div"><a href="informacije_o_izvodjacu.php" class="dropdown-item-vise">IZMENI INFORMACIJE</a></div>
              <div class="vise-div"><a href="zabelezi_statistiku.php" class="dropdown-item-vise">STATISTIKA</a></div>
            </div>
          </li>
        <?php
        }
        if(isset($_SESSION["id"]) && $user['tip_korisnika']=='Administrator'){?>
          <li class="menu_li" style="width: 15px;"><i class="fas fa-chevron-down" style="color:white;" id="vise"></i>
            <div class="dropdown-menu-vise" id="administrator_dropdown">
              <div class="vise-div"><a href="includes/rezultati_anketa.php" class="dropdown-item-vise">REZULTATI ANKETA</a></div>
              <div class="vise-div"><a href="includes/kreiraj_anketu.php" class="dropdown-item-vise">KREIRAJ ANKETU</a></div>
              <div class="vise-div"><a href="zabelezi_statistiku.php" class="dropdown-item-vise">STATISTIKA</a></div>
            </div>
          </li>
        <?php
        }
        if(isset($_SESSION["id"])){?>
          </div>
          </div>
        <li class="menu_li" style="width:auto;"><label for="" id="ime" style="position: relative; top: -1px; left:0px; font-weight:bold; white-space: nowrap;"><?php echo strtoupper($name) ?></label>
          <div class="dropdown-menu" id="logoutMenu">
            <a href="logout.php" class="dropdown-item">Odjavi se</a>
          </div>
        </li> 
        <?php
        }
        else{
          ?>
            <li class="menu_li" ><a href="index.php" class="menu_link">PREGLED PROGRAMA</a></li>
            <li class="menu_li"><a href="login.php" class="menu_link">PRIJAVI SE</a></li>
          <?php  
        }
        ?>
           
        <li style="width: 50px;">
        <?php 
        if(isset($_SESSION["id"])){
            $user = getUserByID();
            $putanja = $user['profilna_slika'];
        ?>
            <a href="profil.php" style="float: inline-start;"><img src="Slike/<?php echo $putanja?>" alt="Profilna slika" class="profile_image" ></a>
        <?php
        }
        else{
        ?>
            <a href="profil.php" style="float: inline-start;"><img src="Slike/default_image.jpg" alt="Profilna slika" class="profile_image" ></a>
        <?php
        }
        ?>    
        </li>
    </ul>
</nav>

<script>
  //Ovaj script je za izvodjaca dropdown

    document.getElementById("vise").addEventListener("click", function () {
        let menu = document.getElementById("izvodjac_dropdown");
        // Prikaz/skrivanje menija
        menu.style.display = (menu.style.display === "block") ? "none" : "block";
    });

    // Zatvaranje menija ako se klikne van njega
    document.addEventListener("click", function (event) {
        let notifIcon = document.getElementById("vise");
        let menu = document.getElementById("izvodjac_dropdown");

        if (!notifIcon.contains(event.target) && !menu.contains(event.target)) {
            menu.style.display = "none";
        }
    });

    document.getElementById("vise").addEventListener("click", function () {
        let menu = document.getElementById("administrator_dropdown");
        // Prikaz/skrivanje menija
        menu.style.display = (menu.style.display === "block") ? "none" : "block";
    });

    // Zatvaranje menija ako se klikne van njega
    document.addEventListener("click", function (event) {
        let notifIcon = document.getElementById("vise");
        let menu = document.getElementById("administrator_dropdown");

        if (!notifIcon.contains(event.target) && !menu.contains(event.target)) {
            menu.style.display = "none";
        }
    });
</script>


<script>
  //Ovaj script je za logout dropdown

    document.getElementById("ime").addEventListener("click", function () {
        let menu = document.getElementById("logoutMenu");
        // Prikaz/skrivanje menija
        menu.style.display = (menu.style.display === "block") ? "none" : "block";
    });

    // Zatvaranje menija ako se klikne van njega
    document.addEventListener("click", function (event) {
        let notifIcon = document.getElementById("ime");
        let menu = document.getElementById("logoutMenu");

        if (!notifIcon.contains(event.target) && !menu.contains(event.target)) {
            menu.style.display = "none";
        }
    });
</script>

<script>
$(document).ready(function(){
    // Klik na ikonu obaveštenja
    $('#over').on('click', function(){
        // Prebaci vidljivost liste obaveštenja
        $('#list').toggle();  
    });

    // Zatvara meni ako se klikne van njega
    $(document).on('click', function(event) {
        var notifIcon = $('#over');
        var menu = $('#list');
        if (!notifIcon[0].contains(event.target) && !menu[0].contains(event.target)) {
            menu.hide(); // Sakrij meni ako klikneš van njega
        }
    });

    // Klik na obaveštenje označava ga kao pročitan
    $('#list').on('click', '#message_items', function(e) {
        var notifId = $(this).find('.message').data('id'); // Dobijanje ID-a obaveštenja
        
        $.ajax({
            url: 'includes/deactive.php', // Putanja do PHP skripte koja označava obaveštenje kao pročitan
            type: 'GET',
            data: { id: notifId }, // Poslat ID obaveštenja
            success: function(response) {
                //console.log(response);
                console.log(notifId);
                // Možemo promeniti stil obaveštenja ili ga ukloniti iz liste
                $(e.target).closest('li').fadeOut(); // Fade out obaveštenje
                // Opciono, možeš da ažuriraš brojač obaveštenja
                var newCount = parseInt($('#bell-count span').text()) - 1;
                if (newCount >= 0) {
                    $('#bell-count span').text(newCount); // Ažuriraj brojač
                    if (newCount === 0) {
                        $('#bell-count').hide(); // Sakrij brojač ako nema više obaveštenja
                        location.reload();
                    }
                }
            },
            error: function() {
                alert('Došlo je do greške pri označavanju obaveštenja.');
            }
        });
    });
});

$(document).ready(function(){
    function updateUnreadMessages() {
        $.ajax({
            url: 'includes/get_unread_messages.php', // Kreiraj ovaj fajl za dohvat broja nepročitanih poruka
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if(response.unread_count > 0) {
                    $('#msg-count').text(response.unread_count).show();
                } else {
                    $('#msg-count').hide();
                }
            }
        });
    }

    // Prvo pokreni funkciju odmah kada se stranica učita
    updateUnreadMessages();

    // Onda ažuriraj svakih 5 sekundi
    setInterval(updateUnreadMessages, 2000);
});


$(document).ready(function() {
    function updateNotifications() {
        $.ajax({
            url: 'includes/check_notifications.php', // Putanja do PHP koda koji broji nepročitana obaveštenja
            type: 'GET',
            success: function(response) {
                let count = parseInt(response); // Konvertujemo odgovor u broj
                if (count > 0) {
                    $('#bell-count').text(count).show(); // Ažuriramo broj i prikazujemo ako ima obaveštenja
                } else {
                    $('#bell-count').hide(); // Sakrij ako nema novih obaveštenja
                }
            }
        });
    }

    // Prvi poziv odmah da se učita broj obaveštenja
    updateNotifications();

    // Pokreni proveru na svake 2 sekunde
    setInterval(updateNotifications, 2000);
});

</script>

