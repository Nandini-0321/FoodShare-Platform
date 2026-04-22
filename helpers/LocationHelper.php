<?php
/**
 * Location Helper Class
 * Handles geocoding, distance calculations, and routing
 */

class LocationHelper {
    
    /**
     * Geocode an address to latitude and longitude using OpenStreetMap Nominatim
     * @param string $address Full address to geocode
     * @return array|null Array with 'lat', 'lon', 'display_name' or null on failure
     */
    public static function geocodeAddress($address) {
        if (empty($address)) {
            return null;
        }
        
        // Build Nominatim API URL
        $url = "https://nominatim.openstreetmap.org/search?" . http_build_query([
            'q' => $address,
            'format' => 'json',
            'limit' => 1,
            'addressdetails' => 1
        ]);
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'FoodShare App/1.0 (Food Donation Platform)');
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200 && $response) {
                $data = json_decode($response, true);
                
                if (!empty($data[0])) {
                    return [
                        'lat' => floatval($data[0]['lat']),
                        'lon' => floatval($data[0]['lon']),
                        'display_name' => $data[0]['display_name'] ?? $address
                    ];
                }
            }
        } catch (Exception $e) {
            error_log("Geocoding error: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Calculate distance between two points using Haversine formula
     * @param float $lat1 Latitude of point 1
     * @param float $lon1 Longitude of point 1
     * @param float $lat2 Latitude of point 2
     * @param float $lon2 Longitude of point 2
     * @return float Distance in kilometers
     */
    public static function haversineDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // Earth's radius in kilometers
        
        // Convert degrees to radians
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        
        // Haversine formula
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        
        return round($distance, 2); // Return distance rounded to 2 decimal places
    }
    
    /**
     * Get driving route information using OpenRouteService API
     * @param float $startLat Starting latitude
     * @param float $startLon Starting longitude
     * @param float $endLat Ending latitude
     * @param float $endLon Ending longitude
     * @return array|null Route info with 'distance', 'duration', 'route' or null
     */
    public static function getDrivingRoute($startLat, $startLon, $endLat, $endLon) {
        // For now, return calculated straight-line distance
        // To enable routing, get free API key from https://openrouteservice.org/
        $apiKey = ''; // Add your OpenRouteService API key here
        
        if (empty($apiKey)) {
            // Fallback to Haversine distance when no API key
            $distance = self::haversineDistance($startLat, $startLon, $endLat, $endLon);
            
            // Estimate time: assume average speed of 40 km/h in city traffic
            $estimatedTime = ($distance / 40) * 60; // Convert to minutes
            
            return [
                'distance' => $distance,
                'duration' => round($estimatedTime),
                'route_description' => 'Estimated straight-line distance',
                'is_estimated' => true
            ];
        }
        
        // If API key is available, use OpenRouteService
        try {
            $url = "https://api.openrouteservice.org/v2/directions/driving-car?" . http_build_query([
                'api_key' => $apiKey,
                'start' => "$startLon,$startLat",
                'end' => "$endLon,$endLat"
            ]);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200 && $response) {
                $data = json_decode($response, true);
                
                if (!empty($data['features'][0]['properties'])) {
                    $props = $data['features'][0]['properties'];
                    $segments = $props['segments'][0];
                    
                    return [
                        'distance' => round($segments['distance'] / 1000, 2), // Convert m to km
                        'duration' => round($segments['duration'] / 60), // Convert s to minutes
                        'route_description' => 'Driving route via road network',
                        'is_estimated' => false
                    ];
                }
            }
        } catch (Exception $e) {
            error_log("Routing error: " . $e->getMessage());
        }
        
        // Fallback to Haversine if API fails
        return self::getDrivingRoute($startLat, $startLon, $endLat, $endLon);
    }
    
    /**
     * Format distance for display
     * @param float $distance Distance in kilometers
     * @return string Formatted distance string
     */
    public static function formatDistance($distance) {
        if ($distance < 1) {
            return round($distance * 1000) . ' m';
        } else {
            return $distance . ' km';
        }
    }
}
?>
