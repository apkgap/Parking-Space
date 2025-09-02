<?php
/**
 * REST API Endpoints Module
 * Handles all custom REST API endpoints for the application
 */

/**
 * Register vehicle type API endpoint
 */
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/vehicle_type', array(
        'methods' => 'GET',
        'callback' => 'get_custom_vehicle_type_data',
        'permission_callback' => '__return_true',
    ));
});

function get_custom_vehicle_type_data($request) {
    $args = array(
        'post_type' => 'vehicle_type',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );

    $query = new WP_Query($args);
    $data = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $data[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'max_seats' => get_field('max_seats'),
                'pricing_rate' => get_field('pricing_rate'),
            );
        }
        wp_reset_postdata();
    }

    return rest_ensure_response($data);
}

/**
 * Register parking space API endpoint
 */
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/parking_space', array(
        'methods' => 'GET',
        'callback' => 'get_parking_space_data',
        'permission_callback' => '__return_true',
        'args' => array(
            'location' => array(
                'required' => false,
                'type' => 'string',
                'description' => 'Filter parking spaces by location taxonomy',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'locate_id' => array(
                'required' => false,
                'type' => 'integer',
                'description' => 'Filter parking spaces by location ID',
                'sanitize_callback' => 'absint',
            ),
        ),
    ));
});

function get_parking_space_data($request) {
    $args = array(
        'post_type' => 'parking_space',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'asc',
    );
    
    $location = $request->get_param('location');
    $locate_id = $request->get_param('locate_id');
    
    if (!empty($location)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'location',
                'field'    => 'slug',
                'terms'    => $location,
            ),
        );
    }
    if (!empty($locate_id)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'location',
                'field'    => 'id',
                'terms'    => $locate_id,
            ),
        );
    }
    
    $query = new WP_Query($args);
    $data = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $location_terms = get_the_terms(get_the_ID(), 'location');
            $location_name = !is_wp_error($location_terms) && !empty($location_terms) ? $location_terms[0]->name : '';
            $location_slug = !is_wp_error($location_terms) && !empty($location_terms) ? $location_terms[0]->slug : '';
            $location_id = !is_wp_error($location_terms) && !empty($location_terms) ? $location_terms[0]->term_id : '';
            
            $data[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'parking_id' => get_field('parking_id'),
                'location' => array(
                    'id' => $location_id,
                    'name' => $location_name,
                    'slug' => $location_slug                    
                ),
                'position' => array(
                    'x' => get_field('position')['position_x'] ?? null,
                    'y' => get_field('position')['position_y'] ?? null,
                ),
            );
        }
        wp_reset_postdata();
    }
    
    return rest_ensure_response($data);
}

/**
 * Register taxonomy terms API endpoint
 */
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/taxonomy_terms/(?P<taxonomy>[a-zA-Z0-9_-]+)', array(
        'methods' => 'GET',
        'callback' => 'get_taxonomy_terms',
        'permission_callback' => '__return_true',
        'args' => array(
            'taxonomy' => array(
                'required' => true,
                'type' => 'string',
                'description' => 'The taxonomy to retrieve terms from',
            ),
        ),
    ));
});

function get_taxonomy_terms($request) {
    $taxonomy = $request['taxonomy'];
    
    if (!taxonomy_exists($taxonomy)) {
        return new WP_Error('invalid_taxonomy', 'Invalid taxonomy', array('status' => 404));
    }
    
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ));
    
    if (is_wp_error($terms)) {
        return $terms;
    }
    
    $formatted_terms = array();
    foreach ($terms as $term) {
        $formatted_terms[] = array(
            'id' => $term->term_id,
            'title' => $term->name,
        );
    }
    
    return rest_ensure_response($formatted_terms);
}
?>
