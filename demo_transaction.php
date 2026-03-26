<?php
require_once __DIR__ . '/config.php';
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo Transaction</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; padding: 0 16px; }
        .row { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
        button { padding: 10px 14px; cursor: pointer; }
        pre { background: #f3f3f3; padding: 12px; overflow: auto; }
        input { padding: 8px; width: 320px; }
    </style>
</head>
<body>
    <h1>Demo Transaction: con vs senza</h1>
    <p>Ogni test usa una mail unica. L'errore è forzato volutamente nella seconda query.</p>

    <div class="row">
        <button id="btnNoTx">Test senza transaction</button>
        <button id="btnTx">Test con transaction (PDO)</button>
    </div>

    <div class="row">
        <label for="lastEmail"><b>Ultima email test:</b></label>
        <input id="lastEmail" readonly>
    </div>

    <h3>Risposta endpoint</h3>
    <pre id="outResponse">Premi un pulsante per iniziare...</pre>

    <h3>Stato nel DB</h3>
    <pre id="outState">Nessun test eseguito.</pre>

<script>
const baseApi = '<?= API_URL ?>';

function buildPayload(prefix) {
    const email = `${prefix}_${Date.now()}@demo.local`;
    return {
        email,
        password: 'Password123!',
        nome: 'Demo',
        cognome: 'Transaction'
    };
}

async function runDemo(endpoint, prefix) {
    const payload = buildPayload(prefix);
    document.getElementById('lastEmail').value = payload.email;

    const res = await fetch(baseApi + endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });

    const data = await res.json();
    document.getElementById('outResponse').textContent = JSON.stringify(data, null, 2);

    const stateRes = await fetch(baseApi + 'demo-state', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: payload.email })
    });

    const stateData = await stateRes.json();
    document.getElementById('outState').textContent = JSON.stringify(stateData, null, 2);
}

document.getElementById('btnNoTx').addEventListener('click', () => runDemo('register-demo-no-tx', 'notx'));
document.getElementById('btnTx').addEventListener('click', () => runDemo('register-demo-tx', 'tx'));
</script>
</body>
</html>
