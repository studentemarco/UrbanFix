<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';

session_start();

// try {
    $email = $_POST["email"] ?? null;
    $pass = $_POST["password"] ?? null;

    if (!$email || !$pass) {
        header("Location: accedi.php?errore=Dati mancanti");
        exit;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json'
            ],
            'content' => json_encode(["email" => $email, "password" => $pass])
        ]
    ]);

    $response = file_get_contents(
        API_URL . "login",
        false,
        $context
    );

    // $response = json_decode($json, true);


    // if ($response === false) {
    //     $err = curl_error($curl);
    //     curl_close($curl);
    //     header("Location: accedi.php?errore=Errore API: $err");
    //     exit;
    // }

    $utente = json_decode($response, true);

    if (!$utente || !isset($utente["success"]) || !$utente["success"]) {
        header("Location: accedi.php?errore=Username o password errati");
        exit;
    }

    // Salvo i dati in sessione
    $_SESSION["name"] = $utente["name"] ?? '';
    $_SESSION["surname"] = $utente["surname"] ?? '';
    $_SESSION["email"] = $utente["email"] ?? '';
    $_SESSION["token"] = $utente["token"] ?? '';

    header("Location: ../segnalazioni.php");
    exit;

// } catch (Throwable $e) {
//     header("Location: accedi.php?errore=Errore durante il login");
//     exit;
// }
?>
