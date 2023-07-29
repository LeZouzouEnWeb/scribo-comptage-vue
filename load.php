<?php

// Étape 8: Créer la table dans la base de données lors de l'activation du plugin
function create_views_counter_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'scribo_views_counter';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        post_id INT NOT NULL,
        if_page INT NOT NULL,
        if_article INT NOT NULL,
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
    $table_name = $wpdb->prefix . 'scribo_excluded_ips';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        ip_address VARCHAR(45) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
register_activation_hook(WPSCV_URI_FILE, 'create_excluded_ips_table');


// Étape 1: Ajouter un menu parent pour "Exclusion d'IP"
function custom_views_counter_settings_page()
{
    // Étape 1.1: Ajouter un menu parent pour "Exclusion d'IP" dans le menu "Réglages"

    // Étape 2: Ajouter la sous-page pour le nombre de vues par date
    add_menu_page(
        'Nombre de vues par date', // Étape 2.2: Titre de la sous-page dans le backend
        'Nombre de vues par date', // Étape 2.3: Texte du menu de la sous-page dans le backend
        'manage_options', // Étape 2.4: Capacité utilisateur requise pour afficher la sous-page
        'custom-views-counter-settings', // Étape 2.5: Slug de la sous-page
        'custom_views_counter_render_views_by_date', // Étape 2.6: Fonction de rendu pour la sous-page
        'dashicons-admin-generic' // Étape 1.7: Icône du menu (facultatif)
    );

    // Ajouter la sous-page pour le nombre de vues par page et par article
    add_submenu_page(
        'custom-views-counter-settings',
        'Nombre de vues par page et par article',
        'Vues par page/article',
        'manage_options',
        'custom-views-counter-views-by-page-article',
        'custom_views_counter_render_views_by_page_article'
    );
    add_submenu_page(
        'custom-views-counter-settings', // Étape 2.1: Slug du menu parent (même que dans add_menu_page)
        'Exclusion d\'IP', // Étape 1.2: Titre de la page dans le backend
        'Exclusion d\'IP', // Étape 1.3: Texte du menu dans le backend
        'manage_options',  // Étape 1.4: Capacité utilisateur requise pour afficher la page
        'custom-views-counter-exlude', // Étape 1.5: Slug de la page
        'custom_views_counter_render_exclude', // Étape 1.6: Fonction de rendu pour la page

    );
    // Étape 4: Ajouter une nouvelle sous-page pour afficher les IP exclues
    add_submenu_page(
        'custom-views-counter-settings', // Slug du menu parent (même que dans add_menu_page)
        'IP Exclues', // Titre de la sous-page dans le backend
        'IP Exclues', // Texte du menu de la sous-page dans le backend
        'manage_options', // Capacité utilisateur requise pour afficher la sous-page
        'custom-views-counter-excluded-ip', // Slug de la sous-page
        'custom_views_counter_render_excluded_ip' // Fonction de rendu pour la sous-page
    );
}
add_action('admin_menu', 'custom_views_counter_settings_page');