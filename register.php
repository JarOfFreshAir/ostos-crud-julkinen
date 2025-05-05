<?php 

require_once __DIR__ . "/includes/session.php";
/* Liitetään config tiedosto*/
require_once "tietokantayhteys.php";
 
/* Määritellään muuttujat ja niille tyhjät arvot.*/
$username = $password = $confirm_password = "";
$errors = [];
 
/*Käsitellään lomakkeen tiedot kun lomake lähetetään*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //Tarkistetaan CSRF-tokenin oikeellisuus. 
    require_once "includes/csrf_check.php";

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
 
    /* Tarkistetaan käyttäjänimi */
    if(empty($username)){
        $errors["username"] = "Kirjoita käyttäjänimi.";
    } else{
        /* Valmistellaan valintalauseke */
        $sql = "SELECT id FROM kayttajat WHERE nimi = :username";
        
        $statement = $connection->prepare($sql);      
        /* Kiinnitetään muuttujat valmistellun lausekkeen parametreihin */
        $statement->bindParam(":username", $username, PDO::PARAM_STR); //voidaan jättää pois tuo pdo::param...
        
        /* Yritetään suorittää valmisteltua lauseketta. 
        Tarkistetaan onko käyttäjänimi jo varattu.*/
        if($statement->execute()){
            if($statement->rowCount() == 1){
                $errors["username"] = "Tämä käyttäjänimi on jo varattu.";
            }
        } else{
            $errors["database"] = "Valitettavasti jokin meni pieleen. Yritä uudelleen myöhemmin.";
        }
           
        // Suljetaan lauseke
        unset($statement);
    }
    
    // Vahvistetaan salasana
    if(empty($password)){
        $errors["password"] = "Syötä salasana.";    
    } elseif(strlen($password) < 6){
        $errors["password"] = "Salasanassa pitää olla vähintään 6 merkkiä.";
    }
    
    // Vahvistetaan uudelleen kirjoitettu salasana
    if(empty($confirm_password)){
        $errors["confirm_password"] = "Vahvista salasana."; 
    } else{
        if(empty($errors["password"]) && ($password != $confirm_password)){
            $errors["confirm_password"] = "Salasana ei täsmännyt.";
        }
    }
    
    /* Tarkista syöttevirheet ennen kuin tiedot syötetään tietokantaan*/
    if(empty($errors)){
        
        // Valmistellaan syötelauseke
        $sql = "INSERT INTO kayttajat (nimi, salasana, rooli) VALUES (:username, :password, :role)";
        
        //PÄIVITÄ TÄMÄ
        if($statement = $connection->prepare($sql)){

            // Asetetaan parametrit
            /* "Hajautetaan" salasana*/
            /* https://www.php.net/manual/en/function.password-hash.php */
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = $_POST["role"]; /* jos on tyhjä niin tulee default arvona "perus". Voitaisiin myös ylempänä tehdä validointi, että on varmasti asetettu. Validointi olisi hyvä tehdä.*/

            // Kiinnitetään muuttujat valmistellun lausekkeen parametreihin
            $statement->bindParam(":username", $username, PDO::PARAM_STR);
            $statement->bindParam(":password", $hashed_password, PDO::PARAM_STR);
            $statement->bindParam(":role", $role, PDO::PARAM_STR);
            
            // Yritetään suorittaa valmisteltu lauseke
            if($statement->execute()){
                // Uudelleenohjataan kirjautumissivulle
                header("location: login.php");
                exit;
            } else{
                $errors["database"] = "Valitettavasti jokin meni pieleen. Yritä uudelleen myöhemmin.";
            }
        }
         
        // Suljetaan lauseke (oikeastaan turha)
        unset($statement);
    }
    
    // Suljetaan yhteys (oikeastaan turha)
    unset($connection);
}

//Asetetaan CSRF-tokeni. 
require_once "includes/csrf_token.php";

include "includes/header.php";
?>
	<main class="container">

	<section>
		<h2>Rekisteröinti</h2>
		<p>Täytä alla oleva lomake luodaksesi käyttäjätilin.</p>

        <span class="text-danger">
            <?php 
                foreach($errors as $error) {
					echo $error."<br>";
                }
            ?>
        </span>

		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="token" id="csrf_token" value="<?=$_SESSION['token']?>">
			<div class="form-group <?php echo (!empty($errors["username"])) ? 'has-error' : ''; ?>">
				<label>Käyttäjänimi</label>
				<input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>">
				<span class="help-block text-danger"><?php echo isset($errors["username"]) ? $errors["username"] : ""; ?></span>
			</div>  
            <div class="form-group">
				<label>Valitse käyttäjän rooli</label>
				<select name="role">
					<option value="perus">Peruskäyttäjä</option>
					<option value="admin">Admin</option>
				</select>
			</div>	  
			<div class="form-group <?php echo (!empty($errors["password"])) ? 'has-error' : ''; ?>">
				<label>Salasana</label>
				<input type="password" name="password" class="form-control" value="<?php echo htmlspecialchars($password); ?>">
				<span class="help-block text-danger"><?php echo isset($errors["password"]) ? $errors["password"] : ""; ?></span>
			</div>
			<div class="form-group <?php echo (!empty($errors["confirm_password"])) ? 'has-error' : ''; ?>">
				<label>Toista salasana</label>
				<input type="password" name="confirm_password" class="form-control" value="<?php echo htmlspecialchars($confirm_password); ?>">
				<span class="help-block text-danger"><?php echo isset($errors["confirm_password"]) ? $errors["confirm_password"] : ""; ?></span>
			</div>
			<div class="form-group">
				<input type="submit" value="Rekisteröidy" class="btn btn-primary">
			</div>
			<p>Onko sinulla jo tili? <a href="login.php">Kirjaudu sisään täällä</a>.</p>
			<p><a class="btn btn-danger" href="index.php">Takaisin</a></p>
		</form>
	</section>

	</main>
	
<?php include "includes/footer.php";?>