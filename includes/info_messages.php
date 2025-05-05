<?php 
    if (!empty($_SESSION["viesti"])) { 
        echo '<div class="alert alert-success" role="alert">' . $_SESSION["viesti"] . '</div>';
        $_SESSION["viesti"] = "";
    }

    if (!empty($_SESSION["virheviesti"])) {
        echo '<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Virhe!</strong> '. $_SESSION["virheviesti"] .'
            </div>';
        $_SESSION["virheviesti"] = "";
    } 
?>