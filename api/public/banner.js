function bannerSuccess(message) {
    const banner = document.createElement('div');
    banner.className = 'alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3';
    banner.style.zIndex = '1050';
    banner.textContent = message;
    document.body.appendChild(banner);

    // Rimuovi il banner dopo 3.5 secondi
    setTimeout(() => {
        banner.remove();
    }, 3500);
}

function bannerError(message) {
    const banner = document.createElement('div');
    banner.className = 'alert alert-danger position-fixed top-0 start-50 translate-middle-x mt-3';
    banner.style.zIndex = '1050';
    banner.textContent = message;
    document.body.appendChild(banner);

    // Rimuovi il banner dopo 3.5 secondi
    setTimeout(() => {
        banner.remove();
    }, 3500);
}