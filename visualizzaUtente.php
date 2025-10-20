<?php
    session_start();
    if(!isset($_SESSION["name"])){
        header("location: index.php?errore=Devi prima effettuare il login");
        exit();
    }

    //var_dump($_SESSION);
    
?>
<!doctype html>
<html lang="en">
    <head>
        <title>Pagina utente</title>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, shrink-to-fit=no"
        />

        <!-- Bootstrap CSS v5.2.1 -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
            crossorigin="anonymous"
        />
    </head>

    <body style="background-color: <?php echo $_SESSION['color']; ?>;">
        
        <h1>Ciao <?php 
            echo $_SESSION["name"]; 

            $hasAccess = false;
            $roles = json_decode(file_get_contents("./ruoli.json"), true);
            //cicla tra gli elementi che sono role con i rispottivi permissions. se il ruolo dell'utente della sessione è uguale a uno di questi ruoli, allora controlla se nell'elenco c'è manage users
            foreach ($roles as $role) {
                if ($role["role"] === $_SESSION["role"] && in_array("manage users", $role["permissions"])) {
                    $hasAccess = true;
                    break;
                }
            }
            echo $hasAccess ? " (hai l'accesso admin)" : " (non hai l'accesso admin)";
            if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin") {
                echo "<a href='visualizzaAdmin.php'>Vai alla pagina admin</a>";

            }elseif (isset($_SESSION["role"]) && $_SESSION["role"] === "utente") {
                echo "<a href='visualizzaUser.php'>Vai alla pagina user</a>";
            }
        ?></h1>
        <br>
        <form action="cambiaColore.php" method="post">
            cambia colore di sfondo con il tuo preferito!
            <input type="color" class="form-control form-control-color" name="bgcolor" value="<?php echo $_SESSION['color']; ?>" title="Scegli il tuo colore di sfondo" />
            <input type="submit" class="btn btn-primary mt-3" value="Cambia colore" />
        </form>
        <br><br><br>
        <a href="logout.php" class="btn btn-danger">Logout</a>

        <script
            src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
            crossorigin="anonymous"
        ></script>

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
            integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
            crossorigin="anonymous"
        ></script>
    </body>
</html>
