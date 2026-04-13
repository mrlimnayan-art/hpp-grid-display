<?php
/**
 * Plugin Name: HangPoPok Grid Stock Display (Slave Version)
 * Description: បង្ហាញស្លាកស្តុកនៅលើ Grid Post (មានភ្ជាប់ Auto-Update ពី GitHub)
 * Version: 1.5
 * Author: WP Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ==========================================
// ប្រព័ន្ធ AUTO-UPDATE ពី GITHUB
// ==========================================
$puc_path = __DIR__ . '/plugin-update-checker-5.6/plugin-update-checker.php';
if ( file_exists( $puc_path ) ) {
    require_once $puc_path;
    $myUpdateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/mrlimnayan-art/hpp-grid-display/', // ប្តូរទៅកាន់ Link Repo ពិតប្រាកដ
        __FILE__,
        'hpp-grid-display-slave'
    );
    $myUpdateChecker->setBranch('main');
}

add_filter( 'the_title', 'hpp_safe_stock_badge_display', 10, 2 );

function hpp_safe_stock_badge_display( $title, $id = null ) {
    if ( empty( $id ) || is_admin() || is_nav_menu_item( $id ) || is_singular() || ! in_the_loop() ) {
        return $title;
    }

    if ( ! in_array( get_post_type( $id ), array( 'post', 'product' ) ) ) {
        return $title;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'hpp_sync_data_full';
    
    $status = $wpdb->get_var( $wpdb->prepare( "SELECT stock_status FROM $table_name WHERE post_id = %d", $id ) );
    
    if ( ! empty( $status ) ) {
        $bg_color = '#46b450';
        if ( mb_strpos( $status, 'ជិត' ) !== false ) {
            $bg_color = '#ff9800'; 
        } elseif ( mb_strpos( $status, 'អស់' ) !== false ) {
            $bg_color = '#d63638'; 
        }

        $badge_html = sprintf(
            '<br><span class="hpp-grid-badge" style="background:%s; color:#fff; padding:3px 10px; border-radius:4px; font-size:11px; font-weight:bold; display:inline-block; margin-top:8px; line-height:1.5; text-transform:uppercase; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">%s</span>',
            $bg_color,
            esc_html( $status )
        );
        
        return $title . $badge_html;
    }
    
    return $title;
}

add_action( 'wp_head', function() {
    echo '<style>.hpp-grid-badge { clear: both; pointer-events: none; }</style>';
});