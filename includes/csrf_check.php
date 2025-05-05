<?php 

//Tarkistetaan onko CSRF-token asetettu ja sama kuin SESSION-muuttujassa.
if (!isset($_SESSION['token']) || !isset($_POST["token"]) || !hash_equals($_SESSION['token'], $_POST['token'])) {
    exit("CSRF-token error.");
}


/* Jos halutaan mahdollistaa useat lomakkeet auki eri välilehdissä
täytyy sallia sama token koko session keston ajan. Token expire voitaisiin 
ehkä myös jättää pois, jos vain sessio muuten vanhenee. */
/* TÄLLÖIN jätettäisiin pois 
unset($_SESSION['token']);
unset($_SESSION['token-expire']);
SEKÄ ehkä token-expire tarkistus.*/

/* if (hash_equals($_SESSION['token'], $_POST['token'])) {
    exit("Token did not match or token expired. Please reload form.");
} */

/* if (hash_equals($_SESSION['token'], $_POST['token']) || time() >= $_SESSION['token-expire']) {
    exit("Token did not match or token expired. Please reload form.");
} */

//Tyhjennetään tokenin tiedot
//Varmista ettei csrf_token.php ajeta ennen csrf_check.php tiedostoa
//muuten token nollautuu 
/* unset($_SESSION['token']);
unset($_SESSION['token-expire']); */

?>