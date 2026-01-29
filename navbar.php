<?php
// Determine base URL dynamically (supports both "/progetto/UrbanFix" and "/UrbanFix")
$script = $_SERVER['SCRIPT_NAME'] ?? ($_SERVER['REQUEST_URI'] ?? '/');
$baseUrl = '';

// prefer explicit /progetto/UrbanFix if present
$pos = strpos($script, '/progetto/UrbanFix');
if ($pos !== false) {
    $baseUrl = substr($script, 0, $pos + strlen('/progetto/UrbanFix'));
} else {
    // otherwise look for /UrbanFix
    $pos = strpos($script, '/UrbanFix');
    if ($pos !== false) {
        $baseUrl = substr($script, 0, $pos + strlen('/UrbanFix'));
    } else {
        // fallback to dirname (useful if deployed at webroot)
        $dir = rtrim(dirname($script), '/\\');
        $baseUrl = $dir === '' ? '' : $dir;
    }
}

$baseUrl = rtrim($baseUrl, '/');
?>
<nav class="navbar navbar-expand-lg navbar-light border-bottom fixed-top" style="background-color: #f9f7f3;" id="mainNavbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo $baseUrl; ?>/index.php">UrbanFix</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <?php
                if (session_status() === PHP_SESSION_NONE) session_start();
                $is_logged = isset($_SESSION['token']) && !empty($_SESSION['token']);
            ?>
            <div class="navbar-nav">
            <a class="nav-link <?php if ($current_page == 'index') echo 'active'; ?>" href="<?php echo $baseUrl; ?>/index.php">Home</a>
            <?php if ($is_logged) : ?> 
                <a class="nav-link <?php if ($current_page == 'segnalazioni') echo 'active'; ?>" href="<?php echo $baseUrl; ?>/segnalazioni.php">Segnalazioni</a>
            <?php endif; ?>
            </div>

            <?php if ($current_page != 'account') : ?>  


                <div class="ms-auto d-flex align-items-center">
                    <?php if (!$is_logged): ?>
                        <a href="account/accedi.php" class="btn btn-outline-primary btn-sm me-2">Login</a>
                        <a href="account/registrazione.php" class="btn btn-primary btn-sm">Registrati</a>
                    <?php else:
                        $displayName = htmlspecialchars($_SESSION['name'] ?? $_SESSION['surname'] ?? $_SESSION['email'] ?? 'Utente', ENT_QUOTES, 'UTF-8');
                    ?>
                        <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-circle me-2" viewBox="0 0 16 16" aria-hidden="true">
                            <path d="M13.468 12.37C12.758 11.226 11.48 10.5 8 10.5s-4.758.726-5.468 1.87A6.987 6.987 0 0 0 8 15a6.987 6.987 0 0 0 5.468-2.63z"/>
                            <path fill-rule="evenodd" d="M8 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                            <path fill-rule="evenodd" d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1z"/>
                            </svg>
                            <span><?php echo $displayName; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="account/account.php">Modifica account</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                            <form action="account/logout.php" method="post" class="m-0">
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                            </li>
                        </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div style="margin-top: 70px;"></div>

<footer>
    <div class="fixed-bottom border-top" style="background-color: #f9f7f3;">
        <div class="container py-2 d-flex justify-content-center align-items-center gap-2 small text-muted">
            <span>© <?php echo date('Y'); ?> UrbanFix - All rights reserved</span>
            <span>&middot;</span>
            <a href="https://github.com/studentemarco/UrbanFix" target="_blank" rel="noopener noreferrer" class="link-secondary text-decoration-none d-inline-flex align-items-center" aria-label="GitHub">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-github" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0 0 16 8c0-4.42-3.58-8-8-8z"/>
                </svg>
                <span class="ms-1">GitHub</span>
            </a>
        </div>
    </div>
</footer>