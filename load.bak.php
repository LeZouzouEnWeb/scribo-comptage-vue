<?php


// Étape 8: Créer la table dans la base de données lors de l'activation du plugin
function create_views_counter_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'views_counter';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        post_id INT NOT NULL,
        user_ip VARCHAR(45) NOT NULL,
        user_agent TEXT NOT NULL,
        view_date DATE NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
register_activation_hook(WPSCV_URI_FILE, 'create_views_counter_table');






function create_excluded_ips_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'excluded_ips';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        ip_address VARCHAR(45) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'create_excluded_ips_table');