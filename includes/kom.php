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
$korisnicko_ime = $_SESSION['username'] ?? '';
$tip_korisnika = $_SESSION['tip_korisnika'] ?? '';

// Ako je korisnik prijavljen, postavljamo njegov tip korisnika
if (!empty($korisnicko_ime) && empty($tip_korisnika)) {
    $stmt = $conn->prepare("SELECT tip_korisnika FROM korisnici WHERE korisnicko_ime = ?");
    $stmt->bind_param("s", $korisnicko_ime);
    $stmt->execute();
    $result = $stmt->get_result();
    $korisnik = $result->fetch_assoc();

    if ($korisnik) {
        $_SESSION['tip_korisnika'] = $korisnik['tip_korisnika'];
        $tip_korisnika = $korisnik['tip_korisnika'];
    }
}

// Obrada brisanja komentara
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_comment']) && $tip_korisnika === 'Administrator') {
    $comment_id = $_POST['comment_id'];

    $stmt = $conn->prepare("DELETE FROM komentari WHERE komentar_id = ?");
    $stmt->bind_param("i", $comment_id);
    
    if ($stmt->execute()) {
        // Pre nego što se bilo šta prikaže, preusmeravamo korisnika
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $_GET['id']);
        exit;
    } else {
        echo "Greška pri brisanju komentara.";
    }
}

// Obrada dodavanja komentara
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['komentar'])) {
    $komentar = $_POST['komentar'] ?? '';
    $festival_id = $_POST['festival_id'] ?? 0;

    if (!empty($korisnicko_ime) && !empty($komentar) && $festival_id > 0) {
        $stmt = $conn->prepare("SELECT korisnik_id FROM korisnici WHERE korisnicko_ime = ?");
        $stmt->bind_param("s", $korisnicko_ime);
        $stmt->execute();
        $result = $stmt->get_result();
        $korisnik = $result->fetch_assoc();

        if ($korisnik) {
            $korisnik_id = $korisnik['korisnik_id'];

            $insert = $conn->prepare("INSERT INTO komentari (korisnik_id, korisnicko_ime, komentar, post_id, kreiran) 
                                      VALUES (?, ?, ?, ?, NOW())");
            $insert->bind_param("issi", $korisnik_id, $korisnicko_ime, $komentar, $festival_id);
            $insert->execute();

            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $festival_id);
            exit;
        }
    } else {
        echo "Svi podaci su obavezni!";
    }
}

// Dohvatanje komentara
$festival_id = $_GET['id'] ?? 0;
$komentari = [];

if ($festival_id > 0) {
    $statement = $conn->prepare("SELECT k.*, u.profilna_slika 
                                 FROM komentari k 
                                 JOIN korisnici u ON k.korisnik_id = u.korisnik_id 
                                 WHERE k.post_id = ? 
                                 ORDER BY k.kreiran DESC");
    $statement->bind_param("i", $festival_id);
    $statement->execute();
    $result = $statement->get_result();
    $komentari = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Komentari</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .comment-box {
            display: flex;
            align-items: flex-start;
            margin-top: 20px;
        }
        .profile-pic {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        textarea {
            width: 100%;
            height: 80px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: none;
        }
        button {
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .comments-list {
            margin-top: 20px;
        }
        .comment {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .comment-content {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
        }
        .comment-content strong {
            display: block;
            margin-bottom: 5px;
        }
        .trash-button {
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 12px;
        }
        .trash-button:hover {
            background-color: #0056b3;
        }
        .blok {
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 12px;
        }
        .blok:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<?php $user=getUserByID() ?>
<!-- Forma za unos komentara -->
<form method="POST" class="comment-box">
    <img src="Slike/<?php echo $user['profilna_slika']?>" alt="Profilna slika" class="profile-pic">
    <div style="flex: 1;">
        <input type="text" name="korisnicko_ime" value="<?= htmlspecialchars($korisnicko_ime) ?>" readonly required style="width: 100%; margin-bottom: 10px; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
        <textarea name="komentar" placeholder="Vaš komentar..." required></textarea>
        <input type="hidden" name="festival_id" value="<?= htmlspecialchars($festival_id) ?>">
        <button type="submit">Pošalji</button>
    </div>
</form>

<!-- Prikaz komentara -->
<div class="comments-list">
    <h2>Komentari:</h2>
    <?php if (!empty($komentari)): ?>
        <?php foreach ($komentari as $komentar): ?>
            <div class="comment">
                <img src="Slike/<?= htmlspecialchars($komentar['profilna_slika']) ?>" alt="Profilna slika" class="profile-pic">
                <div class="comment-content">
                    <strong><?= htmlspecialchars($komentar['korisnicko_ime']) ?></strong>
                    <p><?= htmlspecialchars($komentar['komentar']) ?></p>
                    <small><?= htmlspecialchars($komentar['kreiran']) ?></small>
                    <?php if ($tip_korisnika === 'Administrator'): ?>
                        <form action="./includes/blokiraj_korisnika.php" method="post" style="">
                            <input type="hidden" name="korisnik_id" value="<?= $komentar['korisnik_id'] ?>">
                            <button type="submit" class="blok">
                                🚫 Blokiraj
                            </button>
                        </form>

                        <form method="POST">
                            <input type="hidden" name="comment_id" value="<?= $komentar['komentar_id'] ?>">
                            <button class="trash-button" name="delete_comment">
                                <i class="fas fa-trash-alt"></i> Obriši
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Još nema komentara.</p>
    <?php endif; ?>
</div>
</body>
</html>