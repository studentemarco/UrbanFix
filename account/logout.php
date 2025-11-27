<?php
    session_start();
    //session_unset(); // svuota tutte le variabili di sessione
    // unset($_SESSION['name']);
    // unset($_SESSION['role']);
    // unset($_SESSION['userid']);
    // unset($_SESSION['userdata']);
    session_destroy();
    header("location: ../index.php");
    exit();
?>