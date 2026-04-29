function initMap() {
    map = L.map('mapSegnalazione').setView([45.4642, 9.19], 8);
    

    //'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png' (alternative tiles)

    L.tileLayer(window.AppConfig.mapTileBase, {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Gestione degli overlay dinamicamente (es. confini, vie, etc.)
    if (window.AppConfig.mapTileOverlays && window.AppConfig.mapTileOverlays.length > 0) {
        window.AppConfig.mapTileOverlays.forEach(function(overlayUrl) {
            L.tileLayer(overlayUrl, {
                maxZoom: 19,
                opacity: 0.7
            }).addTo(map);
        });
    }

    return map;


}