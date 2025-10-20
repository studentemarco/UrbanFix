<?php
    session_start();
    if (!isset($_POST["username"]) || !isset($_POST["password"]) || empty(trim($_POST["username"])) || empty(trim($_POST["password"]))) {
        header("Location: registrazione.php?errore=Compila tutti i campi");
        exit();
    }

    $utenti = json_decode(file_get_contents("./utenti.json"));

    foreach ($utenti as $utente) {
        if ($utente->username === trim($_POST["username"])) {
            error_log("Username già esistente");
            header("Location: registrazione.php?errore=Username gia esistente");
            die();
        }
    }

    $newUser = new stdClass();
    $newUser->username = trim($_POST["username"]);
    $salt = bin2hex(random_bytes(16));
    var_dump($salt);
    $pepper = trim(file_get_contents("./pepper.txt"));
    $newUser->password = $salt . hash("sha256", ((trim($_POST["password"])) . $salt . $pepper));
    $newUser->bgcolor = isset($_POST["bgcolor"]) ? $_POST["bgcolor"] : "#ffffff";
    $newUser->role = "utente";
    //$newUser->salt = $salt;

    $utenti[] = $newUser;
    file_put_contents("./utenti.json", json_encode($utenti));

    header("Location: index.php?successo=Registrazione avvenuta con successo, effettua il login");
    exit();
?>