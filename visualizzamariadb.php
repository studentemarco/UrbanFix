<?php 
echo "Ciao";
    $connessione = new mysqli("localhost", "utente_phpmyadmin", "password_sicura", "UrbanFix_Users");
    if ($connessione->connect_errno){
        die("Connessione fallita: " . $connessione->connect_error);
    }
    echo "<hr> ok <hr>";

    $interrogaizone = "SELECT * FROM UrbanFix_Users";

    $connessione->query($interrogaizone);
    $risultato = $connessione->query($interrogaizone);
    if(!$risultato){
        die("Query fallita: " . $connessione->error);
    }
    var_dump($risultato);
    while ($row = $risultato->fetch_assoc()) {
        echo "ID: " . $row["id"] . " - Nome: " . $row["nome"] . " - Email: " . $row["email"] . "<br>";
    }
?>