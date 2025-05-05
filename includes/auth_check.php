<?php 
/* Tarkistetaan onko käyttäjä jo kirjautuneena sisään, jos on niin ohjataan tervetulosivulle. Varmistetaan myös ettei kirjautuminen ole vanhentunut.*/
if(!isset($_SESSION["user_id"]) || (time() - $_SESSION["last_activity"] > 1800)){

    // Viimeisin toiminta sivustolla oli 30 min sitten
    $_SESSION = array();     // Unset $_SESSION variable for the run-time 
    session_destroy();   // Destroy session data in storage
    // Ohjataan uudelleen kirjautumissivulle
    header("location: login.php");
    exit;
} else { // JOS haluttaisiin päivittää vain jos kulunut esim. 1 min eikä ihan joka kerta niin: } else if (time() - $_SESSION["last_activity"] > 60) {
    $_SESSION["last_activity"] = time();
}
?>