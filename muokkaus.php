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
$ostos_id = "";
$errors = [];


//Tarkistetaan onko lomake lähetetty
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //Tarkistetaan CSRF-tokenin oikeellisuus. 
    require_once "includes/csrf_check.php";

    $nimi = trim($_POST["nimi"]);
    $hinta = $_POST["hinta"];
    $ostos_id = $_POST["ostos_id"];

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
        $tiedot = array(
            ":nimi" => $nimi,
            ":hinta" => $hinta,
            ":id" => $ostos_id,
            ":user_id" => $user_id
        );
        //Luodaan SQL-lauseke, jolla tiedot saadaan syötettyä tietokannan ostostauluun
        $sql = "UPDATE ostos SET nimi=:nimi, hinta=:hinta
                WHERE id=:id AND kayttaja = :user_id"; //Lisätarkistus, että onhan käyttäjä päivitettävän tuotteen omistaja. Eli oikeus muokata sitä.

        //Käytetään valmisteltua lauseketta prepare(), jotta vältytään SQL-injektion riskiltä
        $statement = $connection->prepare($sql);
        //Ajetaan lauseke tietokantaan
        $statement->execute($tiedot);

        if ($statement->rowCount() > 0) {
            /*Tallennetaan sessioon viesti siitä kun uusi ostos on lähetetty. 
            Jos sitä ei tallenneta sinne niin uudelleenohjaus estää viestin näyttämisen.*/ 
            $_SESSION["viesti"] = "Ostoksen tiedot on päivitetty onnistuneesti.";
        } else {
            $_SESSION["virheviesti"] = "Sinulla ei ole oikeuksia muokata tätä tuotetta.";
        }

        /* Uudelleenohjataan selain. Estetään lomakkeen uudelleen lähetys, jos sivu päivitetään.*/ 
        header("Location: ostos.php"); 
        /* Varmistetaan ettei alempaa koodia ajeta kun uudelleenohjaus tehdään */ 
        exit; 
    }
}

if (isset($_GET["id"])) {
    $ostos_id = $_GET["id"];

    $sql = 'SELECT id, nimi, hinta
            FROM ostos
            WHERE id=:id AND kayttaja = :user_id
            LIMIT 1'; 

    $statement = $connection->prepare($sql);
    $statement->bindParam(":id", $ostos_id);
    $statement->bindParam(":user_id", $user_id);
    $statement->execute();

    if ($statement->rowCount() == 1) {
        $result = $statement->fetch(PDO::FETCH_ASSOC);/* https://www.php.net/manual/en/pdostatement.fetch.php */
	    $nimi = $result["nimi"];
		$hinta = $result["hinta"];
	} else {
        $_SESSION["virheviesti"] = "Kyseistä ostosta ei löytynyt tai sinulla ei ole oikeuksia käsitellä sitä. Sinut ohjattiin takaisin ostosten sivulle.";
        /* Uudelleenohjataan selain. Estetään lomakkeen uudelleen lähetys, jos sivu päivitetään.*/ 
        header("Location: ostos.php"); 
        /* Varmistetaan ettei alempaa koodia ajeta kun uudelleenohjaus tehdään */ 
        exit; 
	}
} elseif (empty($ostos_id)) {
    /* Uudelleenohjataan selain. Estetään lomakkeen uudelleen lähetys, jos sivu päivitetään.*/ 
    header("Location: ostos.php"); 
    /* Varmistetaan ettei alempaa koodia ajeta kun uudelleenohjaus tehdään */ 
    exit; 
}

//Asetetaan CSRF-tokeni. 
require_once "includes/csrf_token.php";

include "includes/header.php";
?>

    <main class="container">

    <?php include "includes/login_nav.php";?>
	
	    <h1>Ostokset</h1>

        <span class="text-danger">
            <?php 
                foreach($errors as $error) {
                    foreach($error as $message) {
						echo $message."<br>";
					}
                }
            ?>
        </span>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="token" id="csrf_token" value="<?=$_SESSION['token']?>">
            <input type="hidden" name="ostos_id" value="<?php echo $ostos_id;?>">
            <div class="form-group">
                <label for="nimi">Ostoksen nimi:</label>
                <input type="text" class="form-control" name="nimi" placeholder="Tähän ostoksen nimi" maxlength="150" value="<?php echo htmlspecialchars($nimi ?? "");?>" required>
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
                <input type="number" class="form-control" name="hinta" step="0.01" min="0" max="999999999999.99" placeholder="Tähän ostoksen hinta" value="<?php echo htmlspecialchars($hinta ?? "");?>" required>
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
            <button type="submit" class="btn btn-primary">Päivitä</button><a href="ostos.php" class="btn btn-danger ml-2">Takaisin</a>
        </form>

	</main>

<?php include "includes/footer.php";?>