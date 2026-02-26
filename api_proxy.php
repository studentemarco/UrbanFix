<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Verifica che l'utente sia loggato
if (!isset($_SESSION['token'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autenticato']);
    exit;
}

// Leggi il metodo HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Leggi l'azione richiesta
$action = $_GET['action'] ?? '';

$token = $_SESSION['token'];

// === GESTIONE DELLE RICHIESTE ===

if ($method === 'GET' && $action === 'comuni') {
    // GET /api/comuni
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            'ignore_errors' => true
        ]
    ]);

    $response = file_get_contents(API_URL . 'comuni', false, $context);
    
    // Passa la risposta così com'è
    echo $response;
    exit;
}

if ($method === 'POST' && $action === 'crea_segnalazione') {
    // POST /api/problemi
    
    // Leggi i dati dal body
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validazione base lato server
    if (!isset($input['titolo'], $input['descrizione'], $input['comune_qid'], $input['coordinate'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Campi obbligatori mancanti']);
        exit;
    }

    // Prepara il body per l'API
    $body = json_encode([
        'titolo' => $input['titolo'],
        'descrizione' => $input['descrizione'],
        'comune_qid' => $input['comune_qid'],
        'coordinate' => $input['coordinate']
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            'content' => $body,
            'ignore_errors' => true
        ]
    ]);

    $response = file_get_contents(API_URL . 'problemi', false, $context);
    
    // Ottieni lo status code
    $status_code = 200;
    if (isset($http_response_header)) {
        preg_match('{HTTP\/\S*\s(\d+)}', $http_response_header[0], $matches);
        $status_code = intval($matches[1]);
    }
    
    http_response_code($status_code);
    echo $response;
    exit;
}

// Endpoint non valido
http_response_code(404);
echo json_encode(['error' => 'Endpoint non trovato']);
?>
