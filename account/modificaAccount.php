<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';
session_start();

// Recupera dati dal form
$name = $_POST['name'] ?? null;
$surname = $_POST['surname'] ?? null;
$email = $_POST['email'] ?? null;
$current_password = $_POST['current_password'] ?? null;
$new_password = $_POST['new_password'] ?? null;
$confirm_password = $_POST['confirm_password'] ?? null;

if (!$name || !$surname || !$email || !$current_password) {
    header("Location: account.php?errore=Compila tutti i campi obbligatori");
    exit;
}

if ($new_password && $new_password !== $confirm_password) {
    header("Location: account.php?errore=Le nuove password non corrispondono");
    exit;
}

// Prepara dati per API
$updateData = [
    "name" => $name,
    "surname" => $surname,
    "email" => $email,
    "current_password" => $current_password
];
if ($new_password) {
    $updateData["new_password"] = $new_password;
}

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            "Content-Type: application/json",
            // "Authorization: Bearer " . ($_SESSION['token'] ?? '')
        ],
        'content' => json_encode($updateData),
        'ignore_errors' => true
    ]
]);

$response = file_get_contents(
    API_URL . "updateAccount", // endpoint da adattare secondo API
    false,
    $context
);

$data = json_decode($response, true);

if (!$data || !isset($data["success"]) || !$data["success"]) {
    $messaggio = $data["description"] ?? "Errore durante la modifica";
    header("Location: account.php?errore=" . urlencode($messaggio));
    exit;
}

// Aggiorna dati sessione
$_SESSION["name"] = $name;
$_SESSION["surname"] = $surname;
$_SESSION["email"] = $email;

header("Location: account.php?messaggio=Modifica avvenuta con successo!");
exit;
?>