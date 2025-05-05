<?php 
//Jotta nähtäisiin virheilmoitukset selaimessa (poistetaan lopullisesta versiosta)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
Tietokantayhteyden asetukset
https://www.w3schools.com/php/php_constants.asp
*/
define("HOST", "localhost");
define("USERNAME", "tester"); // käyttäjänimi, jolla oikeudet tarvittavaan tietokantaan
define("PASSWORD", "T3st4us"); // ^käyttäjän salasana
define("DBNAME",  "tievi22_ostosesimerkki_login"); // käytettävä tietokanta
define("DSN",   "mysql:host=". HOST .";dbname=". DBNAME .";charset=UTF8");

/* Yritetään ottaa yhteys tietokantaan käyttäen käyttäen PDO:ta
https://www.w3schools.com/php/php_mysql_connect.asp*/
try {
	$connection = new PDO(DSN, USERNAME, PASSWORD);
	// Asetetaan PDO virhemoodi
	$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $error) {
	die("VIRHE: Yhteyttä ei onnistuttu muodostamaan. " . $error->getMessage());
}

?>