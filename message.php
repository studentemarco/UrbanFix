<?php
    if(isset($_GET["errore"])){
        ?>
        <h2 class="alert alert-danger position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 1050; width: auto; max-width: 90%;">
            <?=htmlspecialchars($_GET["errore"])?>
        </h2>
        <?php
    }
    if(isset($_GET["successo"])){
        ?>
        <h2 class="alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 1050; width: auto; max-width: 90%;">
            <?=htmlspecialchars($_GET["successo"])?>
        </h2>
        <?php
    }
?>