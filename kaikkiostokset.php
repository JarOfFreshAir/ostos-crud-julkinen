<?php 

// Start the session
/* Käynnistetään session. Lisätään sivun alkuun ennen kuin käsitellään yhtään HTML:ää. 
Käynnistetään jokaisella sivulla, joilla tarvitaan.*/ 
require_once __DIR__ . "/includes/session.php";

require_once "includes/auth_check.php";

//Tarkistetaan onko käyttäjällä admin oikeudet
if ($_SESSION["role"] != "admin") {
    header("location: index.php");
    exit;
}

require_once "tietokantayhteys.php";

function haeKaikkiOstokset($connection) {

    /* Haetaan tietokannasta ostos-taulusta kaikki tiedot */
    $sql = 'SELECT id, nimi, hinta
            FROM ostos
            ORDER BY hinta DESC'; 

    $statement = $connection->prepare($sql);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);/* fetchAll(PDO::FETCH_ASSOC); https://www.php.net/manual/en/pdostatement.fetchall.php https://www.phptutorial.net/php-pdo/php-fetchall/*/

    return $result;
}

include "includes/header.php";
?>

    <main class="container">

    <?php include "includes/login_nav.php";?>
	
	    <h1>Kaikki ostokset</h1>

        <!-- SESSION viestit näytetään täällä -->
        <?php require_once __DIR__ . "/includes/info_messages.php";?>

        <h2>Lista ostoksista:</h2>

        <table id="ostostaulukko" class="table table-striped">
            <thead>
                <tr>
                    <th>Nimi</th>
                    <th>Hinta</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $ostokset = haeKaikkiOstokset($connection);
                if (!empty($ostokset)) {
                    foreach ($ostokset as $rivi) { 
                        $uusiRivi = '<tr>
                                        <td>'. htmlspecialchars($rivi["nimi"] ?? "Tyhjä") .'</td>
                                        <td>'. htmlspecialchars($rivi["hinta"]  ?? " ") .'</td>
                                    </tr>';

                        echo $uusiRivi;
                    }

                } else {
                    echo "<blockquote>Tällä hetkellä tietokanassa ei ole yhtään ostosta.</blockquote>";
                }
                ?>
            </tbody>
        </table>

        <p><a class="btn btn-danger" href="index.php">Takaisin</a></p>

	</main>

<?php include "includes/footer.php";?>