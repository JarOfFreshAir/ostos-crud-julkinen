<?php 

// Start the session
require_once __DIR__ . "/includes/session.php";

require_once "includes/auth_check.php";
require_once "tietokantayhteys.php";

if (isset($_GET["id"])) {
    $id = $_GET["id"];
    $user_id = $_SESSION["user_id"];

    try {
        // Valmistellaan valintalauseke
        $sql = "SELECT id, kayttaja FROM ostos WHERE id=:id LIMIT 1";

        $kayttajahaku = $connection->prepare($sql);
        
        /* $kayttajahaku->bindParam(":user_id", $user_id); */
        $kayttajahaku->bindParam(":id", $id);
        $kayttajahaku->execute();
        $row = $kayttajahaku->fetch(PDO::FETCH_ASSOC); 
/*         if($kayttajahaku->rowCount() == 1){
           $row = $kayttajahaku->fetch(PDO::FETCH_ASSOC); 
        } */
        if ($row["kayttaja"] == $user_id) {
            $sql = 'DELETE FROM ostos
                    WHERE id=:id
                    LIMIT 1'; 

            $statement = $connection->prepare($sql);
            $statement->bindParam(":id", $id);
            $statement->execute();

            $_SESSION["viesti"] = "Ostos poistettu järjestelmästä.";

            /* Uudelleenohjataan selain. Estetään lomakkeen uudelleen lähetys, jos sivu päivitetään.*/ 
            header("Location: ostos.php"); 
            /* Varmistetaan ettei alempaa koodia ajeta kun uudelleenohjaus tehdään */ 
            exit; 
        } else {
            $_SESSION["virheviesti"] = "Sinulla ei ole oikeuksia poistaa tätä ostosta.";
        }

        
    } catch (Exception $e){
        $_SESSION["virheviesti"] = "Ostosten poistossa tapahtui virhe.";
        //Lokitiedostoon virheilmoitus? $e->getMessage();
        //Ohjata virhesivulle?
        /* Uudelleenohjataan selain. Estetään lomakkeen uudelleen lähetys, jos sivu päivitetään.*/ 
        header("Location: ostos.php"); 
        /* Varmistetaan ettei alempaa koodia ajeta kun uudelleenohjaus tehdään */ 
        exit; 
    }
    
} else {
    $_SESSION["virheviesti"] = "Virheellinen id ostokselle.";
    //Lokitiedostoon virheilmoitus? $e->getMessage();
    //Ohjata virhesivulle?
    /* Uudelleenohjataan selain. Estetään lomakkeen uudelleen lähetys, jos sivu päivitetään.*/ 
    header("Location: ostos.php"); 
    /* Varmistetaan ettei alempaa koodia ajeta kun uudelleenohjaus tehdään */ 
    exit; 
}
?>