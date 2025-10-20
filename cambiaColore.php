<?php
session_start();

if (!isset($_POST["bgcolor"]) || empty(trim($_POST["bgcolor"]))) {
    $_POST["bgcolor"] = "#ffffff";
}

$_SESSION["color"] = $_POST["bgcolor"];

// Aggiorna il file utenti.json
$utenti = json_decode(file_get_contents("./utenti.json"));
foreach($utenti as $key => $utente){
    if($utente->username===$_SESSION["name"]){
        $utenti[$key]->bgcolor = $_POST["bgcolor"];
    }
}
file_put_contents("./utenti.json", json_encode($utenti));

header("Location: visualizzaUtente.php");
exit();
?>