<nav class="navbar-nav float-right">
    <span>
        <?php 
        if (isset($_SESSION["user_id"]) && isset($_SESSION["role"]) && $_SESSION["role"] == "admin") {
            echo '<a href="kaikkiostokset.php" class="mr-2">Kaikki ostokset</a> ';
        }
        
        if (isset($_SESSION["user_id"])) { 
            echo '<a class="text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Kirjaudu ulos</a>';
        } else {
            echo '<a class="pr-3" href="login.php"><i class="fas fa-sign-in-alt"></i> Kirjaudu</a>';
            echo '<a href="register.php"><i class="fa fa-user-plus"></i> Rekisteröidy</a>';
        } ?>
    </span>
</nav>