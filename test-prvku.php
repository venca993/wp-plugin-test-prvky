<?php
/*
Plugin Name: Example Object Manager
Plugin URI: https://example.com
Description: Plugin pro správu objektů v WordPressu s vlastními shortcody.
Version: 1.0
Author: Example Author
License: GPL2
*/

// Vytvoření vlastního typu obsahu (objektu)
function example_register_custom_post_type() {
    $labels = array(
        'name'               => 'Prvky',
        'singular_name'      => 'Prvek',
        'menu_name'          => 'Prvky',
        'name_admin_bar'     => 'Prvek',
        'add_new'            => 'Přidat nový',
        'add_new_item'       => 'Přidat nový prvek',
        'new_item'           => 'Nový prvek',
        'edit_item'          => 'Upravit prvek',
        'view_item'          => 'Zobrazit prvek',
        'all_items'          => 'Všechny prvky',
        'search_items'       => 'Hledat prvky',
        'parent_item_colon'  => 'Rodičovský prvek:',
        'not_found'          => 'Prvky nenalezeny.',
        'not_found_in_trash' => 'Prvky v koši nenalezeny.'
    );

    $args = array(
        'labels'             => $labels,
        'description'        => 'Příklad vlastního typu obsahu pro správu objektů.',
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'example-object' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
        'menu_icon'          => 'dashicons-welcome-write-blog', // Ikona v menu
    );

    register_post_type( 'example_object', $args );
}
add_action( 'init', 'example_register_custom_post_type' );

// Registrace shortcode pro zobrazení jednotlivého prvku
function example_object_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 0 // Výchozí hodnota id
    ), $atts, 'example-object');

    // Načtení příslušného objektu na základě zadaného ID
    $object_id = absint($atts['id']);
    $object = get_post($object_id);

    // Pokud objekt nebyl nalezen, vrátíme prázdný řetězec
    if (!$object) {
        return '';
    }

    // Získání obsahu objektu (například titul a obsah)
    $object_title = get_the_title($object_id);
    $object_content = apply_filters('the_content', $object->post_content);

    // Vytvoření HTML kódu pro zobrazení objektu včetně shortcode
    $output = '<div class="example-object">';
    $output .= '<h2>' . esc_html($object_title) . '</h2>';
    $output .= '<div class="content">' . $object_content . '</div>';
    $output .= '<p><strong>Shortcode:</strong> [example-object id="' . $object_id . '"]</p>';
    $output .= '</div>';

    return $output;
}
add_shortcode('example-object', 'example_object_shortcode');

// Přidání meta boxu s shortcode do administračního rozhraní
function example_add_shortcode_meta_box() {
    add_meta_box(
        'example-shortcode',
        'Shortcode',
        'example_shortcode_meta_box_callback',
        'example_object',
        'side', // umístění na stránce
        'default'
    );
}
add_action('add_meta_boxes', 'example_add_shortcode_meta_box');

// Callback funkce pro zobrazení obsahu meta boxu s shortcode
function example_shortcode_meta_box_callback($post) {
    $shortcode = '[example-object id="' . $post->ID . '"]';
    echo '<p>Kopírujte a vložte tento shortcode do obsahu:</p>';
    echo '<input type="text" value="' . esc_attr($shortcode) . '" readonly="readonly" style="width: 100%;" onclick="this.select();">';
}


// Registrace shortcode pro zobrazení seznamu všech objektů
function example_all_objects_shortcode($atts) {
    $atts = shortcode_atts(array(
        'order' => 'ASC', // Výchozí pořadí
        'orderby' => 'title' // Výchozí řazení dle titulku
    ), $atts, 'example-all-objects');

    $args = array(
        'post_type' => 'example_object',
        'posts_per_page' => -1, // Získání všech objektů
        'orderby' => $atts['orderby'],
        'order' => $atts['order']
    );

    $objects = get_posts($args);

    // Pokud nejsou nalezeny žádné objekty, vrátíme prázdný řetězec
    if (empty($objects)) {
        return '';
    }

    // Vytvoření HTML kódu pro zobrazení seznamu všech objektů
    $output = '<div class="example-all-objects">';

    foreach ($objects as $object) {
        $output .= '<div class="example-object">';
        $output .= '<h2>' . esc_html($object->post_title) . '</h2>';
        $output .= '<div class="content">' . apply_filters('the_content', $object->post_content) . '</div>';
        $output .= '</div>';
    }

    $output .= '</div>';

    return $output;
}
add_shortcode('example-all-objects', 'example_all_objects_shortcode');




// Funkce pro přidání vlastního sloupce do seznamu příspěvků
function add_custom_column_to_example_object_list($columns) {
    // Přidání vlastního sloupce s názvem "Můj vlastní sloupec"
    $columns['my_custom_column'] = 'Můj vlastní sloupec';
    return $columns;
}
add_filter('manage_example_object_posts_columns', 'add_custom_column_to_example_object_list');


// Funkce pro vykreslení obsahu vlastního sloupce
function render_custom_column_content($column_name, $post_id) {
    // Zde můžete specifikovat, co se má zobrazit ve vašem vlastním sloupci
    if ($column_name === 'my_custom_column') {
        // Vykreslení hodnoty shorcodu
        echo esc_html('[example-object id="'.$post_id.'"]');
    }
}
add_action('manage_example_object_posts_custom_column', 'render_custom_column_content', 10, 2);
