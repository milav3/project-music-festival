<?php 
  include_once 'core/function.php';
  
  if(!isset($_SESSION['id'])){
    header("location: login.php");
  }
?>
<head>
  <link rel="icon" type="slike/jpg" href="Slike\logob.png">
</head>
<body>
<?php include 'includes/overall/header.php';?>
<div style="justify-items: center;"> 
  <div class="wrapper">
    <?php include_once 'users-div.php'; ?>
  </div>  
</div>
  <script src="javascript/users.js"></script>
  <?php 
    include 'includes/overall/footer.php';?>
</body>
</html>
