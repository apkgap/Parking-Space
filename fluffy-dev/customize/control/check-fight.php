<?php 
// ===== ตั้งค่า API KEY =====
define('AVSTACK_KEY', 'eca6a0d76e5630cebb73e589344f92ec'); // <— ใส่คีย์จริง

// ===== Enqueue JS หน้าเว็บทั้งหมด หรือเลือกหน้าเองตามต้องการ =====
add_action('wp_enqueue_scripts', function () {
    // ถ้าต้องการจำกัดเฉพาะบางเพจ ใช้ if (is_page(123)) { ... }
    wp_enqueue_script('flight-lookup-js', get_template_directory_uri() . '/customize/js/aviationstack/flight-lookup.js', ['jquery'], '1.0', true);
    wp_localize_script('flight-lookup-js', 'FLIGHT_LOOKUP', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('flight_lookup_nonce'),
    ]);
    // สไตล์เล็กน้อย (optional)
    wp_add_inline_style('wp-block-library', '.flight-card{margin:6px 0;padding:8px;border:1px solid #e5e7eb;border-radius:8px}');
});

// ===== AJAX: ดึงข้อมูลจาก aviationstack ฝั่งเซิร์ฟเวอร์ =====
add_action('wp_ajax_flight_lookup', 'flight_lookup_ajax');
add_action('wp_ajax_nopriv_flight_lookup', 'flight_lookup_ajax');
function flight_lookup_ajax() {
    check_ajax_referer('flight_lookup_nonce', 'nonce');

    $flight_iata = isset($_GET['flight']) ? sanitize_text_field($_GET['flight']) : '';
    $flight_date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : ''; // YYYY-MM-DD (ถ้ามี)

    if (!$flight_iata) wp_send_json_error(['message' => 'Missing flight number']);
    if (!preg_match('/^[A-Z0-9]{2,3}\s?[0-9]{1,4}[A-Z]?$/i', $flight_iata)) {
        wp_send_json_error(['message' => 'รูปแบบหมายเลขเที่ยวบินไม่ถูกต้อง']);
    }

    $params = [
        'access_key'  => AVSTACK_KEY,
        'flight_iata' => strtoupper(str_replace(' ', '', $flight_iata)),
    ];
    if ($flight_date) $params['flight_date'] = $flight_date;

    $url = 'http://api.aviationstack.com/v1/flights?' . http_build_query($params);
    $res = wp_remote_get($url, ['timeout' => 20]);

    if (is_wp_error($res)) wp_send_json_error(['message' => $res->get_error_message()]);
    $code = wp_remote_retrieve_response_code($res);
    $body = json_decode(wp_remote_retrieve_body($res), true);

    if ($code !== 200 || empty($body['data'])) wp_send_json_error(['message' => 'ไม่พบข้อมูลเที่ยวบิน']);
    $best = $body['data'][0];

    $out = [
        'flight_iata'  => $best['flight']['iata'] ?? '',
        'airline'      => $best['airline']['name'] ?? '',
        'status'       => $best['flight_status'] ?? '',
        'dep_airport'  => $best['departure']['airport'] ?? '',
        'dep_iata'     => $best['departure']['iata'] ?? '',
        'dep_scheduled'=> $best['departure']['scheduled'] ?? '',
        'dep_terminal' => $best['departure']['terminal'] ?? '',
        'dep_gate'     => $best['departure']['gate'] ?? '',
        'arr_airport'  => $best['arrival']['airport'] ?? '',
        'arr_iata'     => $best['arrival']['iata'] ?? '',
        'arr_scheduled'=> $best['arrival']['scheduled'] ?? '',
        'arr_terminal' => $best['arrival']['terminal'] ?? '',
        'arr_gate'     => $best['arrival']['gate'] ?? '',
    ];
    wp_send_json_success($out);
}