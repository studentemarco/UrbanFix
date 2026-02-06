<?php
    session_start();
    require_once 'config.php';
    
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
    $email = urlencode($_SESSION['email']);

    // $context = stream_context_create([
    //     'http' => [
    //         'method' => 'GET',
    //         'header' =>
    //             "Content-Type: application/json\r\n" .
    //             "Authorization: Bearer " . ($_SESSION['token'] ?? '') . "\r\n"
    //     ]
    // ]);

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


    //var_dump(API_URL . "ruolo/".$email);

    $response = file_get_contents(
        API_URL . "privilegi/".$email,
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

    // Se il codice non è 200, qualcosa è andato storto (opzionale)
    if ($status_code !== 200) {
        // Gestisci altri errori (es. 404, 500) se vuoi
        die("Errore API: Codice di stato " . $status_code);
    }

    // $ruolo = json_decode($response, true);
    // var_dump($response);
    // var_dump($ruolo);
    
    $data_api = json_decode($response, true);
    
    //se l'api risponde che il token non è valido, reindirizzo al logout in modo da eliminare la sessione
    // if (!$response || !isset($response["success"]) || !$response["success"]) {
    //     header("Location: account/logout.php");
    //     exit;
    // }



    // 2. Trasformiamo l'array dei privilegi in un formato facile da controllare
    // Risultato atteso: ['VISUALIZZA_SEGNALAZIONI', 'CREA_SEGNALAZIONE', ...]
    $privilegi = [];
    if (isset($data_api['data']) && is_array($data_api['data'])) {
        $privilegi = array_column($data_api['data'], 'nome');
    }

    // Funzione di utilità per il controllo nella UI
    function può($nome_privilegio, $lista_privilegi) {
        return in_array($nome_privilegio, $lista_privilegi);
    }
?>

<!doctype html>
<html lang="it">
    <head>
        <title>UrbanFix - Segnalazioni</title>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, shrink-to-fit=no"
        />

        <!-- Bootstrap CSS v5.2.1 -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
            crossorigin="anonymous"
        />
        <link rel="stylesheet" href="style.css">
    </head>

    <body>
        <header>
            <!-- place navbar here -->
            <?php $current_page = 'segnalazioni'; include 'navbar.php'; ?>
        </header>
        <main>
            <div class="container my-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1>Segnalazioni</h1>
                        <p class="text-muted">Gestisci le problematiche del tuo territorio.</p>
                    </div>
                    
                    <?php if (può('CREA_SEGNALAZIONE', $privilegi)): ?>
                        <button class="btn btn-primary btn-lg">+ Nuova Segnalazione</button>
                    <?php endif; ?>
                </div>

                <div class="action-bar d-flex flex-wrap gap-2">
                    <?php if (può('VISUALIZZA_SEGNALAZIONI_AREA', $privilegi)): ?>
                        <button class="btn btn-outline-secondary">Filtra per il mio Comune</button>
                    <?php endif; ?>

                    <?php if (può('GESTISCI_STATO_SEGNALAZIONE', $privilegi)): ?>
                        <button class="btn btn-outline-warning">Aggiorna Stati</button>
                    <?php endif; ?>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Buca profonda in Via Roma</h5>
                        <p class="card-text">Segnalata da: utente@mail.it</p>
                        
                        <div class="mt-3 border-top pt-2">
                            <?php if (può('VOTA_SEGNALAZIONE', $privilegi)): ?>
                                <button class="btn btn-sm btn-outline-primary">👍 Vota</button>
                            <?php endif; ?>

                            <?php if (può('COMMENTA_SEGNALAZIONE', $privilegi)): ?>
                                <button class="btn btn-sm btn-outline-secondary">💬 Commenta</button>
                            <?php endif; ?>

                            <?php if (può('SEGNALA_RISOLTA', $privilegi)): ?>
                                <button class="btn btn-sm btn-success float-end">Segna come Risolta</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                

                <?php if (può('GESTISCI_UTENTI', $privilegi) || può('GESTISCI_AMMINISTRAZIONI', $privilegi) || può('GESTISCI_COMUNE', $privilegi)): ?>
                <div class="admin-section">
                    <h4 class="text-danger">Pannello Amministrazione</h4>
                    <div class="d-flex gap-2">
                        <?php if (può('GESTISCI_UTENTI', $privilegi)): ?>
                            <a href="admin_utenti.php" class="btn btn-danger">👤 Gestione Utenti</a>
                        <?php endif; ?>

                        <?php if (può('GESTISCI_COMUNE', $privilegi)): ?>
                            <a href="admin_comune.php" class="btn btn-danger">🏛️ Gestione Comune</a>
                        <?php endif; ?>

                        <?php if (può('GESTISCI_AMMINISTRAZIONI', $privilegi)): ?>
                            <a href="admin_enti.php" class="btn btn-danger">🏢 Gestione Enti</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php 
                    $context = stream_context_create([
                        'http' => [
                            'method' => 'GET',
                            'header' => [
                                "Content-Type: application/json",
                                "Authorization: Bearer " . ($_SESSION['token'] ?? '')
                                // "Origin: " . SERVER_URL 
                            ],
                            'ignore_errors' => true // Permette di leggere il body anche se l'HTTP status è 4xx o 5xx
                        ]
                    ]);

                    $response = file_get_contents(
                        API_URL . "problemi", 
                        false, 
                        $context
                    );

                    $data = json_decode($response, true);

                    // if (!$data || !isset($data["success"]) || !$data["success"]) {
                    //     $messaggio = $data["description"] ?? "Errore durante il recupero dei problemi";
                    //     echo "<div class='alert alert-danger'>$messaggio</div>";
                    // }

                    foreach ($data["data"] as $problema) {
                        //var_dump($problema);
                        include 'segnalazioneTemplate.php';
                        //va a capo
                        echo "<hr/>";
                    }
                ?>

            </div>
        </main>

        <!-- Bootstrap JavaScript Libraries -->
        <script
            src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
            crossorigin="anonymous"
        ></script>

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
            integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
            crossorigin="anonymous"
        ></script>
    </body>
</html>