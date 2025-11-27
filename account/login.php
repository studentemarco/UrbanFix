<?php
    session_start();

    $utenti = json_decode(file_get_contents("./utenti.json"));
?>
<!doctype html>
<html lang="en">
    <head>
        <title>Pagina di login</title>
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
        <h1>
            <?php
                //var_dump($utenti);
                try
                {
                    $user = $_POST["username"];
                    $pass = $_POST["password"];

                    $pepper = trim(file_get_contents("./pepper.txt"));

                    foreach($utenti as $key => $utente){
                        $salt = substr($utente->password, 0, 32);
                        if($utente->username===$user && $utente->password===($salt . hash("sha256", ($pass . $salt . $pepper)))){
                            $_SESSION["name"] = $user;
                            $_SESSION["role"] = $utente->role;
                            $_SESSION["userid"] = $utente->userid;
                            //var_dump($_SESSION);
                            header("Location: ../index.php");
                            die();
                        }

                        header("Location: accedi.php?errore=Username o password errati");

                    }
                    header("Location: accedi.php?errore=Username o password errati");
                } catch (Exception $e) {
                    header("Location: accedi.php?errore=Errore durante il login");
                }
            ?>
        </h1>

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
