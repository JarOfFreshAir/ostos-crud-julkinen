<?php 
    //Näitä ei tarvitse asettaa, jos ovat jo palvelimen asetuksessa asetettu. Myös xamppissa voi testata vaihtaa php.ini tiedostoon.
    //ini_set('session.cookie_secure', 1); /*EI voi olla käytössä etäpalvelimella koske ei ole HTTPS yhteyttä : HTTPS-yhteys: Käytä session.cookie_secure-asetusta vain, jos yhteys on suojattu HTTPS*/
    ini_set('session.cookie_httponly', 1); /* Estä JavaScript-pääsy evästeisiin: Käytä session.cookie_httponly-asetusta, jotta JavaScript ei voi lukea session evästeitä ja estät näin XSS-hyökkäykset. */

    // Käynnistetään sessio
    session_start();
?>