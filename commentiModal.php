<?php
// commentiModal.php
// Mostra i commenti e la chat per una segnalazione

session_start();
require_once 'config.php'; // o il path corretto
// require_once '../auth.php'; // se serve autenticazione

ini_set('display_errors', 1);
    error_reporting(E_ALL);

$problema_id = isset($_GET['problema_id']) ? intval($_GET['problema_id']) : 0;
if ($problema_id <= 0) {
    echo '<div class="alert alert-danger">ID segnalazione non valido.</div>';
    exit;
}

// Carica i commenti dall'api


    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                "Content-Type: application/json",
                "Authorization: Bearer " . ($_SESSION['token'] ?? '')
            ],
            'ignore_errors' => true
        ]
    ]);

    // var_dump($context);
    // var_dump(API_URL . "problemi/".$problema_id."/commenti");

    $response = file_get_contents(
        API_URL . "problemi/".$problema_id."/commenti",
        false,
        $context
    );

    $status_code = 0;
    if (isset($http_response_header)) {
        // La prima riga è sempre "HTTP/1.1 200 OK" o simile
        preg_match('{HTTP\/\S*\s(\d+)}', $http_response_header[0], $matches);
        $status_code = intval($matches[1]);
    }

    // CONTROLLO DIRETTO SUL CODICE 401
    if ($status_code === 401) {
        // Token scaduto o non valido: vai al logout
        header("Location: account/logout.php");
        exit;
    }

    // var_dump(
    //     $response,
    //     $status_code
    // );

    $commenti = [];
    if ($status_code === 200) {
        $decoded = json_decode($response, true);
        if (is_array($decoded)) {
            // Se è un array associativo singolo, lo metto in un array
            if (isset($decoded['autore']) && isset($decoded['testo'])) {
                $commenti[] = $decoded;
            } else {
                $commenti = $decoded;
            }
        }
    }

?>
</div>
<?php
if (!is_array($commenti) || empty($commenti['data']) || !is_array($commenti['data'])) {
    echo '<div class="text-muted">Nessun commento.</div>';
} else {
    foreach ($commenti['data'] as $c) {
        if (is_array($c)) {
            echo '<div style="margin-bottom:8px; padding:8px; border-bottom:1px solid #eee;">';
            echo '<strong>' . htmlspecialchars($c['Utenti_email']) . '</strong><br>';
            echo '<span>' . htmlspecialchars($c['descrizione']) . '</span><br>';
            echo '<span class="text-muted" style="font-size:0.9em;">' . htmlspecialchars($c['time']) . '</span>';
            echo '</div>';
        }
    }
}
?>
</div>
<form method="post" action="commentiModal.php?problema_id=<?= $problema_id ?>" class="input-group">
    <input type="text" name="testo" class="form-control" placeholder="Scrivi un commento..." required>
    <button class="btn btn-primary" type="submit">Invia</button>
</form>
<?php
// Gestione invio commento (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['testo'])) {
    $testo = trim($_POST['testo']);
    $autore = $_SESSION['nome_utente'] ?? 'Anonimo';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn->connect_error) {
        $stmt = $conn->prepare("INSERT INTO commenti (problema_id, autore, testo, data_invio) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('iss', $problema_id, $autore, $testo);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        // Redirect per evitare doppio invio
        header('Location: commentiModal.php?problema_id=' . $problema_id);
        exit;
    }
}
?>
