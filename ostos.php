<?php 

// Start the session
/* Käynnistetään session. Lisätään sivun alkuun ennen kuin käsitellään yhtään HTML:ää. 
Käynnistetään jokaisella sivulla, joilla tarvitaan.*/ 
require_once __DIR__ . "/includes/session.php";

require_once "includes/auth_check.php";
require_once "tietokantayhteys.php";

$user_id = $_SESSION["user_id"];
$nimi = "";
$hinta = "";
$errors = [];

//Tarkistetaan onko lomake lähetetty
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //Tarkistetaan CSRF-tokenin oikeellisuus. 
    require_once "includes/csrf_check.php";

    //Lomakkeen muutujat saadaan POST-muuttujasta
    $nimi = trim($_POST["nimi"]);
    $hinta = $_POST["hinta"];

    //Tarkistukset. Palvelinpuolen validointi
    if (empty($nimi)) {
        /* array_push($errors, "Ostokselle täytyy syöttää nimi."); */
        $errors['nimi'][] = "Ostokselle täytyy syöttää nimi.";
    }

    if (strlen($nimi) > 150) {
        /* array_push($errors, "Ostoksen nimi on liian pitkä."); */
        $errors['nimi'][] = "Ostoksen nimi on liian pitkä.";
    }

    if (!is_numeric($hinta) || empty($hinta)) {
        /* array_push($errors, "Ostoksen hinta on tyhjä tai ei ole oikeassa muodossa."); */
        $errors['hinta'][] = "Ostoksen hinta on tyhjä tai ei ole oikeassa muodossa.";
    }

    if ($hinta >= 1000000000000) {
        /* array_push($errors, "Ostoksen hinta on liian suuri."); */
        $errors['hinta'][] = "Ostoksen hinta on liian suuri.";
    }

    if ($hinta < 0) {
        /* array_push($errors, "Ostoksen hinta on liian suuri."); */
        $errors['hinta'][] = "Hinnan tulee olla positiivinen luku.";
    }

    /* Tarkistetaan onko tullut virheitä. Jos validointi meni läpi ilman virheitä, 
    lähetetään tiedot tietokantaan. */
    if (empty($errors)) {
        try {
            //Luodaan SQL-lauseke, jolla tiedot saadaan syötettyä tietokannan ostostauluun
            $sql = "INSERT INTO ostos (nimi, hinta, kayttaja)
                    VALUES (:nimi, :hinta, :kayttaja)";

            //Käytetään valmisteltua lauseketta prepare(), jotta vältytään SQL-injektion riskiltä
            $statement = $connection->prepare($sql);
            $statement->bindParam(":nimi", $nimi);
            $statement->bindParam(":hinta", $hinta);
            $statement->bindParam(":kayttaja", $user_id);
            //Ajetaan lauseke tietokantaan
            $statement->execute();

            /*Tallennetaan sessioon viesti siitä kun uusi ostos on lähetetty. 
            Jos sitä ei tallenneta sinne niin uudelleenohjaus estää viestin 
            näyttämisen.*/ 
            $_SESSION["viesti"] = "Ostos lisätty onnistuneesti.";

            /* Uudelleenohjataan selain. Estetään lomakkeen uudelleen lähetys, jos sivu päivitetään.*/ 
            header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"])); 
            /* Varmistetaan ettei alempaa koodia ajeta kun uudelleenohjaus tehdään */ 
            exit;   
        } catch (Exception $e) {
            $_SESSION["virheviesti"] = "Ostosta ei pystytty lisäämään järjestelmään. Yritä uudelleen ja 
                                            ota yhteys ylläpitäjään, mikäli virhe toistuu.";
        }

    } else {
        //Voitaisiin tehdä jotain...
    }
}

function haeOstokset($connection, $user_id) {
    /* Haetaan tietokannasta ostos-taulusta kaikki tiedot */
    $sql = 'SELECT id, nimi, hinta
            FROM ostos
            WHERE kayttaja = :user_id
            ORDER BY hinta DESC'; 

    $statement = $connection->prepare($sql);
    $statement->bindParam(":user_id", $user_id);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);/* fetchAll(PDO::FETCH_ASSOC); https://www.php.net/manual/en/pdostatement.fetchall.php https://www.phptutorial.net/php-pdo/php-fetchall/*/

    return $result;
}

//Asetetaan CSRF-tokeni. 
require_once "includes/csrf_token.php";

include "includes/header.php";
?> 

    <main class="container">

    <?php include "includes/login_nav.php";?>
	
	    <h1>Ostokset</h1>

        <p>Tervetuloa <?php echo $_SESSION["user"];?>! Lisää ostoksia alla olevalla lomakkeella.</p>

        <span class="text-danger">
            <?php 
                foreach($errors as $error) {
                    foreach($error as $message) {
						echo $message."<br>";
					}
                }
            ?>
        </span>

        <!-- SESSION viestit näytetään täällä -->
        <?php require_once __DIR__ . "/includes/info_messages.php";?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <!-- Alla on piilotetussa input kentässä csrf-token. Liitetty valueen shorthand echolla 
			eli voisi myös olla myös ihan normaalit php-tagit ja echo $_SESSION['token']; -->
            <input type="hidden" name="token" id="csrf_token" value="<?=$_SESSION['token']?>">

            <div class="form-group">
                <label for="nimi">Ostoksen nimi:</label>
                <input type="text" class="form-control" name="nimi" placeholder="Tähän ostoksen nimi" maxlength="150" value="<?php echo htmlspecialchars($nimi);?>" required>
                <div class="error-viesti text-danger">
                    <?php 
					if (isset($errors['nimi'])) {
						foreach($errors['nimi'] as $message) {
							echo $message."<br>";
						}
					}
					?>
                </div>
            </div>
            <div class="form-group">
                <label for="hinta">Ostoksen hinta:</label>
                <input type="number" class="form-control" name="hinta" step="0.01" min="0" max="999999999999.99" placeholder="Tähän ostoksen hinta" value="<?php echo htmlspecialchars($hinta);?>" required>
                <div class="error-viesti text-danger">
                    <?php 
					if (isset($errors['hinta'])) {
						foreach($errors['hinta'] as $message) {
							echo $message."<br>";
						}
					}
					?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Lähetä</button>
        </form>

        <h2>Lista ostoksista:</h2>

        <table id="ostostaulukko" class="table">
            <thead>
                <tr>
                    <th>Nimi</th>
                    <th>Hinta</th>
                    <th>Poista</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $ostokset = haeOstokset($connection, $user_id);
                
                if (!empty($ostokset)) {
                    foreach ($ostokset as $rivi) { 
                        $uusiRivi = '<tr>
                                        <td><a href="muokkaus.php?id='.$rivi["id"].'">'.htmlspecialchars($rivi["nimi"] ?? "").'</a></td>
                                        <td>'.htmlspecialchars($rivi["hinta"] ?? "").'</td>
                                        <td><a href="poista.php?id='. $rivi["id"] . '" role="button" class="btn btn-danger" onclick="return confirm(\'Haluatko varmasti poistaa ostoksen?\')">X</a></td>
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