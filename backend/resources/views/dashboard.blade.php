<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Family Tracker - Dashboard</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Material Symbols Outlined -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=delete,edit,refresh,update,vpn_key_alert" />
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body, html {
            height: 100%;
            width: 100%;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        #map {
            height: 100vh;
            width: 100vw;
        }
        
        /* Info Drawer Styles */
        #info-drawer {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.2);
            transition: right 0.3s ease-in-out;
            z-index: 1000;
            overflow-y: auto;
        }
        
        #info-drawer.open {
            right: 0;
        }
        
        #drawer-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.3);
            z-index: 999;
            display: none;
        }
        
        #drawer-overlay.visible {
            display: block;
        }
        
        .drawer-header {
            padding: 20px;
            background: #2c3e50;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        
        .drawer-header-content {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }
        
        .drawer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 3px solid white;
            overflow: hidden;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            flex-shrink: 0;
        }
        
        .drawer-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .drawer-header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }
        
        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.3s ease;
            transform: rotate(0deg);
        }
        
        .close-btn:hover {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
            transform: rotate(180deg);
        }
        
        .drawer-content {
            padding: 20px;
        }
        
        .info-section {
            margin-bottom: 24px;
        }
        
        .info-section h3 {
            font-size: 14px;
            font-weight: 600;
            color: #7f8c8d;
            text-transform: uppercase;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 500;
            color: #34495e;
        }
        
        .info-value {
            color: #7f8c8d;
            text-align: right;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-stale {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .battery-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .battery-bar {
            width: 60px;
            height: 12px;
            border: 2px solid #34495e;
            border-radius: 2px;
            position: relative;
            overflow: hidden;
        }
        
        .battery-fill {
            height: 100%;
            transition: width 0.3s, background-color 0.3s;
        }
        
        .battery-fill.high {
            background: #27ae60;
        }
        
        .battery-fill.medium {
            background: #f39c12;
        }
        
        .battery-fill.low {
            background: #e74c3c;
        }
        
        /* Manual Update Button Styles */
        .update-button {
            width: 100%;
            padding: 12px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, opacity 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .update-button:hover:not(:disabled) {
            background: #2980b9;
        }
        
        .update-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .update-button .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .update-message {
            margin-top: 12px;
            padding: 10px;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
            display: none;
        }
        
        .update-message.success {
            background: #d4edda;
            color: #155724;
            display: block;
        }
        
        .update-message.error {
            background: #f8d7da;
            color: #721c24;
            display: block;
        }
    </style>
</head>
<body>
    <!-- Floating User Menu -->
    <div id="floating-user-menu" style="position: fixed; top: 20px; right: 20px; z-index: 1001;">
        <!-- User Avatar Button -->
        <div id="user-avatar-btn" style="
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #2196F3;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: transform 0.2s, box-shadow 0.2s;
            color: white;
            font-size: 24px;
            font-weight: 400;
        " onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" 
           onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">
            👤
        </div>
        
        <!-- Dropdown Menu -->
        <div id="user-dropdown" style="
            position: absolute;
            top: 60px;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            overflow: hidden;
        ">
            <!-- User Info Header -->
            <div style="padding: 14px 16px; background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                <div style="font-weight: 500; color: #2c3e50; font-size: 14px;">{{ Auth::user()->username }}</div>
                <div style="font-size: 12px; color: #6c757d;">{{ Auth::user()->email }}</div>
            </div>
            
            <!-- Menu Items -->
            <div style="padding: 8px 0;">
                <div id="profile-settings-btn" style="
                    padding: 12px 16px;
                    cursor: pointer;
                    transition: background 0.2s;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    font-size: 14px;
                    color: #495057;
                " onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                    <span>⚙️</span>
                    <span>Profile Settings</span>
                </div>
                
                <div style="height: 1px; background: #e9ecef; margin: 4px 0;"></div>
                
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" style="
                        width: 100%;
                        padding: 12px 16px;
                        background: none;
                        border: none;
                        cursor: pointer;
                        transition: background 0.2s;
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        font-size: 14px;
                        color: #dc3545;
                        text-align: left;
                    " onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                        <span>🚪</span>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div id="map"></div>
    
    <!-- Drawer Overlay -->
    <div id="drawer-overlay"></div>
    
    <!-- Info Drawer -->
    <div id="info-drawer">
        <div class="drawer-header">
            <div class="drawer-header-content">
                <div class="drawer-avatar" id="drawer-avatar">
                    <!-- Avatar will be inserted here -->
                </div>
                <h2 id="drawer-device-name">Device Info</h2>
            </div>
            <button class="close-btn" id="close-drawer">&times;</button>
        </div>
        <div class="drawer-content">
            <div class="info-section">
                <h3>Location</h3>
                <div class="info-item">
                    <span class="info-label">Latitude</span>
                    <span class="info-value" id="drawer-latitude">-</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Longitude</span>
                    <span class="info-value" id="drawer-longitude">-</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Accuracy</span>
                    <span class="info-value" id="drawer-accuracy">-</span>
                </div>
            </div>
            
            <div class="info-section">
                <h3>Device Status</h3>
                <div class="info-item">
                    <span class="info-label">Battery Level</span>
                    <span class="info-value">
                        <div class="battery-indicator">
                            <span id="drawer-battery-text">-</span>
                            <div class="battery-bar">
                                <div class="battery-fill" id="drawer-battery-bar"></div>
                            </div>
                        </div>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Signal Strength</span>
                    <span class="info-value" id="drawer-signal">-</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value" id="drawer-status">-</span>
                </div>
            </div>
            
            <div class="info-section">
                <h3>Last Update</h3>
                <div class="info-item">
                    <span class="info-label">Timestamp</span>
                    <span class="info-value" id="drawer-timestamp">-</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Time Ago</span>
                    <span class="info-value" id="drawer-time-ago">-</span>
                </div>
            </div>
            
            <div class="info-section" id="optional-status-section" style="display: none;">
                <h3>Optional Status</h3>
                <div class="info-item" id="microphone-item" style="display: none;">
                    <span class="info-label">Microphone</span>
                    <span class="info-value" id="drawer-microphone">-</span>
                </div>
                <div class="info-item" id="camera-item" style="display: none;">
                    <span class="info-label">Camera</span>
                    <span class="info-value" id="drawer-camera">-</span>
                </div>
                <div class="info-item" id="recording-item" style="display: none;">
                    <span class="info-label">Recording</span>
                    <span class="info-value" id="drawer-recording">-</span>
                </div>
            </div>
            
            <div class="info-section">
                <h3>Actions</h3>
                <button class="update-button" id="manual-update-btn">
                    <span id="update-btn-text">Request Update</span>
                </button>
                <div class="update-message" id="update-message"></div>
            </div>
        </div>
    </div>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    
    <script>
        // Initialize map centered on Mecca
        const map = L.map('map').setView([21.4225, 39.8262], 13);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        // Store markers by deviceId
        const markers = {};
        let isFirstLoad = true;
        
        // Load avatar icons
        let avatarIcons = {};
        fetch('/api/devices/avatar-icons')
            .then(res => res.json())
            .then(data => {
                if (data.icons) {
                    data.icons.forEach(icon => {
                        avatarIcons[icon.id] = icon;
                    });
                    
                    // Load user avatar after icons are loaded
                    loadUserAvatar();
                }
            })
            .catch(err => console.error('Failed to load avatar icons:', err));
        
        // Color palette for different devices
        const colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8',
            '#F7DC6F', '#BB8FCE', '#85C1E2', '#F8B739', '#52B788'
        ];
        
        // Map device IDs to consistent color indices
        const deviceColorMap = {};
        let colorIndex = 0;
        
        // Get color for device (consistent across updates)
        function getDeviceColor(deviceId) {
            if (!deviceColorMap[deviceId]) {
                deviceColorMap[deviceId] = colorIndex++;
            }
            return colors[deviceColorMap[deviceId] % colors.length];
        }
        
        // Create custom icon with avatar
        function createAvatarIcon(avatar, isStale) {
            const opacity = isStale ? 0.5 : 1.0;
            let iconHtml = '';
            
            if (avatar && avatar.type === 'upload' && avatar.url) {
                // Uploaded image
                iconHtml = `<div style="
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    border: 3px solid white;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                    opacity: ${opacity};
                    overflow: hidden;
                    background: white;
                ">
                    <img src="${avatar.url}" style="width: 100%; height: 100%; object-fit: cover;" />
                </div>`;
            } else if (avatar && avatar.type === 'icon' && avatar.value && avatarIcons[avatar.value]) {
                // Emoji icon
                const iconData = avatarIcons[avatar.value];
                iconHtml = `<div style="
                    background-color: ${iconData.color};
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    border: 3px solid white;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                    opacity: ${opacity};
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 24px;
                ">${iconData.emoji}</div>`;
            } else {
                // Default colored marker
                iconHtml = `<div style="
                    background-color: #3498db;
                    width: 24px;
                    height: 24px;
                    border-radius: 50%;
                    border: 3px solid white;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                    opacity: ${opacity};
                "></div>`;
            }
            
            return L.divIcon({
                className: 'custom-marker',
                html: iconHtml,
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });
        }
        
        // Keep old function for backward compatibility
        function createColoredIcon(color, isStale) {
            const opacity = isStale ? 0.5 : 1.0;
            return L.divIcon({
                className: 'custom-marker',
                html: `<div style="
                    background-color: ${color};
                    width: 24px;
                    height: 24px;
                    border-radius: 50%;
                    border: 3px solid white;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                    opacity: ${opacity};
                "></div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });
        }
        
        // Fetch and display device locations
        async function fetchLocations() {
            console.log('Fetching locations from API...');
            try {
                // Fetch locations for authenticated user (no email parameter needed)
                const url = `/api/locations`;
                
                const response = await fetch(url);
                const data = await response.json();
                console.log('API Response:', data);
                console.log('Number of locations:', data.locations ? data.locations.length : 0);
                
                if (data.locations && data.locations.length > 0) {
                    const bounds = [];
                    
                    data.locations.forEach((location) => {
                        console.log('Processing device:', location.name, location);
                        const { deviceId, name, latitude, longitude, isStale, batteryLevel, signalStrength, avatar } = location;
                        const icon = createAvatarIcon(avatar, isStale);
                        
                        // Update or create marker
                        if (markers[deviceId]) {
                            // Update existing marker
                            markers[deviceId].setLatLng([latitude, longitude]);
                            markers[deviceId].setIcon(icon);
                            markers[deviceId].setPopupContent(
                                `<b>${name}</b><br>` +
                                `Battery: ${batteryLevel}%<br>` +
                                `Signal: ${signalStrength} dBm<br>` +
                                `${isStale ? '<span style="color: orange;">⚠ Stale data</span>' : '<span style="color: green;">✓ Active</span>'}`
                            );
                            
                            // Update stored location data
                            markers[deviceId].locationData = location;
                        } else {
                            // Create new marker
                            console.log('Creating new marker for:', name, 'at', latitude, longitude);
                            const marker = L.marker([latitude, longitude], { icon })
                                .addTo(map)
                                .bindPopup(
                                    `<b>${name}</b><br>` +
                                    `Battery: ${batteryLevel}%<br>` +
                                    `Signal: ${signalStrength} dBm<br>` +
                                    `${isStale ? '<span style="color: orange;">⚠ Stale data</span>' : '<span style="color: green;">✓ Active</span>'}`
                                );
                            
                            // Add click handler to open drawer
                            marker.on('click', () => openDrawer(location));
                            
                            markers[deviceId] = marker;
                        }
                        
                        // Store location data on marker for drawer access
                        markers[deviceId].locationData = location;
                        
                        // Add to bounds for fitting
                        bounds.push([latitude, longitude]);
                    });
                    
                    // Fit map to show all markers on first load only
                    if (isFirstLoad && bounds.length > 0) {
                        map.fitBounds(bounds, { padding: [50, 50] });
                        isFirstLoad = false;
                    }
                } else {
                    console.log('No active devices found');
                    
                    // Show message if user has devices but no recent data
                    if (data.message && data.deviceCount > 0) {
                        console.log(`User has ${data.deviceCount} devices but no recent location data`);
                        
                        // Show notification on map
                        if (!document.getElementById('no-data-message')) {
                            const messageDiv = document.createElement('div');
                            messageDiv.id = 'no-data-message';
                            messageDiv.style.cssText = `
                                position: fixed;
                                top: 80px;
                                left: 50%;
                                transform: translateX(-50%);
                                background: #fff3cd;
                                color: #856404;
                                padding: 12px 20px;
                                border-radius: 8px;
                                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                                z-index: 1002;
                                font-size: 14px;
                                max-width: 400px;
                                text-align: center;
                            `;
                            messageDiv.innerHTML = `
                                <strong>⚠ No Recent Location Data</strong><br>
                                You have ${data.deviceCount} registered device(s), but they haven't sent location updates recently.
                            `;
                            document.body.appendChild(messageDiv);
                            
                            // Auto-hide after 10 seconds
                            setTimeout(() => {
                                if (document.getElementById('no-data-message')) {
                                    document.getElementById('no-data-message').remove();
                                }
                            }, 10000);
                        }
                    }
                }
            } catch (error) {
                console.error('Error fetching locations:', error);
            }
        }
        
        // Initial fetch
        fetchLocations();
        
        // Poll for updates every 30 seconds as per requirements
        setInterval(fetchLocations, 30000);
        
        // Drawer functionality
        const drawer = document.getElementById('info-drawer');
        const overlay = document.getElementById('drawer-overlay');
        const closeBtn = document.getElementById('close-drawer');
        const manualUpdateBtn = document.getElementById('manual-update-btn');
        const updateBtnText = document.getElementById('update-btn-text');
        const updateMessage = document.getElementById('update-message');
        
        // Store current device ID for manual update
        let currentDeviceId = null;
        
        // Open drawer with device data
        function openDrawer(deviceData) {
            // Store device ID for manual update
            currentDeviceId = deviceData.deviceId;
            
            // Reset update message
            updateMessage.className = 'update-message';
            updateMessage.textContent = '';
            
            // Enable update button
            manualUpdateBtn.disabled = false;
            updateBtnText.textContent = 'Request Update';
            
            // Remove any existing spinner
            const existingSpinner = manualUpdateBtn.querySelector('.spinner');
            if (existingSpinner) {
                existingSpinner.remove();
            }
            // Populate device name
            document.getElementById('drawer-device-name').textContent = deviceData.name || 'Unknown Device';
            
            // Populate avatar
            const drawerAvatar = document.getElementById('drawer-avatar');
            if (deviceData.avatar) {
                if (deviceData.avatar.type === 'upload' && deviceData.avatar.url) {
                    drawerAvatar.innerHTML = `<img src="${deviceData.avatar.url}" alt="${deviceData.name}" />`;
                    drawerAvatar.style.background = 'white';
                } else if (deviceData.avatar.type === 'icon' && deviceData.avatar.value && avatarIcons[deviceData.avatar.value]) {
                    const iconData = avatarIcons[deviceData.avatar.value];
                    drawerAvatar.innerHTML = iconData.emoji;
                    drawerAvatar.style.background = iconData.color;
                } else {
                    drawerAvatar.innerHTML = '👤';
                    drawerAvatar.style.background = '#3498db';
                }
            } else {
                drawerAvatar.innerHTML = '👤';
                drawerAvatar.style.background = '#3498db';
            }
            
            // Populate location data
            document.getElementById('drawer-latitude').textContent = deviceData.latitude.toFixed(6);
            document.getElementById('drawer-longitude').textContent = deviceData.longitude.toFixed(6);
            document.getElementById('drawer-accuracy').textContent = deviceData.accuracy ? `${deviceData.accuracy.toFixed(1)} m` : 'N/A';
            
            // Populate battery level
            const batteryLevel = deviceData.batteryLevel || 0;
            document.getElementById('drawer-battery-text').textContent = `${batteryLevel}%`;
            
            const batteryBar = document.getElementById('drawer-battery-bar');
            batteryBar.style.width = `${batteryLevel}%`;
            
            // Set battery color based on level
            batteryBar.className = 'battery-fill';
            if (batteryLevel > 50) {
                batteryBar.classList.add('high');
            } else if (batteryLevel > 20) {
                batteryBar.classList.add('medium');
            } else {
                batteryBar.classList.add('low');
            }
            
            // Populate signal strength
            const signalStrength = deviceData.signalStrength || 0;
            document.getElementById('drawer-signal').textContent = `${signalStrength} dBm`;
            
            // Populate status
            const statusElement = document.getElementById('drawer-status');
            if (deviceData.isStale) {
                statusElement.innerHTML = '<span class="status-badge status-stale">Stale</span>';
            } else {
                statusElement.innerHTML = '<span class="status-badge status-active">Active</span>';
            }
            
            // Populate timestamp
            const timestamp = deviceData.lastUpdate;
            const date = new Date(timestamp);
            document.getElementById('drawer-timestamp').textContent = date.toLocaleString();
            
            // Calculate time ago
            const now = Date.now();
            const diffMs = now - timestamp;
            const diffSec = Math.floor(diffMs / 1000);
            const diffMin = Math.floor(diffSec / 60);
            const diffHour = Math.floor(diffMin / 60);
            
            let timeAgo;
            if (diffSec < 60) {
                timeAgo = `${diffSec} seconds ago`;
            } else if (diffMin < 60) {
                timeAgo = `${diffMin} minute${diffMin !== 1 ? 's' : ''} ago`;
            } else {
                timeAgo = `${diffHour} hour${diffHour !== 1 ? 's' : ''} ago`;
            }
            document.getElementById('drawer-time-ago').textContent = timeAgo;
            
            // Handle optional status fields
            const optionalSection = document.getElementById('optional-status-section');
            let hasOptionalStatus = false;
            
            // Microphone status
            if (deviceData.microphoneStatus !== null && deviceData.microphoneStatus !== undefined) {
                document.getElementById('microphone-item').style.display = 'flex';
                document.getElementById('drawer-microphone').textContent = deviceData.microphoneStatus ? 'Active' : 'Inactive';
                hasOptionalStatus = true;
            } else {
                document.getElementById('microphone-item').style.display = 'none';
            }
            
            // Camera status
            if (deviceData.cameraStatus !== null && deviceData.cameraStatus !== undefined) {
                document.getElementById('camera-item').style.display = 'flex';
                document.getElementById('drawer-camera').textContent = deviceData.cameraStatus ? 'Active' : 'Inactive';
                hasOptionalStatus = true;
            } else {
                document.getElementById('camera-item').style.display = 'none';
            }
            
            // Recording status
            if (deviceData.recordingStatus !== null && deviceData.recordingStatus !== undefined) {
                document.getElementById('recording-item').style.display = 'flex';
                document.getElementById('drawer-recording').textContent = deviceData.recordingStatus ? 'Active' : 'Inactive';
                hasOptionalStatus = true;
            } else {
                document.getElementById('recording-item').style.display = 'none';
            }
            
            // Show/hide optional status section
            optionalSection.style.display = hasOptionalStatus ? 'block' : 'none';
            
            // Open drawer and show overlay
            drawer.classList.add('open');
            overlay.classList.add('visible');
        }
        
        // Close drawer
        function closeDrawer() {
            drawer.classList.remove('open');
            overlay.classList.remove('visible');
        }
        
        // Close button click handler
        closeBtn.addEventListener('click', closeDrawer);
        
        // Overlay click handler (click outside drawer to close)
        overlay.addEventListener('click', closeDrawer);
        
        // Manual update button click handler
        manualUpdateBtn.addEventListener('click', async () => {
            if (!currentDeviceId) {
                return;
            }
            
            // Disable button and show loading state
            manualUpdateBtn.disabled = true;
            updateBtnText.textContent = 'Requesting...';
            
            // Add spinner
            const spinner = document.createElement('div');
            spinner.className = 'spinner';
            manualUpdateBtn.insertBefore(spinner, updateBtnText);
            
            // Hide any previous messages
            updateMessage.className = 'update-message';
            updateMessage.textContent = '';
            
            try {
                // Call the manual update API endpoint
                const response = await fetch(`/api/devices/${currentDeviceId}/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Show success message
                    updateMessage.className = 'update-message success';
                    updateMessage.textContent = 'Update request sent successfully!';
                    
                    // Re-enable button after 2 seconds
                    setTimeout(() => {
                        manualUpdateBtn.disabled = false;
                        updateBtnText.textContent = 'Request Update';
                        spinner.remove();
                    }, 2000);
                } else {
                    // Show error message
                    updateMessage.className = 'update-message error';
                    updateMessage.textContent = data.message || 'Failed to send update request';
                    
                    // Re-enable button
                    manualUpdateBtn.disabled = false;
                    updateBtnText.textContent = 'Request Update';
                    spinner.remove();
                }
            } catch (error) {
                console.error('Error triggering manual update:', error);
                
                // Show error message
                updateMessage.className = 'update-message error';
                updateMessage.textContent = 'Network error. Please try again.';
                
                // Re-enable button
                manualUpdateBtn.disabled = false;
                updateBtnText.textContent = 'Request Update';
                
                // Remove spinner
                const existingSpinner = manualUpdateBtn.querySelector('.spinner');
                if (existingSpinner) {
                    existingSpinner.remove();
                }
            }
        });
    </script>
    
    <!-- Profile Settings Modal -->
    <div id="profile-modal" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
    ">
        <div style="
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        ">
            <!-- Modal Header -->
            <div style="
                padding: 18px 20px;
                background: #2196F3;
                color: white;
                display: flex;
                justify-content: space-between;
                align-items: center;
            ">
                <h2 style="margin: 0; font-size: 18px; font-weight: 500;">Profile Settings</h2>
                <button id="close-profile-modal" style="
                    background: none;
                    border: none;
                    color: white;
                    font-size: 20px;
                    cursor: pointer;
                    padding: 0;
                    width: 28px;
                    height: 28px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 4px;
                    transition: all 0.3s ease;
                    transform: rotate(0deg);
                " onmouseover="this.style.background='rgba(244, 67, 54, 0.2)'; this.style.color='#f44336'; this.style.transform='rotate(180deg)'" 
                   onmouseout="this.style.background='none'; this.style.color='white'; this.style.transform='rotate(0deg)'">
                    ×
                </button>
            </div>
            
            <!-- Tab Navigation -->
            <div style="
                display: flex;
                background: #f8f9fa;
                border-bottom: 1px solid #e9ecef;
            ">
                <button class="tab-btn active" data-tab="profile" style="
                    flex: 1;
                    padding: 14px;
                    background: none;
                    border: none;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                    color: #495057;
                    border-bottom: 2px solid transparent;
                    transition: all 0.2s;
                ">Profile</button>
                <button class="tab-btn" data-tab="settings" style="
                    flex: 1;
                    padding: 14px;
                    background: none;
                    border: none;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                    color: #495057;
                    border-bottom: 2px solid transparent;
                    transition: all 0.2s;
                ">Settings</button>
                <button class="tab-btn" data-tab="devices" style="
                    flex: 1;
                    padding: 14px;
                    background: none;
                    border: none;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                    color: #495057;
                    border-bottom: 2px solid transparent;
                    transition: all 0.2s;
                ">Devices</button>
            </div>
            
            <!-- Tab Content -->
            <div style="padding: 20px; max-height: 400px; overflow-y: auto;">
                <!-- Profile Tab -->
                <div id="profile-tab" class="tab-content">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div id="profile-avatar" style="
                            width: 70px;
                            height: 70px;
                            border-radius: 50%;
                            background: #2196F3;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin: 0 auto 12px;
                            color: white;
                            font-size: 36px;
                            cursor: pointer;
                            transition: transform 0.2s, box-shadow 0.2s;
                            position: relative;
                        " onclick="openAvatarPicker()" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                            👤
                            <div style="
                                position: absolute;
                                bottom: -2px;
                                right: -2px;
                                width: 24px;
                                height: 24px;
                                background: #2196F3;
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                color: white;
                                font-size: 12px;
                                border: 2px solid white;
                            ">✏️</div>
                        </div>
                        <h3 style="margin: 0 0 4px; color: #2c3e50; font-weight: 500;">{{ Auth::user()->username }}</h3>
                        <p style="margin: 0; color: #6c757d; font-size: 13px;">{{ Auth::user()->email }}</p>
                    </div>
                    
                    <form id="profile-form">
                        <div style="margin-bottom: 16px;">
                            <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #2c3e50; font-size: 13px;">Username</label>
                            <input type="text" id="profile-username" value="{{ Auth::user()->username }}" style="
                                width: 100%;
                                padding: 10px 12px;
                                border: 1px solid #dee2e6;
                                border-radius: 4px;
                                font-size: 14px;
                                transition: border-color 0.2s;
                            ">
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #2c3e50; font-size: 13px;">Email</label>
                            <input type="email" id="profile-email" value="{{ Auth::user()->email }}" style="
                                width: 100%;
                                padding: 10px 12px;
                                border: 1px solid #dee2e6;
                                border-radius: 4px;
                                font-size: 14px;
                                transition: border-color 0.2s;
                            ">
                        </div>
                        
                        <div style="text-align: right;">
                            <button type="submit" style="
                                background: #2196F3;
                                color: white;
                                border: none;
                                padding: 10px 20px;
                                border-radius: 4px;
                                font-size: 14px;
                                font-weight: 400;
                                cursor: pointer;
                                transition: background 0.2s;
                            " onmouseover="this.style.background='#1976D2'" onmouseout="this.style.background='#2196F3'">
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Settings Tab -->
                <div id="settings-tab" class="tab-content" style="display: none;">
                    <h4 style="margin: 0 0 16px; color: #2c3e50; font-weight: 500;">Change Password</h4>
                    
                    <form id="password-form">
                        <div style="margin-bottom: 16px;">
                            <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #2c3e50; font-size: 13px;">Current Password</label>
                            <input type="password" id="current-password" style="
                                width: 100%;
                                padding: 10px 12px;
                                border: 1px solid #dee2e6;
                                border-radius: 4px;
                                font-size: 14px;
                                transition: border-color 0.2s;
                            ">
                        </div>
                        
                        <div style="margin-bottom: 16px;">
                            <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #2c3e50; font-size: 13px;">New Password</label>
                            <input type="password" id="new-password" style="
                                width: 100%;
                                padding: 10px 12px;
                                border: 1px solid #dee2e6;
                                border-radius: 4px;
                                font-size: 14px;
                                transition: border-color 0.2s;
                            ">
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #2c3e50; font-size: 13px;">Confirm New Password</label>
                            <input type="password" id="confirm-password" style="
                                width: 100%;
                                padding: 10px 12px;
                                border: 1px solid #dee2e6;
                                border-radius: 4px;
                                font-size: 14px;
                                transition: border-color 0.2s;
                            ">
                        </div>
                        
                        <div style="text-align: right;">
                            <button type="submit" style="
                                background: #2196F3;
                                color: white;
                                border: none;
                                padding: 10px 20px;
                                border-radius: 4px;
                                font-size: 14px;
                                font-weight: 400;
                                cursor: pointer;
                                transition: background 0.2s;
                            " onmouseover="this.style.background='#1976D2'" onmouseout="this.style.background='#2196F3'">
                                Change Password
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Devices Tab -->
                <div id="devices-tab" class="tab-content" style="display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h4 style="margin: 0; color: #2c3e50; font-weight: 500;">My Devices</h4>
                        <button id="refresh-devices" style="
                            background: #2196F3;
                            color: white;
                            border: none;
                            padding: 6px 12px;
                            border-radius: 4px;
                            font-size: 12px;
                            font-weight: 400;
                            cursor: pointer;
                            transition: background 0.2s;
                            display: flex;
                            align-items: center;
                            gap: 4px;
                        " onmouseover="this.style.background='#1976D2'" onmouseout="this.style.background='#2196F3'">
                            <span class="material-symbols-outlined" style="font-size: 18px; font-weight: 300;">refresh</span>
                            Refresh
                        </button>
                    </div>
                    
                    <div id="devices-list">
                        <!-- Devices will be loaded here -->
                        <div style="text-align: center; padding: 20px; color: #6c757d;">
                            Loading devices...
                        </div>
                    </div>
                    
                    <!-- Pending Approvals -->
                    <div id="pending-approvals" style="margin-top: 20px;">
                        <h5 style="margin: 0 0 12px; color: #2c3e50; font-weight: 500;">Pending Device Approvals</h5>
                        <div id="pending-list">
                            <!-- Pending devices will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Avatar Picker Modal -->
    <div id="avatar-picker-modal" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        z-index: 2001;
        display: none;
        align-items: center;
        justify-content: center;
    ">
        <div style="
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            max-height: 70vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        ">
            <!-- Modal Header -->
            <div style="
                padding: 18px 20px;
                background: #2196F3;
                color: white;
                display: flex;
                justify-content: space-between;
                align-items: center;
            ">
                <h3 style="margin: 0; font-size: 16px; font-weight: 500;">Choose Avatar</h3>
                <button id="close-avatar-picker" style="
                    background: none;
                    border: none;
                    color: white;
                    font-size: 18px;
                    cursor: pointer;
                    padding: 0;
                    width: 28px;
                    height: 28px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 4px;
                    transition: all 0.3s ease;
                    transform: rotate(0deg);
                " onmouseover="this.style.background='rgba(244, 67, 54, 0.2)'; this.style.color='#f44336'; this.style.transform='rotate(180deg)'" 
                   onmouseout="this.style.background='none'; this.style.color='white'; this.style.transform='rotate(0deg)'">
                    ×
                </button>
            </div>
            
            <!-- Avatar Grid -->
            <div style="padding: 20px; max-height: 400px; overflow-y: auto;">
                <div id="avatar-grid" style="
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
                    gap: 12px;
                    justify-items: center;
                ">
                    <!-- Avatar options will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Floating menu functionality
        const userAvatarBtn = document.getElementById('user-avatar-btn');
        const userDropdown = document.getElementById('user-dropdown');
        const profileSettingsBtn = document.getElementById('profile-settings-btn');
        const profileModal = document.getElementById('profile-modal');
        const closeProfileModal = document.getElementById('close-profile-modal');
        
        // Toggle dropdown
        userAvatarBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isVisible = userDropdown.style.visibility === 'visible';
            
            if (isVisible) {
                userDropdown.style.opacity = '0';
                userDropdown.style.visibility = 'hidden';
                userDropdown.style.transform = 'translateY(-10px)';
            } else {
                userDropdown.style.opacity = '1';
                userDropdown.style.visibility = 'visible';
                userDropdown.style.transform = 'translateY(0)';
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', () => {
            userDropdown.style.opacity = '0';
            userDropdown.style.visibility = 'hidden';
            userDropdown.style.transform = 'translateY(-10px)';
        });
        
        // Open profile modal
        profileSettingsBtn.addEventListener('click', () => {
            profileModal.style.display = 'flex';
            userDropdown.style.opacity = '0';
            userDropdown.style.visibility = 'hidden';
            userDropdown.style.transform = 'translateY(-10px)';
            loadDevices(); // Load devices when modal opens
            loadUserAvatar(); // Load user's current avatar
        });
        
        // Close profile modal
        closeProfileModal.addEventListener('click', () => {
            profileModal.style.display = 'none';
        });
        
        // Close modal when clicking outside
        profileModal.addEventListener('click', (e) => {
            if (e.target === profileModal) {
                profileModal.style.display = 'none';
            }
        });
        
        // Tab functionality
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const tabName = btn.dataset.tab;
                
                // Update tab buttons
                tabBtns.forEach(b => {
                    b.classList.remove('active');
                    b.style.borderBottomColor = 'transparent';
                    b.style.background = 'none';
                });
                btn.classList.add('active');
                btn.style.borderBottomColor = '#2196F3';
                btn.style.background = 'white';
                
                // Update tab content
                tabContents.forEach(content => {
                    content.style.display = 'none';
                });
                document.getElementById(tabName + '-tab').style.display = 'block';
                
                // Load devices when devices tab is clicked
                if (tabName === 'devices') {
                    loadDevices();
                }
            });
        });
        
        // Load devices function
        async function loadDevices() {
            const devicesList = document.getElementById('devices-list');
            const pendingList = document.getElementById('pending-list');
            
            try {
                const response = await fetch('/api/user/devices');
                const data = await response.json();
                
                if (data.devices) {
                    devicesList.innerHTML = '';
                    
                    if (data.devices.length === 0) {
                        devicesList.innerHTML = '<div style="text-align: center; padding: 20px; color: #6c757d;">No devices registered</div>';
                    } else {
                        data.devices.forEach(device => {
                            const deviceCard = createDeviceCard(device);
                            devicesList.appendChild(deviceCard);
                        });
                    }
                }
                
                // Load pending approvals (placeholder for now)
                pendingList.innerHTML = '<div style="text-align: center; padding: 20px; color: #6c757d; font-size: 12px;">No pending approvals</div>';
                
            } catch (error) {
                console.error('Error loading devices:', error);
                devicesList.innerHTML = '<div style="text-align: center; padding: 20px; color: #dc3545;">Error loading devices</div>';
            }
        }
        
        // Create device card
        function createDeviceCard(device) {
            const card = document.createElement('div');
            card.style.cssText = `
                border: 1px solid #e9ecef;
                border-radius: 4px;
                padding: 16px;
                margin-bottom: 12px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                transition: box-shadow 0.2s;
            `;
            
            card.innerHTML = `
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="
                        width: 40px;
                        height: 40px;
                        border-radius: 50%;
                        background: #f8f9fa;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 18px;
                    ">${device.avatar?.type === 'icon' ? '📱' : '📱'}</div>
                    <div>
                        <div style="font-weight: 500; color: #2c3e50; font-size: 14px;" id="device-name-${device.device_id}">${device.name}</div>
                        <div style="font-size: 12px; color: #6c757d;">${device.device_id}</div>
                        <div style="font-size: 11px; color: ${device.is_active ? '#28a745' : '#dc3545'};">
                            ${device.is_active ? '● Active' : '● Inactive'}
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button onclick="requestDeviceUpdate('${device.device_id}')" style="
                        background: none;
                        border: none;
                        color: #4CAF50;
                        padding: 8px;
                        border-radius: 50%;
                        cursor: pointer;
                        transition: background 0.2s;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 32px;
                        height: 32px;
                    " onmouseover="this.style.background='rgba(76, 175, 80, 0.1)'" onmouseout="this.style.background='none'" title="Request location update">
                        <span class="material-symbols-outlined" style="font-size: 18px; font-weight: 300;">update</span>
                    </button>
                    <button onclick="generateVerificationCode('${device.device_id}')" style="
                        background: none;
                        border: none;
                        color: #FF9800;
                        padding: 8px;
                        border-radius: 50%;
                        cursor: pointer;
                        transition: background 0.2s;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 32px;
                        height: 32px;
                    " onmouseover="this.style.background='rgba(255, 152, 0, 0.1)'" onmouseout="this.style.background='none'" title="Generate verification code">
                        <span class="material-symbols-outlined" style="font-size: 18px; font-weight: 300;">vpn_key_alert</span>
                    </button>
                    <button onclick="editDevice('${device.device_id}', '${device.name}')" style="
                        background: none;
                        border: none;
                        color: #2196F3;
                        padding: 8px;
                        border-radius: 50%;
                        cursor: pointer;
                        transition: background 0.2s;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 32px;
                        height: 32px;
                    " onmouseover="this.style.background='rgba(33, 150, 243, 0.1)'" onmouseout="this.style.background='none'" title="Edit device name">
                        <span class="material-symbols-outlined" style="font-size: 18px; font-weight: 300;">edit</span>
                    </button>
                    <button onclick="deleteDevice('${device.device_id}')" style="
                        background: none;
                        border: none;
                        color: #f44336;
                        padding: 8px;
                        border-radius: 50%;
                        cursor: pointer;
                        transition: background 0.2s;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 32px;
                        height: 32px;
                    " onmouseover="this.style.background='rgba(244, 67, 54, 0.1)'" onmouseout="this.style.background='none'" title="Delete device">
                        <span class="material-symbols-outlined" style="font-size: 18px; font-weight: 300;">delete</span>
                    </button>
                </div>
            `;
            
            return card;
        }
        
        // Edit device function
        async function editDevice(deviceId, currentName) {
            const nameElement = document.getElementById(`device-name-${deviceId}`);
            
            // Create input field
            const input = document.createElement('input');
            input.type = 'text';
            input.value = currentName;
            input.style.cssText = `
                width: 100%;
                padding: 4px 8px;
                border: 1px solid #2196F3;
                border-radius: 4px;
                font-size: 14px;
                font-weight: 500;
                color: #2c3e50;
                background: white;
            `;
            
            // Replace name with input
            nameElement.innerHTML = '';
            nameElement.appendChild(input);
            input.focus();
            input.select();
            
            // Handle save on Enter or blur
            const saveEdit = async () => {
                const newName = input.value.trim();
                
                if (newName === '' || newName === currentName) {
                    // Cancel edit - restore original name
                    nameElement.textContent = currentName;
                    return;
                }
                
                try {
                    const response = await fetch(`/api/devices/${deviceId}/name`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({ name: newName })
                    });
                    
                    if (response.ok) {
                        nameElement.textContent = newName;
                        // Show success message briefly
                        const originalColor = nameElement.style.color;
                        nameElement.style.color = '#2196F3';
                        setTimeout(() => {
                            nameElement.style.color = originalColor;
                        }, 1000);
                    } else {
                        nameElement.textContent = currentName;
                        alert('Error updating device name');
                    }
                } catch (error) {
                    console.error('Error updating device name:', error);
                    nameElement.textContent = currentName;
                    alert('Error updating device name');
                }
            };
            
            // Save on Enter key
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    saveEdit();
                }
            });
            
            // Save on blur (click outside)
            input.addEventListener('blur', saveEdit);
            
            // Cancel on Escape key
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    nameElement.textContent = currentName;
                }
            });
        }
        
        // Generate verification code function
        async function generateVerificationCode(deviceId) {
            const button = event.target.closest('button');
            const icon = button.querySelector('.material-symbols-outlined');
            
            // Show loading state
            button.disabled = true;
            button.style.opacity = '0.6';
            
            try {
                const response = await fetch(`/api/devices/${deviceId}/generate-code`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Show verification code modal
                    showVerificationCodeModal(data.verification_code, data.expires_at);
                } else {
                    alert(data.message || 'Failed to generate verification code');
                }
            } catch (error) {
                console.error('Error generating verification code:', error);
                alert('Error generating verification code');
            } finally {
                // Reset button state
                button.disabled = false;
                button.style.opacity = '1';
            }
        }
        
        // Show verification code modal
        function showVerificationCodeModal(code, expiresAt) {
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0, 0, 0, 0.5);
                z-index: 3000;
                display: flex;
                align-items: center;
                justify-content: center;
            `;
            
            const expiryTime = new Date(expiresAt).toLocaleTimeString();
            
            modal.innerHTML = `
                <div style="
                    background: white;
                    border-radius: 16px;
                    padding: 30px;
                    max-width: 400px;
                    text-align: center;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                ">
                    <div style="
                        width: 60px;
                        height: 60px;
                        background: #FF9800;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: 0 auto 20px;
                        color: white;
                        font-size: 24px;
                    ">🔑</div>
                    
                    <h3 style="margin: 0 0 10px; color: #2c3e50; font-weight: 500;">Verification Code</h3>
                    <p style="margin: 0 0 20px; color: #6c757d; font-size: 14px;">Use this code to delete the device from mobile app</p>
                    
                    <div style="
                        background: #f8f9fa;
                        border: 2px dashed #dee2e6;
                        border-radius: 8px;
                        padding: 20px;
                        margin: 20px 0;
                        font-family: 'Courier New', monospace;
                        font-size: 24px;
                        font-weight: bold;
                        color: #2c3e50;
                        letter-spacing: 2px;
                    ">${code}</div>
                    
                    <p style="margin: 0 0 20px; color: #e74c3c; font-size: 12px;">
                        ⏰ Expires at ${expiryTime}
                    </p>
                    
                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <button onclick="copyToClipboard('${code}')" style="
                            background: #2196F3;
                            color: white;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 4px;
                            font-size: 14px;
                            cursor: pointer;
                            transition: background 0.2s;
                        " onmouseover="this.style.background='#1976D2'" onmouseout="this.style.background='#2196F3'">
                            Copy Code
                        </button>
                        <button onclick="this.closest('.modal-overlay').remove()" style="
                            background: #6c757d;
                            color: white;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 4px;
                            font-size: 14px;
                            cursor: pointer;
                            transition: background 0.2s;
                        " onmouseover="this.style.background='#5a6268'" onmouseout="this.style.background='#6c757d'">
                            Close
                        </button>
                    </div>
                </div>
            `;
            
            modal.className = 'modal-overlay';
            document.body.appendChild(modal);
            
            // Close on outside click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        }
        
        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Show success feedback
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                button.style.background = '#4CAF50';
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.style.background = '#2196F3';
                }, 2000);
            }).catch(() => {
                alert('Failed to copy code');
            });
        }
        
        // Request device update function
        async function requestDeviceUpdate(deviceId) {
            const button = event.target.closest('button');
            const icon = button.querySelector('.material-symbols-outlined');
            
            // Show loading state
            button.disabled = true;
            button.style.opacity = '0.6';
            icon.style.animation = 'spin 1s linear infinite';
            
            try {
                const response = await fetch(`/api/devices/${deviceId}/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                if (response.ok) {
                    // Show success feedback
                    const originalColor = button.style.color;
                    button.style.color = '#2196F3';
                    
                    // Show success message
                    const successMsg = document.createElement('div');
                    successMsg.style.cssText = `
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: #4CAF50;
                        color: white;
                        padding: 12px 20px;
                        border-radius: 4px;
                        z-index: 3000;
                        font-size: 14px;
                    `;
                    successMsg.textContent = 'Update request sent successfully';
                    document.body.appendChild(successMsg);
                    
                    setTimeout(() => {
                        button.style.color = originalColor;
                        if (document.body.contains(successMsg)) {
                            document.body.removeChild(successMsg);
                        }
                    }, 3000);
                } else {
                    // Show error
                    const errorMsg = document.createElement('div');
                    errorMsg.style.cssText = `
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: #f44336;
                        color: white;
                        padding: 12px 20px;
                        border-radius: 4px;
                        z-index: 3000;
                        font-size: 14px;
                    `;
                    errorMsg.textContent = 'Failed to send update request';
                    document.body.appendChild(errorMsg);
                    
                    setTimeout(() => {
                        if (document.body.contains(errorMsg)) {
                            document.body.removeChild(errorMsg);
                        }
                    }, 3000);
                }
            } catch (error) {
                console.error('Error requesting device update:', error);
                alert('Error requesting device update');
            } finally {
                // Reset button state
                button.disabled = false;
                button.style.opacity = '1';
                icon.style.animation = 'none';
            }
        }
        
        // Delete device function
        async function deleteDevice(deviceId) {
            if (!confirm('Are you sure you want to delete this device?')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/devices/${deviceId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                if (response.ok) {
                    loadDevices(); // Reload devices list
                    // Show success message
                    const successMsg = document.createElement('div');
                    successMsg.style.cssText = `
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: #2196F3;
                        color: white;
                        padding: 12px 20px;
                        border-radius: 4px;
                        z-index: 3000;
                        font-size: 14px;
                    `;
                    successMsg.textContent = 'Device deleted successfully';
                    document.body.appendChild(successMsg);
                    
                    setTimeout(() => {
                        document.body.removeChild(successMsg);
                    }, 3000);
                } else {
                    alert('Error deleting device');
                }
            } catch (error) {
                console.error('Error deleting device:', error);
                alert('Error deleting device');
            }
        }
        
        // Refresh devices
        document.getElementById('refresh-devices').addEventListener('click', loadDevices);
        
        // Profile form submission
        document.getElementById('profile-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('profile-username').value;
            const email = document.getElementById('profile-email').value;
            
            try {
                const response = await fetch('/api/user/profile', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ username, email })
                });
                
                if (response.ok) {
                    alert('Profile updated successfully');
                    location.reload(); // Reload to show updated info
                } else {
                    alert('Error updating profile');
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                alert('Error updating profile');
            }
        });
        
        // Password form submission
        document.getElementById('password-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const currentPassword = document.getElementById('current-password').value;
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            if (newPassword !== confirmPassword) {
                alert('New passwords do not match');
                return;
            }
            
            try {
                const response = await fetch('/api/user/change-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ 
                        current_password: currentPassword,
                        new_password: newPassword,
                        new_password_confirmation: confirmPassword
                    })
                });
                
                if (response.ok) {
                    alert('Password changed successfully');
                    document.getElementById('password-form').reset();
                } else {
                    const data = await response.json();
                    alert(data.message || 'Error changing password');
                }
            } catch (error) {
                console.error('Error changing password:', error);
                alert('Error changing password');
            }
        });
        
        // Avatar picker functionality
        const avatarPickerModal = document.getElementById('avatar-picker-modal');
        const closeAvatarPicker = document.getElementById('close-avatar-picker');
        const profileAvatar = document.getElementById('profile-avatar');
        
        // Open avatar picker
        function openAvatarPicker() {
            avatarPickerModal.style.display = 'flex';
            loadAvatarOptions();
        }
        
        // Close avatar picker
        closeAvatarPicker.addEventListener('click', () => {
            avatarPickerModal.style.display = 'none';
        });
        
        // Close modal when clicking outside
        avatarPickerModal.addEventListener('click', (e) => {
            if (e.target === avatarPickerModal) {
                avatarPickerModal.style.display = 'none';
            }
        });
        
        // Load avatar options
        function loadAvatarOptions() {
            const avatarGrid = document.getElementById('avatar-grid');
            
            // Load available icons from the same API used for devices
            fetch('/api/devices/avatar-icons')
                .then(res => res.json())
                .then(data => {
                    avatarGrid.innerHTML = '';
                    
                    if (data.icons) {
                        data.icons.forEach(icon => {
                            const avatarOption = document.createElement('div');
                            avatarOption.style.cssText = `
                                width: 70px;
                                height: 70px;
                                border-radius: 50%;
                                background: ${icon.color};
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 32px;
                                cursor: pointer;
                                transition: transform 0.2s, box-shadow 0.2s;
                                border: 3px solid transparent;
                            `;
                            
                            avatarOption.innerHTML = icon.emoji;
                            
                            // Add hover effects
                            avatarOption.onmouseover = function() {
                                this.style.transform = 'scale(1.1)';
                                this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.2)';
                                this.style.borderColor = '#2196F3';
                            };
                            
                            avatarOption.onmouseout = function() {
                                this.style.transform = 'scale(1)';
                                this.style.boxShadow = 'none';
                                this.style.borderColor = 'transparent';
                            };
                            
                            // Add click handler
                            avatarOption.onclick = function() {
                                selectAvatar(icon);
                            };
                            
                            avatarGrid.appendChild(avatarOption);
                        });
                    }
                })
                .catch(err => {
                    console.error('Failed to load avatar options:', err);
                    avatarGrid.innerHTML = '<div style="text-align: center; padding: 20px; color: #6c757d;">Error loading avatar options</div>';
                });
        }
        
        // Select avatar
        function selectAvatar(icon) {
            // Update profile avatar display
            profileAvatar.style.background = icon.color;
            profileAvatar.innerHTML = `
                ${icon.emoji}
                <div style="
                    position: absolute;
                    bottom: -2px;
                    right: -2px;
                    width: 24px;
                    height: 24px;
                    background: #2196F3;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: 12px;
                    border: 2px solid white;
                ">✏️</div>
            `;
            
            // Update floating menu avatar IMMEDIATELY
            const userAvatarBtn = document.getElementById('user-avatar-btn');
            userAvatarBtn.style.background = icon.color;
            userAvatarBtn.innerHTML = icon.emoji;
            
            // Force a repaint to ensure immediate visual update
            userAvatarBtn.offsetHeight;
            
            // Save to server
            saveUserAvatar(icon);
            
            // Close modal
            avatarPickerModal.style.display = 'none';
        }
        
        // Save user avatar (placeholder - you can implement the API endpoint)
        async function saveUserAvatar(icon) {
            try {
                const response = await fetch('/api/user/avatar', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ 
                        avatar_type: 'icon',
                        avatar_value: icon.id
                    })
                });
                
                if (response.ok) {
                    console.log('Avatar updated successfully');
                } else {
                    console.error('Error updating avatar');
                }
            } catch (error) {
                console.error('Error saving avatar:', error);
            }
        }
        
        // Load user's current avatar
        async function loadUserAvatar() {
            try {
                const response = await fetch('/api/user/profile-data');
                if (response.ok) {
                    const userData = await response.json();
                    
                    if (userData.avatar_type === 'icon' && userData.avatar_value && avatarIcons[userData.avatar_value]) {
                        const iconData = avatarIcons[userData.avatar_value];
                        
                        // Update profile avatar
                        profileAvatar.style.background = iconData.color;
                        profileAvatar.innerHTML = `
                            ${iconData.emoji}
                            <div style="
                                position: absolute;
                                bottom: -2px;
                                right: -2px;
                                width: 24px;
                                height: 24px;
                                background: #2196F3;
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                color: white;
                                font-size: 12px;
                                border: 2px solid white;
                            ">✏️</div>
                        `;
                        
                        // Update floating menu avatar
                        const userAvatarBtn = document.getElementById('user-avatar-btn');
                        userAvatarBtn.style.background = iconData.color;
                        userAvatarBtn.innerHTML = iconData.emoji;
                        userAvatarBtn.style.fontSize = '24px';
                    }
                }
            } catch (error) {
                console.error('Error loading user avatar:', error);
            }
        }
    </script>
</body>
</html>
