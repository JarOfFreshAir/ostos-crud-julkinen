<?php 

//CSRF-suojaustokeni: https://code-boxx.com/simple-csrf-token-php/
// varmista, että session_start(); on ajettu ennen tätä tiedostoa.
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}


/* Jos halutaan mahdollistaa useat lomakkeet auki eri välilehdissä
täytyy sallia sama token koko session keston ajan. Token expire voitaisiin 
ehkä myös jättää pois, jos vain sessio muuten vanhenee. */
/* TÄLLÖIN ehkä:
 if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}*/

/* $_SESSION['token'] = bin2hex(random_bytes(32));
$_SESSION['token-expire'] = time() + 3600; //3600 = 60 * 60 eli 1 h */

?>