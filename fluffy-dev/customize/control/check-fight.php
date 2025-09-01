<?php
if ( ! defined('AVIATIONSTACK_KEY') ) {
    define('AVIATIONSTACK_KEY', 'eca6a0d76e5630cebb73e589344f92ec');
}

add_action('wp_enqueue_scripts', function () {
    wp_register_script('flight-search-dummy', false);
    wp_enqueue_script('flight-search-dummy');

    wp_enqueue_script(
        'flight-lookup',
        get_stylesheet_directory_uri() . '/customize/js/aviationstack/flight-lookup.js',
        ['jquery', 'jquery-ui-autocomplete'],
        '1.0.2',
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

    $api_key = AVIATIONSTACK_KEY;
    error_log('API Key: ' . $api_key);
    
    if (empty($api_key)) {
        error_log('API key validation failed');
        wp_send_json(['flights' => [], 'more' => false, 'error' => 'missing_api_key']);
    }

    $limit  = isset($_POST['limit'])  ? min(50, max(1, (int) $_POST['limit']))  : 15;
    $offset = isset($_POST['offset']) ? max(0, (int) $_POST['offset']) : 0;

    $params = [
        'access_key' => $api_key,
        'limit'      => $limit,
        'offset'     => $offset,
    ];

    foreach (['flight_iata','airline_iata','dep_iata','arr_iata','flight_date'] as $field) {
        if (!empty($_POST[$field])) {
            $params[$field] = sanitize_text_field( wp_unslash($_POST[$field]) );
        }
    }

    $url  = 'http://api.aviationstack.com/v1/flights?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    error_log('API URL: ' . $url);
    
    $resp = wp_remote_get($url, ['timeout' => 12, 'redirection' => 3]);

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

    $data   = isset($json['data']) && is_array($json['data']) ? $json['data'] : [];
    $paging = isset($json['pagination']) ? $json['pagination'] : null;

    error_log('Flight data count: ' . count($data));

    $more = false;
    if (is_array($paging) && isset($paging['total'],$paging['offset'],$paging['limit'])) {
        $more = ( (int) $paging['offset'] + (int) $paging['limit'] ) < (int) $paging['total'];
    }

    wp_send_json([
        'flights' => $data,
        'more'    => $more,
    ]);
}