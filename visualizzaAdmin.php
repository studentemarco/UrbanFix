<?php
    session_start();

    if(isset($_SESSION["name"])&& $_SESSION["role"]==="admin"){
        
    } else {
        header("location: index.php?errore=Devi prima effettuare il login come admin");
        exit();
    }

?>
<!doctype html>
<html lang="en">
    <head>
        <title>Admin</title>
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
            <h1>Admin</h1>
            <p>Benvenuto nella pagina di amministrazione.</p>
            <p>Qui puoi gestire gli utenti e le loro autorizzazioni.</p>

            <p>di seguito l'elenco dei permessi:</p>
            <?php
                $roles = json_decode(file_get_contents("./ruoli.json"), true);
                foreach($roles as $role) {
                    if ($role['role'] === 'admin'){
                        echo "<h3>" . $role['role'] . "</h3>";
                        echo "<ul>";
                        foreach($role['permissions'] as $permission) {
                            echo "<li>" . $permission . "</li>";
                        }
                        echo "</ul>";
                    }
                }
            ?>

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
