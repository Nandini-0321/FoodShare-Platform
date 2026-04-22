/**
 * FoodShare Live Tracking System
 * Uses Leaflet.js for map rendering, Geolocation API, and Socket.IO for real-time updates.
 */

class TrackingSystem {
    constructor() {
        this.map = null;
        this.marker = null;
        this.routeLine = null;
        this.trackingId = null;
        this.watchId = null;
        this.isVolunteer = false;
        this.baseUrl = '/Foodshare3/controllers/TrackingController.php';
        this.socket = (typeof io !== 'undefined') ? io('http://localhost:3000') : null;

        this.init();
    }

    init() {
        this.injectModal();
        this.attachEventListeners();
        
        if (this.socket) {
            this.socket.on('location_updated', (data) => {
                if (!this.isVolunteer) {
                    this.updateMapFromSocket(data);
                }
            });
        }
    }

    injectModal() {
        const modalHtml = `
            <div id="tracking-modal" class="tracking-modal">
                <div class="tracking-content">
                    <div class="tracking-header">
                        <div class="tracking-info">
                            <div class="tracking-stat">
                                <label>Status <span style="font-size: 10px; color: ${this.socket ? '#00e676' : 'red'};">${this.socket ? '(Live)' : '(Offline)'}</span></label>
                                <value id="track-status">Connecting...</value>
                            </div>
                            <div class="tracking-stat">
                                <label>Speed</label>
                                <value id="track-speed">0 km/h</value>
                            </div>
                        </div>
                        <i class="fas fa-times tracking-close"></i>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="tracking-progress-container">
                        <div class="tracking-stepper">
                            <div class="tracking-progress-bar" id="stepper-progress" style="width: 50%;"></div>
                            <div class="tracking-step completed">
                                <div class="step-icon"><i class="fas fa-check"></i></div>
                                <div class="step-label">Requested</div>
                            </div>
                            <div class="tracking-step completed">
                                <div class="step-icon"><i class="fas fa-check"></i></div>
                                <div class="step-label">Accepted</div>
                            </div>
                            <div class="tracking-step completed" id="step-pickup">
                                <div class="step-icon"><i class="fas fa-box"></i></div>
                                <div class="step-label">Picked Up</div>
                            </div>
                            <div class="tracking-step active" id="step-transit">
                                <div class="step-icon"><i class="fas fa-truck"></i></div>
                                <div class="step-label">On the Way</div>
                            </div>
                            <div class="tracking-step" id="step-delivered">
                                <div class="step-icon"><i class="fas fa-home"></i></div>
                                <div class="step-label">Delivered</div>
                            </div>
                        </div>
                    </div>

                    <div id="map" class="tracking-map"></div>
                    
                    <!-- Volunteer Controls -->
                    <div id="volunteer-controls" class="tracking-controls" style="display: none;">
                        <button id="btn-stop-track" class="btn-track btn-stop-track">
                            <i class="fas fa-stop"></i> Stop Tracking
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    attachEventListeners() {
        document.querySelector('.tracking-close').addEventListener('click', () => {
            document.getElementById('tracking-modal').style.display = 'none';
            this.stopViewing();
        });

        const stopBtn = document.getElementById('btn-stop-track');
        if (stopBtn) {
            stopBtn.addEventListener('click', () => {
                if (confirm('Are you sure you want to stop tracking? This will complete the delivery session.')) {
                    this.stopTracking();
                }
            });
        }
    }

    // Initialize Map
    initMap(lat, lng) {
        if (this.map) {
            this.map.remove(); // Reset map if exists
        }

        const startLat = lat || 12.9716; // Default to Bangalore
        const startLng = lng || 77.5946;

        this.map = L.map('map').setView([startLat, startLng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);

        // Custom marker icon
        const icon = L.divIcon({
            className: 'custom-div-icon',
            html: "<div style='background-color:#4834d4; width: 15px; height: 15px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5);'></div>",
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });

        this.marker = L.marker([startLat, startLng], { icon: icon }).addTo(this.map);
        this.routeLine = L.polyline([], { color: '#4834d4', weight: 4 }).addTo(this.map);
    }

    // Volunteer: Start Tracking
    async startTracking(pickupId, donationId) {
        this.isVolunteer = true;

        try {
            const formData = new FormData();
            formData.append('action', 'start_tracking');
            formData.append('pickup_id', pickupId);
            formData.append('donation_id', donationId);

            const response = await fetch(this.baseUrl, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                this.trackingId = data.tracking_id;
                if(this.socket) {
                    this.socket.emit('join_tracking', this.trackingId);
                }
                this.startGeolocation();
                this.showModal(true);
            } else {
                alert('Failed to start tracking: ' + data.error);
            }
        } catch (error) {
            console.error('Error starting tracking:', error);
        }
    }

    startGeolocation() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser');
            return;
        }

        this.watchId = navigator.geolocation.watchPosition(
            (position) => this.handlePositionUpdate(position),
            (error) => console.error('Geolocation error:', error),
            {
                enableHighAccuracy: true,
                maximumAge: 0,
                timeout: 5000
            }
        );
    }

    async handlePositionUpdate(position) {
        const { latitude, longitude, accuracy, speed, heading } = position.coords;

        // Update Map
        if (!this.map) this.initMap(latitude, longitude);

        const newLatLng = [latitude, longitude];
        this.marker.setLatLng(newLatLng);
        this.routeLine.addLatLng(newLatLng);
        this.map.panTo(newLatLng);

        // Update UI
        document.getElementById('track-speed').textContent = `${Math.round(speed * 3.6 || 0)} km/h`;
        document.getElementById('track-status').textContent = 'Live Tracking Active';
        document.getElementById('stepper-progress').style.width = speed > 5 ? '80%' : '65%';
        document.getElementById('step-transit').classList.add('active');

        // Send to Socket.IO Server to broadcast immediately
        if (this.socket) {
            this.socket.emit('update_location', {
                tracking_id: this.trackingId, 
                latitude, longitude, accuracy, speed, heading
            });
        } else {
            // Fallback to PHP if socket fails
            try {
                const formData = new FormData();
                formData.append('action', 'update_location');
                formData.append('tracking_id', this.trackingId);
                formData.append('latitude', latitude);
                formData.append('longitude', longitude);
                await fetch(this.baseUrl, { method: 'POST', body: formData });
            } catch (ignored) {}
        }
    }

    // Volunteer: Stop Tracking
    async stopTracking() {
        if (this.watchId) navigator.geolocation.clearWatch(this.watchId);

        try {
            const formData = new FormData();
            formData.append('action', 'stop_tracking');
            formData.append('tracking_id', this.trackingId);

            await fetch(this.baseUrl, { method: 'POST', body: formData });

            if(this.socket) this.socket.emit('leave_tracking', this.trackingId);
            document.getElementById('tracking-modal').style.display = 'none';
            window.location.reload(); // Refresh to update status
        } catch (error) {
            console.error('Error stopping tracking:', error);
        }
    }

    // Donor/NGO: View Tracking
    async viewTracking(trackingId) {
        this.trackingId = trackingId;
        this.isVolunteer = false;
        this.showModal(false);
        
        if (this.socket) {
            this.socket.emit('join_tracking', this.trackingId);
        }

        // Fetch initial location to prefill Map
        await this.fetchLocation();
    }

    async fetchLocation() {
        try {
            const response = await fetch(`${this.baseUrl}?action=get_current_location&tracking_id=${this.trackingId}`);
            const data = await response.json();

            if (data.success && data.location) {
                this.updateMapFromSocket(data.location);
                document.getElementById('track-status').textContent = 'Live Update Received';
            } else {
                document.getElementById('track-status').textContent = 'Waiting for driver...';
            }
        } catch (error) {
            console.error('Error fetching location:', error);
        }
    }
    
    updateMapFromSocket(data) {
        const { latitude, longitude, speed } = data;

        if (!this.map) this.initMap(latitude, longitude);

        const newLatLng = [latitude, longitude];
        this.marker.setLatLng(newLatLng);
        this.routeLine.addLatLng(newLatLng);
        this.map.panTo(newLatLng);

        document.getElementById('track-speed').textContent = `${Math.round((speed || 0) * 3.6)} km/h`;
        document.getElementById('track-status').textContent = 'Live Data Connected';
        document.getElementById('stepper-progress').style.width = speed > 5 ? '80%' : '65%';
        document.getElementById('step-transit').classList.add('active');
    }

    showModal(isVolunteer) {
        const modal = document.getElementById('tracking-modal');
        modal.style.display = 'flex';

        const controls = document.getElementById('volunteer-controls');
        controls.style.display = isVolunteer ? 'flex' : 'none';
        
        // Leaflet edge case: force map to resize when parent display is flexed
        setTimeout(() => {
            if (this.map) this.map.invalidateSize();
        }, 100);
    }

    stopViewing() {
        if (this.watchId) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
        }
        if (this.socket && this.trackingId) {
            this.socket.emit('leave_tracking', this.trackingId);
        }
        this.trackingId = null;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    window.trackingSystem = new TrackingSystem();
});
