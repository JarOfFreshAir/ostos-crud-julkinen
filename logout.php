<?php 
require_once __DIR__ . "/includes/session.php";
 
// Poistetaan kaikki session muuttujat
$_SESSION = array(); // sama kuin vanhempi session_unset();
 
// Tuhotaan sessio.
session_destroy();

// Ohjataan pääsivulle
header("location: index.php");
exit;
?>