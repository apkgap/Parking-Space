<?php
if ( ! defined('FLIGHTAPI_KEY') ) {
    define('FLIGHTAPI_KEY', '68c546e77104692ea76fa1a6');
}

add_action('wp_enqueue_scripts', function () {
    wp_register_script('flight-search-dummy', false);
    wp_enqueue_script('flight-search-dummy');

    wp_enqueue_script(
        'flight-lookup',
        get_stylesheet_directory_uri() . '/customize/js/aviationstack/flight-lookup.js',
        ['jquery', 'jquery-ui-autocomplete'],
        '1.0.3',
        true
    );

    wp_localize_script('flight-lookup', 'akpFlightSearch', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('akp_flight_nonce'),
    ]);
});

add_action('wp_ajax_akp_flight_search',        'theme_flight_search_handler');
add_action('wp_ajax_nopriv_akp_flight_search', 'theme_flight_search_handler');

function theme_flight_search_handler() {
    error_log('Flight search handler called');
    error_log('POST data: ' . print_r($_POST, true));
    
    check_ajax_referer('akp_flight_nonce');

    $api_key = FLIGHTAPI_KEY;
    error_log('API Key: ' . $api_key);
    
    if (empty($api_key)) {
        error_log('API key validation failed');
        wp_send_json(['flights' => [], 'more' => false, 'error' => 'missing_api_key']);
    }

    // Parse flight IATA code to extract airline and flight number
    $flight_iata = isset($_POST['flight_iata']) ? sanitize_text_field(wp_unslash($_POST['flight_iata'])) : '';
    
    if (empty($flight_iata)) {
        wp_send_json(['flights' => [], 'more' => false, 'error' => 'missing_flight_code']);
    }
    
    // Extract airline code (first 2-3 letters) and flight number
    if (preg_match('/^([A-Z]{2,3})(\d+)$/', strtoupper($flight_iata), $matches)) {
        $airline_code = $matches[1];
        $flight_number = $matches[2];
    } else {
        wp_send_json(['flights' => [], 'more' => false, 'error' => 'invalid_flight_format']);
    }
    
    // Use today's date in YYYYMMDD format
    $date = date('Ymd');
    
    $url = "https://api.flightapi.io/airline/{$api_key}?num={$flight_number}&name={$airline_code}&date={$date}";
    error_log('API URL: ' . $url);
    
    $resp = wp_remote_get($url, [
        'timeout' => 12, 
        'redirection' => 3,
        'headers' => [
            'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
        ]
    ]);

    if (is_wp_error($resp)) {
        error_log('WP Remote Error: ' . $resp->get_error_message());
        wp_send_json(['flights' => [], 'more' => false, 'error' => 'fetch_failed']);
    }

    $body = wp_remote_retrieve_body($resp);
    error_log('API Response Body: ' . $body);
    
    $json = json_decode($body, true);
    if (!is_array($json)) {
        error_log('JSON decode failed');
        wp_send_json(['flights' => [], 'more' => false, 'error' => 'bad_json']);
    }

    // FlightAPI.io returns array directly, convert to AviationStack format for compatibility
    $flights = [];
    
    if (is_array($json) && !empty($json)) {
        foreach ($json as $flight_data) {
            // Convert FlightAPI.io format to AviationStack-like format
            $converted_flight = [
                'flight' => [
                    'iata' => $flight_iata,
                    'number' => $flight_iata
                ],
                'airline' => [
                    'name' => $airline_code . ' Airlines'
                ],
                'departure' => [],
                'arrival' => []
            ];
            
            // Map departure data if available
            if (isset($flight_data['departure'])) {
                $dep = $flight_data['departure'];
                $converted_flight['departure'] = [
                    'airport' => isset($dep['airport']) ? $dep['airport'] : (isset($dep['airportCode']) ? $dep['airportCode'] : ''),
                    'scheduled' => isset($dep['departureDateTime']) ? $dep['departureDateTime'] : null,
                    'iata' => isset($dep['airportCode']) ? $dep['airportCode'] : '',
                    'terminal' => isset($dep['terminal']) ? $dep['terminal'] : null
                ];
            }
            
            // Map arrival data if available  
            if (isset($flight_data['arrival'])) {
                $arr = $flight_data['arrival'];
                $converted_flight['arrival'] = [
                    'airport' => isset($arr['airport']) ? $arr['airport'] : (isset($arr['airportCode']) ? $arr['airportCode'] : ''),
                    'scheduled' => isset($arr['arrivalDateTime']) ? $arr['arrivalDateTime'] : null,
                    'iata' => isset($arr['airportCode']) ? $arr['airportCode'] : '',
                    'terminal' => isset($arr['terminal']) ? $arr['terminal'] : null
                ];
            }
            
            $flights[] = $converted_flight;
        }
    }
    
    error_log('Flight data count: ' . count($flights));
    
    wp_send_json([
        'flights' => $flights,
        'more'    => false
    ]);
}