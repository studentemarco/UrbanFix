<?php
    session_start();
    if (!isset($_POST["username"]) || !isset($_POST["password"]) || empty(trim($_POST["username"])) || empty(trim($_POST["password"]))) {
        header("Location: registrazione.php?errore=Compila tutti i campi");
        exit();
    }

    $file = "utenti.json";

    if (file_exists($file)){
        $utenti = json_decode(file_get_contents("./utenti.json"));
    } else {
        $utenti = [];
    }

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
    $newUser->role = "user";
    $newUser->userid = random_int(0, 10000);
    //controlla che non esista gia un userid cosi
    foreach ($utenti as $utente) {
        while ($utente->userid === $newUser->userid) {
            $newUser->userid = random_int(0, 10000);
        }
    }

    require "usersdata.php";

    $u = new UserData($newUser->userid);
    $u->salva();

    //$newUser->salt = $salt;

    $utenti[] = $newUser;
    file_put_contents("./utenti.json", json_encode($utenti));

    header("Location: accedi.php?successo=Registrazione avvenuta con successo, effettua il login");
    exit();
?>