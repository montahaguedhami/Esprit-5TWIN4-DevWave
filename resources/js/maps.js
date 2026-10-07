/**
 * AquaSecure - Project Maps Module
 * Handles Leaflet map initialization for project geolocation
 */

/**
 * Initialize a read-only map for displaying a single project location
 * @param {string} elementId - The ID of the map container element
 * @param {number} lat - Latitude
 * @param {number} lng - Longitude
 * @param {string} title - Project title for popup
 */
export function initProjectShowMap(elementId, lat, lng, title) {
    const mapElement = document.getElementById(elementId);
    if (!mapElement || !lat || !lng) return;

    // Initialize map centered on project location
    const map = L.map(elementId, {
        center: [lat, lng],
        zoom: 13,
        zoomControl: true,
        scrollWheelZoom: false,
        dragging: true,
    });

    // Add OpenStreetMap tiles with attribution
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    // Add marker at project location
    const marker = L.marker([lat, lng]).addTo(map);
    
    // Add popup with project title
    if (title) {
        marker.bindPopup(`<strong>${title}</strong>`).openPopup();
    }

    return map;
}

/**
 * Initialize an editable map for creating/editing a project
 * @param {string} elementId - The ID of the map container element
 * @param {string} latInputId - The ID of the latitude input field
 * @param {string} lngInputId - The ID of the longitude input field
 * @param {number} initialLat - Initial latitude (optional)
 * @param {number} initialLng - Initial longitude (optional)
 */
export function initProjectFormMap(elementId, latInputId, lngInputId, initialLat = null, initialLng = null) {
    const mapElement = document.getElementById(elementId);
    const latInput = document.getElementById(latInputId);
    const lngInput = document.getElementById(lngInputId);
    
    if (!mapElement || !latInput || !lngInput) return;

    // Default center: Tunisia (Tunis)
    const defaultLat = initialLat || 36.8065;
    const defaultLng = initialLng || 10.1815;
    const defaultZoom = (initialLat && initialLng) ? 13 : 7;

    // Initialize map
    const map = L.map(elementId, {
        center: [defaultLat, defaultLng],
        zoom: defaultZoom,
        zoomControl: true,
        scrollWheelZoom: true,
        dragging: true,
    });

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    // Add initial marker if coordinates provided
    let marker = null;
    if (initialLat && initialLng) {
        marker = L.marker([initialLat, initialLng], {
            draggable: true
        }).addTo(map);
        
        // Update inputs when marker is dragged
        marker.on('dragend', function(e) {
            const position = e.target.getLatLng();
            latInput.value = position.lat.toFixed(7);
            lngInput.value = position.lng.toFixed(7);
        });
    }

    // Add marker on map click
    map.on('click', function(e) {
        const { lat, lng } = e.latlng;
        
        // Remove existing marker if any
        if (marker) {
            map.removeLayer(marker);
        }
        
        // Add new draggable marker
        marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);
        
        // Update input fields
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);
        
        // Update inputs when marker is dragged
        marker.on('dragend', function(e) {
            const position = e.target.getLatLng();
            latInput.value = position.lat.toFixed(7);
            lngInput.value = position.lng.toFixed(7);
        });
    });

    // Update marker when inputs change
    const updateMarkerFromInputs = () => {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        
        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], {
                    draggable: true
                }).addTo(map);
                
                marker.on('dragend', function(e) {
                    const position = e.target.getLatLng();
                    latInput.value = position.lat.toFixed(7);
                    lngInput.value = position.lng.toFixed(7);
                });
            }
            map.setView([lat, lng], 13);
        }
    };

    latInput.addEventListener('blur', updateMarkerFromInputs);
    lngInput.addEventListener('blur', updateMarkerFromInputs);

    return map;
}

/**
 * Initialize a map displaying multiple projects
 * @param {string} elementId - The ID of the map container element
 * @param {Array} projects - Array of project objects with {id, lat, lng, nom, statut, budget, finance, url}
 */
export function initProjectsMap(elementId, projects) {
    const mapElement = document.getElementById(elementId);
    if (!mapElement || !projects || projects.length === 0) return;

    // Initialize map centered on Tunisia
    const map = L.map(elementId, {
        center: [34.0, 9.0],
        zoom: 6,
        zoomControl: true,
        scrollWheelZoom: true,
    });

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    // Status colors
    const statusColors = {
        'planifie': '#3b82f6',
        'en_cours': '#f59e0b',
        'termine': '#14b8a6',
        'suspendu': '#64748b',
        'annule': '#ef4444',
    };

    // Add markers for each project
    const bounds = [];
    projects.forEach(project => {
        if (project.lat && project.lng) {
            // Custom icon based on status
            const color = statusColors[project.statut] || '#06b6d4';
            const icon = L.divIcon({
                html: `<div style="background-color: ${color}; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>`,
                iconSize: [24, 24],
                className: 'custom-marker'
            });

            const marker = L.marker([project.lat, project.lng], { icon }).addTo(map);
            
            // Popup content
            const pourcentage = project.budget > 0 ? ((project.finance / project.budget) * 100).toFixed(0) : 0;
            const popupContent = `
                <div style="min-width: 200px;">
                    <h3 style="margin: 0 0 8px 0; font-weight: bold; font-size: 14px; color: #0c4a6e;">
                        ${project.nom}
                    </h3>
                    <div style="margin-bottom: 8px; font-size: 12px; color: #64748b;">
                        <strong>Statut:</strong> ${getStatusLabel(project.statut)}<br>
                        <strong>Budget:</strong> ${formatNumber(project.budget)} DT<br>
                        <strong>Financé:</strong> ${formatNumber(project.finance)} DT (${pourcentage}%)
                    </div>
                    <a href="${project.url}" style="display: inline-block; padding: 6px 12px; background: linear-gradient(135deg, #06b6d4, #3b82f6); color: white; text-decoration: none; border-radius: 6px; font-size: 12px; font-weight: 600;">
                        Voir le projet →
                    </a>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            bounds.push([project.lat, project.lng]);
        }
    });

    // Fit map to show all markers
    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }

    return map;
}

// Helper functions
function getStatusLabel(statut) {
    const labels = {
        'planifie': 'Planifié',
        'en_cours': 'En cours',
        'termine': 'Terminé',
        'suspendu': 'Suspendu',
        'annule': 'Annulé',
    };
    return labels[statut] || statut;
}

function formatNumber(num) {
    return new Intl.NumberFormat('fr-TN', { style: 'decimal' }).format(num);
}

// Make functions globally available
window.initProjectShowMap = initProjectShowMap;
window.initProjectFormMap = initProjectFormMap;
window.initProjectsMap = initProjectsMap;
