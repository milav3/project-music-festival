<?php 
  include_once 'core/function.php';
  if(!isset($_SESSION['id'])){
    header("location: login.php");
  }
?>

<?php include "includes/overall/header.php";?>
  <div style="display:flex;justify-content: center;">
    <div class="wrapper">
      <?php include 'users-div.php';?>
    </div>
    <div class="wrapper" style="margin-left:20px;">
      <section class="chat-area">
        <div class="header">
          <?php 
            $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
            $sql = mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnik_id = {$user_id}");
            if(mysqli_num_rows($sql) > 0){
              $row = mysqli_fetch_assoc($sql);
            }else{
              header("location: users.php");
            }
          ?>
          <a href="users.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
          <img src="Slike/<?php echo $row['profilna_slika']; ?>" style="width:50px; border-radius:50px;" alt="">
          <div class="details">
            <span><?php echo $row['ime']. " " . $row['prezime'] ?></span>
            <p><?php echo $row['status']; ?></p>
          </div>
          </div>
        <div class="chat-box">

        </div>
        <form action="#" class="typing-area" id="chatForm">
          <input type="text" class="incoming_id" name="incoming_id" value="<?php echo $user_id; ?>" hidden>
          <input type="text" id="poruka" name="poruka" class="input-field" placeholder="Type a message here..." autocomplete="off">
          <button type="button" id="sendBtn">>></button>
        </form>

      </section>
    </div>
  </div>

  <script src="javascript/chat.js"></script>
  <script>
    window.onload = function() {
      window.scrollTo(0, 0);
    };
  </script>
  <?php include "includes/overall/footer.php"; ?>
