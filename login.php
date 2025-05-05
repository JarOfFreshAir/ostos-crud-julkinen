<?php

require_once __DIR__ . "/includes/session.php";
 
/* Tarkistetaan onko käyttäjä jo kirjautuneena sisään, jos on niin ohjataan tervetulosivulle.*/
if(isset($_SESSION["user_id"]) && isset($_SESSION["last_activity"]) && (time() - $_SESSION["last_activity"] < 1800)){
    header("location: index.php");
    exit;
}

// Liitetään config tiedosto
require_once "tietokantayhteys.php";
 
// Määritellään muuttujat ja niille tyhjät arvot.
$username = $password = "";
$account_err = "";
$username_err = $password_err = "";
 
// Käsitellään lomakkeen tiedot kun se lähetetään
if($_SERVER["REQUEST_METHOD"] == "POST"){

    //Tarkistetaan CSRF-tokenin oikeellisuus. 
    require_once "includes/csrf_check.php";
 
    // Tarkistetaan onko käyttäjänimi tyhjä.
    if(empty(trim($_POST["username"]))){
        $username_err = "Syötä käyttäjänimi.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Tarkistetaan onko salasana tyhjä.
    if(empty(trim($_POST["password"]))){
        $password_err = "Syötä salasana.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Vahvistetaan kirjautumistiedot
    if(empty($username_err) && empty($password_err)){
        // Valmistellaan valintalauseke
        $sql = "SELECT id, nimi, salasana, rooli FROM kayttajat WHERE nimi = :username LIMIT 1";
        
        if($statement = $connection->prepare($sql)){
			
			// Asetetaan parametri
            $param_username = trim($_POST["username"]);
			
            /* Kiinnitetään muuttujat valmistellun lausekkeen parametreihin*/
            $statement->bindParam(":username", $param_username, PDO::PARAM_STR);       
            
            // Yritetään suorittaa valmisteltu lauseke
            if($statement->execute()){
                /* Tarkistetaan, onko käyttäjänimi olemassa, 
				jos on niin varmistetaan salasana*/
                if($statement->rowCount() == 1){
                    if($row = $statement->fetch()){
						
                        $id = $row["id"];
                        $username = $row["nimi"];
                        $hashed_password = $row["salasana"];

                        /*https://www.php.net/manual/en/function.password-verify.php*/
                        if(password_verify($password, $hashed_password)){
                            
                            session_regenerate_id(true); // Luo uusi session ID

                            /* Tallennetaan tiedot session muuttujiin*/
                            $_SESSION["user_id"] = $id;
                            $_SESSION["role"] = $row["rooli"];
                            $_SESSION["user"] = $username;
                            $_SESSION["last_activity"] = time();

                            // Ohjataan käyttäjä ostos-sivulle
                            header("location: ostos.php");
							exit;
                        } else{
                            // Näytetään virheilmoitus, jos salasana on väärä
                            $account_err = "Syöttämäsi käyttäjänimi tai salasana oli väärä.";
                        }
                    }
                } else{
                    /* Näytetään virheilmoitus mikäli käyttäjänimeä ei löydy.*/
                    $account_err = "Syöttämäsi käyttäjänimi tai salasana oli väärä.";
                }
            } else{
                echo "Valitettavasti jokin meni pieleen. Yritä uudelleen myöhemmin.";
            }
        }
        
        // Sulje lauseke (oikeastaan turha)
        unset($statement);
    }
    
    // Sulje yhteys (oikeastaan turha)
    unset($connection);
}

//Asetetaan CSRF-tokeni. 
require_once "includes/csrf_token.php";
 
include "includes/header.php";
?>
	<main class="container">
	<section>
		<h2>Kirjaudu</h2>

        <!-- SESSION viestit näytetään täällä -->
        <?php require_once __DIR__ . "/includes/info_messages.php";?>
		
		<span class="text-danger">
		<?php echo $account_err;?>
		</span>
		
		<p>Täytä kirjautumistiedot.</p>
		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <!-- Alla on piilotetussa input kentässä csrf-token. Liitetty valueen shorthand echolla 
			eli voisi myös olla myös ihan normaalit php-tagit ja echo $_SESSION['token']; -->
			<input type="hidden" name="token" id="csrf_token" value="<?=$_SESSION['token']?>"> 
			<div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
				<label>Käyttäjänimi</label>
				<input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>">
				<span class="help-block"><?php echo $username_err; ?></span>
			</div>    
			<div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
				<label>Salasana</label>
				<input type="password" name="password" class="form-control">
				<span class="help-block"><?php echo $password_err; ?></span>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Kirjaudu sisään</button>
			</div>
			<p>Jos sinulla ei ole vielä käyttäjätiliä. <a href="register.php">Rekisteröidy täällä</a>.</p>
			<p><a class="btn btn-danger" href="index.php">Takaisin</a></p>
		</form>
	</section>

	</main>
	
<?php include "includes/footer.php";?>