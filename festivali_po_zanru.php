<?php
include 'core/function.php';

if (!isset($_GET['zanr_id'])) {
    echo "<p style='text-align: center;'>Nema festivala za izabrani žanr.</p>";
    exit();
}

$zanr_id = intval($_GET['zanr_id']);

$festivali_query = mysqli_query($conn, "SELECT * FROM festivali WHERE zanr_id = $zanr_id");

if (mysqli_num_rows($festivali_query) == 0) {
    echo "<p style='text-align: center; color: white;'>Nema festivala za izabrani žanr.</p>";
    exit();
}
?>

<?php while ($festival = mysqli_fetch_assoc($festivali_query)): ?>
    <div class="tri_diva">
        <div class="unutrasnji">
            <a href="fest.php?id=<?php echo $festival['festival_id']; ?>">
                <div class="images">
                    <img src="<?php echo htmlspecialchars($festival['slika']); ?>" class="festival-images" alt="Festival">
                </div>    
                <div>
                    <br>
                    <h2><?php echo htmlspecialchars($festival['naziv_festivala']); ?></h2>
                    <br><br>
                    <?php echo htmlspecialchars($festival['datum_pocetka']); ?> - 
                    <?php echo htmlspecialchars($festival['datum_zavrsetka']); ?>
                    <br><br>
                    <i class="fas fa-location-dot"></i>
                    <?php echo htmlspecialchars($festival['lokacija']); ?>
                    <br><br>
                    Ulaz: <?php echo htmlspecialchars($festival['cena_ulaznica']); ?> RSD
                </div>
            </a>
        </div>
    </div>
<?php endwhile; ?>
