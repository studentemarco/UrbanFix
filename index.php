<!doctype html>
<html lang="it">
    <head>
        <title>UrbanFix</title>
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
            <?php $current_page = 'index'; include 'navbar.php'; ?>
        </header>
        <main>
            <div class="container d-flex flex-column align-items-center my-5">
                <div class="text-center mb-4">
                    <h1 class="display-4">UrbanFix</h1>
                    <p class="lead">Progetto di monitoraggio e segnalazione problemi urbani</p>
                </div>

                <?php
                    for ($i = 0; $i < 15; $i++) {
                        echo "<br>";
                    }
                ?>
                
                <div class="card shadow-sm w-100 w-md-75 w-lg-50"> <!-- Banner di presentazione -->
                    <div class="card-body d-flex flex-column flex-md-row align-items-center gap-3">
                        <div class="flex-shrink-0 text-primary d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M18 13v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                <path d="M15 3h6v6"></path>
                                <path d="M10 14L21 3"></path>
                            </svg>
                        </div>

                        <div class="flex-grow-1 text-center text-md-start">
                            <h4 class="h5 mb-1">Vuoi saperne di più sul progetto?</h4>
                            <p class="mb-0 text-muted small">Pagina di presentazione con diagrammi (casi d'uso, Gantt, ER, classi) e documentazione di progetto.</p>
                        </div>

                        <div class="text-center text-md-end mt-2 mt-md-0">
                            <a href="<?php echo $baseUrl; ?>/../urbanfix.php" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                                Vai alla presentazione
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ms-2" aria-hidden="true">
                                    <path d="M15 3h6v6"></path>
                                    <path d="M10 14L21 3"></path>
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <br>

                <div class="card shadow-sm w-100 w-md-75 w-lg-50"> <!-- Banner di presentazione -->
                    <div class="card-body d-flex flex-column flex-md-row align-items-center gap-3">
                        <div class="flex-shrink-0 text-primary d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M18 13v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                <path d="M15 3h6v6"></path>
                                <path d="M10 14L21 3"></path>
                            </svg>
                        </div>

                        <div class="flex-grow-1 text-center text-md-start">
                            <h4 class="h5 mb-1">Vuoi vedere il mockup del progetto?</h4>
                            <p class="mb-0 text-muted small"></p>
                        </div>

                        <div class="text-center text-md-end mt-2 mt-md-0">
                            <a href="https://polis-report.lovable.app/" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                                Vai al mockup
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ms-2" aria-hidden="true">
                                    <path d="M15 3h6v6"></path>
                                    <path d="M10 14L21 3"></path>
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                
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