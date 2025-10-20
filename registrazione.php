<?php
    session_start();

    if(isset($_SESSION["name"])){
        header("location: visualizzaUtente.php");
        exit();
    }

?>
<!doctype html>
<html lang="en">
    <head>
        <title>Pagina di registrazione</title>
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

    <body>
        <main>
            <div class="container mt-5">
            <h1>Pagina di registrazione</h1>
            
                <?php
                    if(isset($_GET["errore"])){
                        ?>
                        <h2 class="alert alert-danger"><?=$_GET["errore"]?></h2>
                        <?php
                    }
                ?>
            <form action="salvaRegistrazione.php" method="post">
                <label class="form-label">Username: </label>
                <input type="text" class="form-control" name="username" placeholder="Inserisci username" maxlength="50"/>
                <label class="form-label">Password: </label>
                <input type="password" class="form-control" name="password" placeholder="Inserisci password" />
                <label class="form-label">Colore di sfondo: </label>
                <input type="color" class="form-control form-control-color" name="bgcolor" value="#fffccdff" title="Scegli il tuo colore di sfondo" />
                <input type="submit" class="btn btn-primary mt-3" value="Registrati" />
            </form>

            <br> 

            <a href="index.php" class="btn btn-primary">Torna alla homepage</a>

            </div>
        </main>

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
