<?php
session_start();
require_once 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$email = urlencode($_SESSION['email'] ?? '');
$isEmbed = isset($_GET['embed']) && $_GET['embed'] === '1';

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

$response = file_get_contents(
	API_URL . "privilegi/" . $email,
	false,
	$context
);

$status_code = 0;
if (isset($http_response_header)) {
	preg_match('{HTTP\/\S*\s(\d+)}', $http_response_header[0], $matches);
	$status_code = intval($matches[1] ?? 0);
}

if ($status_code === 401) {
	header("Location: account/logout.php");
	exit;
}

if ($status_code !== 200) {
	die("Errore API: Codice di stato " . $status_code);
}

$data_api = json_decode($response, true);

$privilegi = [];
if (isset($data_api['data']) && is_array($data_api['data'])) {
	$privilegi = array_column($data_api['data'], 'nome');
}

function può($nome_privilegio, $lista_privilegi)
{
	return in_array($nome_privilegio, $lista_privilegi);
}

$problemi = [];
$responseProblemi = file_get_contents(API_URL . "problemi", false, $context);
$dataProblemi = json_decode($responseProblemi, true);

function parseCoordValue($value)
{
	if (is_float($value) || is_int($value)) {
		return floatval($value);
	}

	if (!is_string($value)) {
		return null;
	}

	$normalized = trim(str_replace(',', '.', $value));
	if ($normalized === '' || !is_numeric($normalized)) {
		return null;
	}

	return floatval($normalized);
}

function extractCoordinates($problema)
{
	$latCandidates = [
		$problema['latitudine'] ?? null,
		$problema['latitude'] ?? null,
		$problema['lat'] ?? null
	];

	$lngCandidates = [
		$problema['longitudine'] ?? null,
		$problema['longitude'] ?? null,
		$problema['lng'] ?? null,
		$problema['lon'] ?? null
	];

	foreach ($latCandidates as $candidate) {
		$lat = parseCoordValue($candidate);
		if ($lat === null) {
			continue;
		}

		foreach ($lngCandidates as $lngCandidate) {
			$lng = parseCoordValue($lngCandidate);
			if ($lng === null) {
				continue;
			}
			return [$lat, $lng];
		}
	}

	$coordRaw = $problema['coordinate'] ?? $problema['coordinates'] ?? null;
	if (is_string($coordRaw)) {
		$parts = preg_split('/\s*,\s*/', trim($coordRaw));
		if (is_array($parts) && count($parts) >= 2) {
			$lat = parseCoordValue($parts[0]);
			$lng = parseCoordValue($parts[1]);
			if ($lat !== null && $lng !== null) {
				return [$lat, $lng];
			}
		}
	}

	return [null, null];
}

if (isset($dataProblemi['data']) && is_array($dataProblemi['data'])) {
	foreach ($dataProblemi['data'] as $problema) {
		[$lat, $lng] = extractCoordinates($problema);
		if ($lat === null || $lng === null) {
			continue;
		}

		$problemi[] = [
			'id' => $problema['ID'] ?? null,
			'titolo' => $problema['titolo'] ?? 'Segnalazione',
			'descrizione' => $problema['descrizione'] ?? '',
			'stato' => $problema['stato'] ?? 'non specificato',
			'nome_comune' => $problema['nome_comune'] ?? 'Comune non specificato',
			'latitudine' => $lat,
			'longitudine' => $lng,
		];
	}
}

$totaleProblemi = isset($dataProblemi['data']) && is_array($dataProblemi['data']) ? count($dataProblemi['data']) : 0;
?>

<!doctype html>
<html lang="it">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<title>UrbanFix - Mappa Segnalazioni</title>

	<link
		href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
		rel="stylesheet"
		integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
		crossorigin="anonymous"
	/>
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

	<style>
		#mapSegnalazione {
			height: 72vh;
			min-height: 420px;
			width: 100%;
			border-radius: 12px;
			border: 1px solid #e0ddd6;
		}

		.map-wrapper {
			background: #ffffff;
			border-radius: 14px;
			padding: 12px;
			box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
		}

		.badge-stato {
			border-radius: 999px;
			padding: 0.25rem 0.6rem;
			font-size: 0.78rem;
			text-transform: capitalize;
		}

		.stato-aperto {
			background: #dbeafe;
			color: #1e3a8a;
		}

		.stato-lavorazione {
			background: #fef3c7;
			color: #92400e;
		}

		.stato-risolta {
			background: #dcfce7;
			color: #166534;
		}

		.stato-default {
			background: #e5e7eb;
			color: #374151;
		}
	</style>
</head>

<body>
	<?php if (!$isEmbed): ?>
		<header>
			<?php $current_page = 'segnalazioni'; include 'navbar.php'; ?>
		</header>
	<?php endif; ?>

	<main>
		<div class="container <?php echo $isEmbed ? 'py-3' : 'my-4 mb-5'; ?>">
			<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
				<div>
					<h1 class="mb-1 <?php echo $isEmbed ? 'h4' : ''; ?>">Mappa Segnalazioni</h1>
					<p class="text-muted mb-0">Vista geografica delle segnalazioni inviate dai cittadini.</p>
				</div>
				<span class="badge text-bg-dark"><?php echo count($problemi); ?> segnalazioni in mappa</span>
			</div>

			<?php if ($totaleProblemi > 0 && count($problemi) === 0): ?>
				<div class="alert alert-warning" role="alert">
					Sono presenti segnalazioni, ma nessuna ha coordinate valide per la mappa.
				</div>
			<?php endif; ?>

			<div class="map-wrapper">
				<div id="mapSegnalazione"></div>
			</div>
		</div>
	</main>

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
	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

	<script src="map.js"></script>

	<script>
		const problemi = <?php echo json_encode($problemi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

		// Inizializzazione mappa: stesso setup usato nella pagina segnalazioni
		map = initMap();

		const bounds = [];

		function escapeHtml(value) {
			return String(value)
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/\"/g, '&quot;')
				.replace(/'/g, '&#039;');
		}

		function statoClass(stato) {
			const value = String(stato || '').toLowerCase();
			if (value === 'aperto') return 'stato-aperto';
			if (value === 'in lavorazione') return 'stato-lavorazione';
			if (value === 'risolta') return 'stato-risolta';
			return 'stato-default';
		}

		problemi.forEach((problema) => {
			const lat = Number(problema.latitudine);
			const lng = Number(problema.longitudine);

			if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
				return;
			}

			const marker = L.marker([lat, lng]).addTo(map);
			bounds.push([lat, lng]);

			const popup = `
				<div style="min-width:220px; max-width:280px;">
					<h6 style="margin-bottom:6px;">${escapeHtml(problema.titolo)}</h6>
					<div style="margin-bottom:8px;">
						<span class="badge-stato ${statoClass(problema.stato)}">${escapeHtml(problema.stato)}</span>
					</div>
					<p style="margin:0 0 6px 0; color:#374151;">${escapeHtml(problema.descrizione || 'Nessuna descrizione')}</p>
					<small style="color:#6b7280;">Comune: ${escapeHtml(problema.nome_comune)}</small>
				</div>
			`;

			marker.bindPopup(popup);
		});

		if (bounds.length > 0) {
			map.fitBounds(bounds, { padding: [30, 30] });
		}
	</script>
</body>
</html>
