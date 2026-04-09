<?php
require_once '../config/config.php';
require_login();
require_any_role(['Driver', 'Admin']);

$page_title = "Offer a Ride";
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid security token. Please try again.";
    } else {
        $origin = sanitize_input($_POST['origin'] ?? '');
        $destination = sanitize_input($_POST['destination'] ?? '');
        $date = sanitize_input($_POST['date'] ?? '');
        $time = sanitize_input($_POST['time'] ?? '');
        $seats = intval($_POST['seats'] ?? 0);
        $price = floatval($_POST['price'] ?? 0);
        $distance = floatval($_POST['distance'] ?? 0);
        $vehicle_type = sanitize_input($_POST['vehicle_type'] ?? '');
        $vehicle_plate = sanitize_input($_POST['vehicle_plate'] ?? '');
        $notes = sanitize_input($_POST['notes'] ?? '');
        
        // Validation
        if (empty($origin) || empty($destination) || empty($date) || empty($time)) {
            $error = "Please fill in all required fields.";
        } elseif ($seats < 1 || $seats > 6) {
            $error = "Number of seats must be between 1 and 6.";
        } elseif ($price < 0) {
            $error = "Price cannot be negative.";
        } elseif (strtotime($date) < strtotime('today')) {
            $error = "Departure date cannot be in the past.";
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO Rides (
                        DriverID, OriginLocation, DestinationLocation, 
                        DepartureDate, DepartureTime, AvailableSeats, TotalSeats,
                        PricePerSeat, Distance, VehicleType, VehiclePlateNumber, Notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $_SESSION['user_id'],
                    $origin,
                    $destination,
                    $date,
                    $time,
                    $seats,
                    $seats,
                    $price,
                    $distance,
                    $vehicle_type,
                    $vehicle_plate,
                    $notes
                ]);
                
                $ride_id = $pdo->lastInsertId();
                
                // Log activity
                log_activity($pdo, $_SESSION['user_id'], 'create_ride', 'Ride', $ride_id);
                
                set_flash_message('success', 'Ride created successfully!');
                redirect(SITE_URL . '/rides/view.php?id=' . $ride_id);
                
            } catch (PDOException $e) {
                error_log("Create ride error: " . $e->getMessage());
                $error = "Failed to create ride. Please try again.";
            }
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Offer a Ride</h3>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo e($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="createRideForm">
                        <?php echo csrf_field(); ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="origin" class="form-label">Origin Location *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt text-success"></i></span>
                                    <input type="text" class="form-control" id="origin" name="origin" 
                                           placeholder="Search for origin location..." required autocomplete="off">
                                    <input type="hidden" id="origin_lat" name="origin_lat">
                                    <input type="hidden" id="origin_lon" name="origin_lon">
                                </div>
                                <div id="origin_suggestions" class="location-suggestions"></div>
                                <small class="text-muted">Start typing to search locations in Malaysia</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="destination" class="form-label">Destination *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt text-danger"></i></span>
                                    <input type="text" class="form-control" id="destination" name="destination" 
                                           placeholder="Search for destination..." required autocomplete="off">
                                    <input type="hidden" id="destination_lat" name="destination_lat">
                                    <input type="hidden" id="destination_lon" name="destination_lon">
                                </div>
                                <div id="destination_suggestions" class="location-suggestions"></div>
                                <small class="text-muted">Start typing to search locations in Malaysia</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">Departure Date *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="date" class="form-control" id="date" name="date" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="time" class="form-label">Departure Time *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                    <input type="time" class="form-control" id="time" name="time" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="seats" class="form-label">Available Seats *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-users"></i></span>
                                    <input type="number" class="form-control" id="seats" name="seats" 
                                           min="1" max="6" value="3" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="price" class="form-label">Price per Seat (RM)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    <input type="number" class="form-control" id="price" name="price" 
                                           min="0" step="0.01" value="5.00">
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="distance" class="form-label">Distance (km)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-road"></i></span>
                                    <input type="number" class="form-control" id="distance" name="distance" 
                                           min="0" step="0.1" value="" readonly required 
                                           style="background-color: #f8f9fa; font-weight: 500;">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vehicle_type" class="form-label">Vehicle Type</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-car"></i></span>
                                    <input type="text" class="form-control" id="vehicle_type" name="vehicle_type" 
                                           placeholder="e.g., Honda Civic">
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="vehicle_plate" class="form-label">Vehicle Plate Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" class="form-control" id="vehicle_plate" name="vehicle_plate" 
                                           placeholder="e.g., ABC 1234">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Any additional information for passengers (pickup points, preferences, etc.)"></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Tip:</strong> Provide clear pickup and drop-off instructions to make carpooling easier!
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo SITE_URL; ?>/dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle me-2"></i>Create Ride Offer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Eco Impact Preview -->
            <div class="card mt-3 border-success">
                <div class="card-body">
                    <h6 class="text-success"><i class="fas fa-leaf me-2"></i>Estimated Environmental Impact</h6>
                    <p class="mb-0 small text-muted">
                        By offering this ride, you can help save up to <strong id="co2Preview">0</strong> kg of CO₂ emissions 
                        and earn approximately <strong id="pointsPreview">0</strong> eco-points!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ===================================
// LocationIQ API Integration
// ===================================

const LOCATIONIQ_API_KEY = 'pk.d4fa3cafad8fecf1d0558e0089b1d36e';

// Debounce function to limit API calls
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Distance measurement function (Haversine formula)
// Source - https://stackoverflow.com/a/11172685
function measure(lat1, lon1, lat2, lon2) {
    var R = 6378.137; // Radius of earth in KM
    var dLat = lat2 * Math.PI / 180 - lat1 * Math.PI / 180;
    var dLon = lon2 * Math.PI / 180 - lon1 * Math.PI / 180;
    var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon/2) * Math.sin(dLon/2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    var d = R * c;
    return d * 1000; // meters
}

// Calculate distance between origin and destination
window.calcRideDistance = function() {
    // Get coordinate values
    var oLat = document.getElementById('origin_lat').value;
    var oLon = document.getElementById('origin_lon').value;
    var dLat = document.getElementById('destination_lat').value;
    var dLon = document.getElementById('destination_lon').value;
    
    // Check if we have BOTH locations set
    if (!oLat || !oLon || !dLat || !dLon) {
        return;
    }
    
    // Parse coordinates to numbers
    var originLat = parseFloat(oLat);
    var originLon = parseFloat(oLon);
    var destLat = parseFloat(dLat);
    var destLon = parseFloat(dLon);
    
    // Calculate distance using Haversine formula
    var distanceMeters = measure(originLat, originLon, destLat, destLon);
    var distance = distanceMeters / 1000; // Convert to km
    
    // Set the distance field
    var distanceField = document.getElementById('distance');
    distanceField.value = distance.toFixed(1);
    
    // Update eco impact preview
    updateImpactPreview();
}

// Search locations using LocationIQ Autocomplete API
async function searchLocation(query, inputId) {
    if (query.length < 3) {
        document.getElementById(inputId + '_suggestions').innerHTML = '';
        return;
    }
    
    const suggestionsDiv = document.getElementById(inputId + '_suggestions');
    suggestionsDiv.innerHTML = '<div class="suggestion-item loading"><i class="fas fa-spinner fa-spin me-2"></i>Searching...</div>';
    
    try {
        // LocationIQ Autocomplete API - biased towards Malaysia
        const url = `https://api.locationiq.com/v1/autocomplete?key=${LOCATIONIQ_API_KEY}&q=${encodeURIComponent(query)}&countrycodes=my&limit=5&format=json&dedupe=1`;
        
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error('API request failed');
        }
        
        const data = await response.json();
        
        if (data && data.length > 0) {
            suggestionsDiv.innerHTML = '';
            
            data.forEach(place => {
                const div = document.createElement('div');
                div.className = 'suggestion-item';
                div.innerHTML = `
                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                    <span>${place.display_name}</span>
                `;
                div.onclick = () => selectLocation(place, inputId);
                suggestionsDiv.appendChild(div);
            });
        } else {
            suggestionsDiv.innerHTML = '<div class="suggestion-item no-results"><i class="fas fa-info-circle me-2"></i>No locations found</div>';
        }
    } catch (error) {
        console.error('Error fetching locations:', error);
        suggestionsDiv.innerHTML = '<div class="suggestion-item error"><i class="fas fa-exclamation-triangle me-2"></i>Error loading locations. Please try again.</div>';
    }
}

// Handle location selection
function selectLocation(place, inputId) {
    // Set the input value and coordinates
    document.getElementById(inputId).value = place.display_name;
    document.getElementById(inputId + '_lat').value = place.lat;
    document.getElementById(inputId + '_lon').value = place.lon;
    document.getElementById(inputId + '_suggestions').innerHTML = '';
    
    // Add visual feedback (green border)
    document.getElementById(inputId).classList.add('location-selected');
    
    // Calculate distance (will only work if both locations are set)
    window.calcRideDistance();
}

// Create debounced search functions
const debouncedSearchOrigin = debounce((query) => searchLocation(query, 'origin'), 500);
const debouncedSearchDestination = debounce((query) => searchLocation(query, 'destination'), 500);

// Calculate and display estimated impact
function updateImpactPreview() {
    const distance = parseFloat(document.getElementById('distance').value) || 0;
    const seats = parseInt(document.getElementById('seats').value) || 1;
    
    if (distance > 0 && seats > 0) {
        // CO2 calculation: Average car emits 0.12 kg CO2 per km per passenger
        // By carpooling, we save emissions from (seats) additional cars
        const co2Saved = (distance * seats * 0.12).toFixed(1);
        
        // Eco-points calculation based on distance and passengers
        const ecoPoints = Math.ceil(10 + (distance * (seats + 1) * 0.5) + (seats * 5));
        
        // Update the display with animation
        const co2Element = document.getElementById('co2Preview');
        const pointsElement = document.getElementById('pointsPreview');
        
        co2Element.textContent = co2Saved;
        pointsElement.textContent = ecoPoints;
        
        // Add highlight animation
        co2Element.style.color = '#28a745';
        pointsElement.style.color = '#28a745';
        co2Element.style.fontWeight = 'bold';
        pointsElement.style.fontWeight = 'bold';
        
        // Pulse animation
        co2Element.style.transform = 'scale(1.1)';
        pointsElement.style.transform = 'scale(1.1)';
        setTimeout(() => {
            co2Element.style.transform = 'scale(1)';
            pointsElement.style.transform = 'scale(1)';
        }, 300);
    } else {
        document.getElementById('co2Preview').textContent = '0';
        document.getElementById('pointsPreview').textContent = '0';
        document.getElementById('co2Preview').style.color = '#6c757d';
        document.getElementById('pointsPreview').style.color = '#6c757d';
    }
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Location search listeners
    document.getElementById('origin').addEventListener('input', function(e) {
        this.classList.remove('location-selected');
        document.getElementById('origin_lat').value = '';
        document.getElementById('origin_lon').value = '';
        debouncedSearchOrigin(e.target.value);
    });
    
    document.getElementById('destination').addEventListener('input', function(e) {
        this.classList.remove('location-selected');
        document.getElementById('destination_lat').value = '';
        document.getElementById('destination_lon').value = '';
        debouncedSearchDestination(e.target.value);
    });
    
    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.col-md-6')) {
            document.getElementById('origin_suggestions').innerHTML = '';
            document.getElementById('destination_suggestions').innerHTML = '';
        }
    });
    
    // Prevent closing when clicking inside suggestions
    document.getElementById('origin_suggestions').addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    document.getElementById('destination_suggestions').addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Seats change listener
    document.getElementById('seats').addEventListener('change', updateImpactPreview);
    
    // Initialize preview
    updateImpactPreview();
});

// Form validation
document.getElementById('createRideForm').addEventListener('submit', function(e) {
    const origin = document.getElementById('origin').value.trim();
    const destination = document.getElementById('destination').value.trim();
    const originLat = document.getElementById('origin_lat').value;
    const destLat = document.getElementById('destination_lat').value;
    const date = document.getElementById('date').value;
    const time = document.getElementById('time').value;
    const distance = document.getElementById('distance').value;
    
    if (!origin || !destination || !date || !time) {
        e.preventDefault();
        alert('Please fill in all required fields');
        return false;
    }
    
    if (!originLat || !destLat) {
        e.preventDefault();
        alert('Please select locations from the suggestions dropdown');
        return false;
    }
    
    if (!distance || parseFloat(distance) <= 0) {
        e.preventDefault();
        alert('Distance could not be calculated. Please try selecting different locations.');
        return false;
    }
    
    // Check if date is not in the past
    const selectedDate = new Date(date + ' ' + time);
    const now = new Date();
    
    if (selectedDate < now) {
        e.preventDefault();
        alert('Departure date and time cannot be in the past');
        return false;
    }
});
</script>

<style>
/* Location suggestions dropdown */
.location-suggestions {
    position: absolute;
    z-index: 1000;
    background: white;
    border: 1px solid #ddd;
    border-radius: 0 0 4px 4px;
    margin-top: -1px;
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    width: calc(100% - 50px);
    margin-left: 50px;
}

.suggestion-item {
    padding: 12px 15px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s;
    font-size: 14px;
}

.suggestion-item:hover:not(.loading):not(.error):not(.no-results) {
    background-color: #e8f4f8;
}

.suggestion-item:last-child {
    border-bottom: none;
}

.suggestion-item.loading {
    color: #6c757d;
    cursor: default;
    background-color: #f8f9fa;
}

.suggestion-item.error {
    color: #dc3545;
    cursor: default;
    background-color: #f8d7da;
}

.suggestion-item.no-results {
    color: #6c757d;
    cursor: default;
}

/* Auto-calculated distance field */
#distance {
    transition: all 0.3s ease;
    font-size: 1.1rem;
}

#distance.is-valid {
    border-color: #28a745;
    background-color: #d4edda;
}

#distance.is-invalid {
    border-color: #dc3545;
    background-color: #f8d7da;
}

/* Preview animation */
#co2Preview, #pointsPreview {
    transition: all 0.3s ease;
    font-size: 1.2rem;
}

/* Input focus effects */
#origin:focus, #destination:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

/* Location selected state */
.location-selected {
    border-color: #28a745 !important;
    background-color: #f0f9f4;
}

/* Distance calculated message */
.distance-calculated-msg {
    display: block;
    margin-top: 5px;
    font-weight: 500;
}

/* Smooth transitions */
.form-control {
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, background-color 0.15s ease-in-out;
}
</style>

<?php include '../includes/footer.php'; ?>

