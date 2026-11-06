<?php
// Povezivanje sa bazom
$host = 'localhost';
$dbname = 'muzickifestival';
$user = 'root';
$password = '';

$conn = new mysqli($host, $user, $password, $dbname);

// Provera konekcije
if ($conn->connect_error) {
    die("Greška pri povezivanju sa bazom: " . $conn->connect_error);
}

// Provera da li je korisnik prijavljen
if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    echo "Morate biti prijavljeni da biste ostavili ocenu.";
    exit;
}

$korisnicko_ime = $_SESSION['username'];
$korisnik_id = $_SESSION['id'];

// Dobijanje trenutnog festivala iz URL-a
$festival_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($festival_id <= 0) {
    echo "Festival nije pronađen.";
    exit;
}


// Prosečna ocena festivala
$query = "SELECT COALESCE(AVG(ocena), 0) AS prosek FROM ocenjivanje WHERE festival_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $festival_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$prosecna_ocena = isset($result['prosek']) ? round($result['prosek'], 1) : 'N/A';
$stmt->close();

// Provera korisničke ocene
$user_rating = null;
$stmt = $conn->prepare("SELECT ocena FROM ocenjivanje WHERE festival_id = ? AND korisnik_id = ?");
$stmt->bind_param("ii", $festival_id, $korisnik_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$user_rating = $result['ocena'] ?? null;
$stmt->close();

// Dobijanje naziva festivala
$stmt = $conn->prepare("SELECT naziv_festivala FROM festivali WHERE festival_id = ?");
$stmt->bind_param("i", $festival_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$naziv_festivala = $result['naziv_festivala'] ?? 'Nepoznat festival';
$stmt->close();
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($naziv_festivala); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            background-color:transparent; /* Background color for visibility */
        }
       /* Star Rating System */
.stars {
    display: flex;
    justify-content: flex-start;
    gap: 10px;
    margin-top: 70px;
    margin-bottom: 25px;
    align-items: center;
}

.stars label {
    position: relative; /* Allows us to position an absolute background */
    width: 50px; /* Star width */
    height: 50px; /* Star height */
    background: url('Slike/staroutline.png') no-repeat center center; /* Outline */
    background-size: contain; /* Ensure it fits well */
    display: block;
    cursor: pointer;
    transition: background 0.3s; /* Smooth transition for color change */
}

.stars input[type="radio"] {
    display: none; /* Hide radio buttons */
}

/* Change star appearance on hover and when selected */
.stars label:hover,
.stars label.selected {
    background-color: yellow; /* Yellow fill color for selected stars */
}

.stars label.selected {
    filter: none; /* Prevents the original fill color from showing */
}

/* Other styles */
h3, h4 {
    margin-bottom: 15px;
    line-height: 1.2;
}

form {
    margin-top: 15px;
}

p {
    margin-top: 20px;
}
h3,h4{
    font-size: 1.2rem;
    color: #2b3b4a;
}
</style>
</head>
<body>
    <h4>Prosečna ocena: <?php echo $prosecna_ocena; ?></h4>

    <?php if ($korisnik_id): ?>
        <h4>Vaša ocena:</h4>
        <form id="ratingForm">
            <div class="stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo ($user_rating == $i) ? 'checked' : ''; ?> />
                    <label for="star<?php echo $i; ?>" data-stars="<?php echo $i; ?>"></label>
                <?php endfor; ?>
            </div>
        </form>
    <?php else: ?>
        <p>Morate biti prijavljeni da biste ocenili festival.</p>
    <?php endif; ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            var $stars = $('.stars label');

            // Handle hover effect
            $stars.on('mouseenter', function() {
                var hoverValue = $(this).data('stars');
                $stars.removeClass('hover');
                for (var i = 1; i <= hoverValue; i++) {
                    $('#star' + i).next().addClass('hover');
                }
            });
            $(document).ready(function() {
                $('.stars label').on('click', function() {
                    var stars = $(this).data('stars');
                    var festival_id = <?php echo $festival_id; ?>;
                    var korisnik_id = <?php echo $korisnik_id; ?>;
                    
                    $.post('includes/save_rating.php', { stars: stars, festival_id: festival_id, korisnik_id: korisnik_id }, function(response) {
                        alert(response.message);
                        location.reload(); // Osvježavanje stranice kako bi se prikazala nova prosečna ocena
                    }, 'json');
                });
            });
            // Clear hover effect when mouse leaves
            $stars.on('mouseleave', function() {
                $stars.removeClass('hover'); 
                var selectedValue = $('input[name="rating"]:checked').val();
                if (selectedValue) {
                    for (var i = 1; i <= selectedValue; i++) {
                        $('#star' + i).next().addClass('selected');
                    }
                }
            });

            // Handle click event
            $stars.on('click', function() {
                var ratingValue = $(this).data('stars');
                $('input[name="rating"]').val(ratingValue);

                // Set selected class based on the rating
                $stars.removeClass('selected');
                for (var i = 1; i <= ratingValue; i++) {
                    $('#star' + i).next().addClass('selected');
                }
            });
        });
    </script>
</body>
</html>