<?php
    session_start();

    if(isset($_SESSION["name"])){
        header("location: ../index.php");
        exit();
    }
    //phpinfo();

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
        <link rel="stylesheet" href="../style.css">
    </head>

    <body>
        <header>
            <?php $current_page = 'account'; include '../navbar.php'; ?>
        </header>
        <main class="container" style="margin-top: 50px;">
            <div class="container mt-5">
            <h1>Pagina di login</h1>
            
            <?php
                include '../message.php';
            ?>

            <div id="err"></div>

            <form action="login.php" method="POST">
                <label class="form-label">Email: </label>
                <input type="email" class="form-control" name="email" placeholder="Inserisci email" required />
                <label class="form-label">Password: </label>
                <input type="password" class="form-control" name="password" placeholder="Inserisci password" required />
                <input type="submit" class="btn btn-primary mt-3" value="Login" />
            </form>
<!-- 
            <form id="loginForm">
                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <button type="submit">Login</button>
            </form> -->

            <!--  -->


            <br><br>

            <h6>Non hai un account? <a href="registrazione.php">Registrati</a></h6>

            <!-- <a href="registrazione.php" class="btn btn-primary mt-3">Registrati</a> -->
            </div>
        </main>

        <!-- <script>
            document.getElementById("loginForm").addEventListener("submit", async function (e) {
                e.preventDefault();

                const email = this.email.value;
                const password = this.password.value;

                const response = await fetch(
                    "https://fluffy-space-telegram-wrvrw59ppq7g294p7-80.app.github.dev/progetto/api/login",
                    {
                        method: "POST",
                        credentials: "include", // IMPORTANTISSIMO (cookie JWT)
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            email: email,
                            password: password
                        })
                    }
                );

                const data = await response.json();

                if (response.ok && data.success) {
                    window.location.href = "../index.php";
                } else {
                    document.getElementById("err").innerHTML = '<h2 id="errore" class="alert alert-danger position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 1050; width: auto; max-width: 90%;"></h2>';
                    document.getElementById("errore").innerText =
                        data.description || "Errore login";
                }
            });
        </script> -->


        <script src="../deleteMessage.js"></script>

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
