<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php'; // Assicurati che API_URL sia definito qui

session_start();

$nome = $_POST['name'] ?? null;
$cognome = $_POST['surname'] ?? null;
$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;

if (!$nome || !$cognome || !$email || !$password) {
    header("Location: registrati.php?errore=Tutti i campi sono obbligatori");
    exit;
}

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            "Content-Type: application/json"
            // "Origin: " . SERVER_URL 
        ],
        'content' => json_encode([
            "nome" => $nome,
            "cognome" => $cognome,
            "email" => $email,
            "password" => $password
        ]),
        'ignore_errors' => true // Permette di leggere il body anche se l'HTTP status è 4xx o 5xx
    ]
]);

$response = file_get_contents(
    API_URL . "register", 
    false, 
    $context
);

$data = json_decode($response, true);

if (!$data || !isset($data["success"]) || !$data["success"]) {
    $messaggio = $data["description"] ?? "Errore durante la registrazione";
    header("Location: registrati.php?errore=" . urlencode($messaggio));
    exit;
}

header("Location: accedi.php?messaggio=" . urlencode("Registrazione completata! Ora puoi accedere."));

exit;
?>