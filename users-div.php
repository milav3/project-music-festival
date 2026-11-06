<section class="users">
    <div class="header">
    <div class="content">
        <?php 
        $sql = mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnik_id = {$_SESSION['id']}");
        if(mysqli_num_rows($sql) > 0){
            $row = mysqli_fetch_assoc($sql);
        }
        ?>
        <img src="Slike/<?php echo $row['profilna_slika']; ?>" style="width:50px; border-radius:50px;" alt="">
        <div class="details">
        <span><?php echo $row['ime']. " " . $row['prezime'] ?></span>
        <p><?php echo $row['status']; ?></p>
        </div>
    </div>
    </div>
    <div class="search">
    <label for="search-input" class="text"></label>
    <input type="text" name="search-input" placeholder="Enter name to search...">
    <button><i class="fas fa-search"></i></button>
    </div>
    <div class="users-list">

    </div>
</section>
<script src="javascript/users.js"></script>
