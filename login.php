<?php 
include 'core/function.php'; 
if(isset($_SESSION["id"])){
    $id = $_SESSION["id"];
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM korisnici WHERE korisnik_id = $id"));
    $stranica = strtolower($user['tip_korisnika']) . '.php';
    header("Location: $stranica");
}?>
<?php include 'includes/overall/header.php';?>
<style>
.gallery {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    position: relative;
    width: 70%;
}

@keyframes neonGlow {
    0% { box-shadow: 0 0 20px black; }
    33% { box-shadow: 0 0 40px black; }
    66% { box-shadow: 0 0 60px black; }
    100% { box-shadow: 0 0 80px black; }
}


.slide-container {
    position: relative;
    margin: 10px;
    display:none;
    border-radius: 15px;
    padding: 15px;
    color: white;
    margin: auto;
}

.slide-container img {
    width: 700px;
    height: 400px;
    animation: neonGlow 4s infinite alternate;
    border-radius: 15px;
}

button.prev, 
button.next {
    font-size: 24px;
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    cursor: pointer;
    padding: 10px;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
}

button.prev {
    left: 20px;
}

button.next {
    right: 20px;
}

</style>
<div style="display:flex;">
<div class="gallery">
<div class="slide-container">
            <img src="Slike/slika1.jpg" alt="Opis slike 1">
            <button class="prev">❮</button>
            <button class="next">❯</button>
        </div>
        <div class="slide-container">
            <img src="Slike/slika2.jpg" alt="Opis slike 2">
            <button class="prev">❮</button>
            <button class="next">❯</button>
        </div>
        <div class="slide-container">
            <img src="Slike/slika3.jpg" alt="Opis slike 3">
            <button class="prev">❮</button>
            <button class="next">❯</button>
        </div>
    </div>

<script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide-container');
    const prevButton = document.querySelectorAll('.prev');
    const nextButton = document.querySelectorAll('.next');
    slides[0].style.display = 'block'; // Pokaži trenutnu sliku
    function showSlide(index) {
        slides.forEach(slide => slide.style.display = 'none'); // Sakrij sve slike
        slides[index].style.display = 'block'; // Pokaži trenutnu sliku
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
    }

    let autoSlide = setInterval(nextSlide, 3000);

    prevButton.forEach(button => button.addEventListener('click', () => {
        clearInterval(autoSlide);  
        prevSlide();
        autoSlide = setInterval(nextSlide, 3000);  
    }));

    nextButton.forEach(button => button.addEventListener('click', () => {
        clearInterval(autoSlide);  
        nextSlide();
        autoSlide = setInterval(nextSlide, 3000);  
    }));

</script>

<?php include 'includes/aside.php'; ?>
</div>
<?php require 'core/script.php';?>
<?php include 'includes/overall/footer.php'; ?>