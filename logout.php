<?php
require 'core/function.php';
$korisnik = getUserByID();
updateUserStatus($korisnik['korisnik_id'], 'offline');
$_SESSION = [];
session_unset();
session_destroy();
header("Location: login.php");
?>