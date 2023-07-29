<?php
function record_views()
{
    if (is_single()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'views_counter';

        $user_ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $post_id = get_the_ID();
        $current_date = date('Y-m-d');

        // Vérifier si la vue pour cette page ou article, cette adresse IP et cette date existe déjà dans la base de données
        $existing_view = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE post_id = %d AND user_ip = %s AND view_date = %s",
            $post_id,
            $user_ip,
            $current_date
        ));

        // Si la vue n'existe pas, l'enregistrer dans la base de données
        if (!$existing_view) {
            $wpdb->insert($table_name, array('post_id' => $post_id, 'user_ip' => $user_ip, 'user_agent' => $user_agent, 'view_date' => $current_date));
        }
    }
}
add_action('wp', 'record_views');

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

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
register_activation_hook(WPSCV_URI_FILE, 'create_excluded_ips_table');




// Étape 1: Ajouter un menu parent pour "Exclusion d'IP"
function custom_views_counter_settings_page()
{
    // Étape 1.1: Ajouter un menu parent pour "Exclusion d'IP" dans le menu "Réglages"
    add_menu_page(
        'Compteur de vue', // Étape 1.2: Titre de la page dans le backend
        'Exclusion d\'IP', // Étape 1.3: Texte du menu dans le backend
        'manage_options',  // Étape 1.4: Capacité utilisateur requise pour afficher la page
        'custom-views-counter-settings', // Étape 1.5: Slug de la page
        'custom_views_counter_render_settings', // Étape 1.6: Fonction de rendu pour la page
        'dashicons-admin-generic' // Étape 1.7: Icône du menu (facultatif)
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
    // Étape 2: Ajouter la sous-page pour le nombre de vues par date
    add_submenu_page(
        'custom-views-counter-settings', // Étape 2.1: Slug du menu parent (même que dans add_menu_page)
        'Nombre de vues par date', // Étape 2.2: Titre de la sous-page dans le backend
        'Nombre de vues par date', // Étape 2.3: Texte du menu de la sous-page dans le backend
        'manage_options', // Étape 2.4: Capacité utilisateur requise pour afficher la sous-page
        'custom-views-counter-views-by-date', // Étape 2.5: Slug de la sous-page
        'custom_views_counter_render_views_by_date' // Étape 2.6: Fonction de rendu pour la sous-page
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
}
add_action('admin_menu', 'custom_views_counter_settings_page');


// Fonction de rendu pour la page de réglages
function custom_views_counter_render_settings()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les autorisations nécessaires pour accéder à cette page.'));
    }

    // Vérifiez si le formulaire a été soumis et mettez à jour les options
    if (isset($_POST['custom_views_counter_submit'])) {
        $excluded_ips = sanitize_text_field($_POST['custom_views_counter_excluded_ips']);
        update_option('custom_views_counter_excluded_ips', $excluded_ips);
        echo '<div class="updated"><p>Les adresses IP exclues ont été enregistrées avec succès.</p></div>';
    }

    // Récupérer les adresses IP exclues actuelles
    $excluded_ips = get_option('custom_views_counter_excluded_ips', '');
?>
<div class="wrap">
    <h1>Exclusion d'IP pour Custom Views Counter</h1>
    <form method="post">
        <label for="custom_views_counter_excluded_ips">Adresses IP à exclure (séparées par des virgules) :</label><br>
        <input type="text" name="custom_views_counter_excluded_ips" id="custom_views_counter_excluded_ips"
            value="<?php echo esc_attr($excluded_ips); ?>" style="width: 400px;"><br><br>
        <input type="submit" name="custom_views_counter_submit" class="button button-primary"
            value="Enregistrer les adresses IP exclues">
    </form>
</div>
<?php
}


function custom_views_counter_render_excluded_ip()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'excluded_ips';

    // Vérifier si un formulaire de suppression a été soumis
    if (isset($_POST['submit'])) {
        $ip_id = absint($_POST['ip_id']);
        $wpdb->delete($table_name, array('id' => $ip_id));
    }

    // Récupérer la liste des adresses IP exclues
    $excluded_ips = $wpdb->get_results("SELECT * FROM $table_name");

?>
<div class="wrap">
    <h1>IP Exclues</h1>
    <table class="widefat">
        <thead>
            <tr>
                <th>#</th>
                <th>Adresse IP</th>
                <th>Supprimer</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $count = 1;
                foreach ($excluded_ips as $ip) :
                ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo esc_html($ip->ip_address); ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="ip_id" value="<?php echo $ip->id; ?>">
                        <button type="submit" name="submit" class="button button-secondary">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php
                    $count++;
                endforeach;
                ?>
        </tbody>
    </table>
</div>
<?php
}




// Étape 4: Fonction de rendu pour la sous-page du nombre de vues par date
function custom_views_counter_render_views_by_date()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'views_counter';

    $posts = get_posts(array('numberposts' => -1));

?>
<div class="wrap">
    <h1>Nombre de vues par date, par adresse IP et par utilisateur (PC) pour chaque page ou article</h1>

    <?php
        foreach ($posts as $post) {
            $post_id = $post->ID;
            $post_title = $post->post_title;

            $views_by_date_ip_pc = $wpdb->get_results($wpdb->prepare(
                "SELECT view_date, user_ip, user_agent, COUNT(*) as view_count 
                FROM $table_name 
                WHERE post_id = %d
                GROUP BY view_date, user_ip, user_agent
                ORDER BY view_date DESC",
                $post_id
            ));

            if (!empty($views_by_date_ip_pc)) {
        ?>
    <h2><?php echo esc_html($post_title); ?> (ID: <?php echo $post_id; ?>)</h2>
    <table class="widefat">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Adresse IP</th>
                <th>Utilisateur (PC)</th>
                <th>Nombre de vues</th>
            </tr>
        </thead>
        <tbody>
            <?php
                        $count = 1;
                        foreach ($views_by_date_ip_pc as $view) :
                        ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo $view->view_date; ?></td>
                <td><?php echo $view->user_ip; ?></td>
                <td><?php echo $view->user_agent; ?></td>
                <td><?php echo $view->view_count; ?></td>
            </tr>
            <?php
                            $count++;
                        endforeach;
                        ?>
        </tbody>
    </table>
    <br>
    <?php
            }
        }
        ?>
</div>
<?php
}



function custom_views_counter_render_views_by_page_article()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'views_counter';

    // Étape 4: Récupérer le nombre de vues par page et par article depuis la base de données
    $views_by_page_article = $wpdb->get_results(
        "SELECT post_id, COUNT(*) as view_count 
        FROM $table_name 
        GROUP BY post_id
        ORDER BY view_count DESC"
    );

    // Étape 5: Récupérer le nombre de vues par jour depuis la base de données
    $views_by_day = $wpdb->get_results(
        "SELECT view_date, COUNT(*) as view_count 
        FROM $table_name 
        GROUP BY view_date
        ORDER BY view_date DESC"
    );

    // Étape 6: Récupérer le nombre de vues par mois depuis la base de données
    $views_by_month = $wpdb->get_results(
        "SELECT DATE_FORMAT(view_date, '%Y-%m') AS view_month, COUNT(*) as view_count 
        FROM $table_name 
        GROUP BY view_month
        ORDER BY view_month DESC"
    );

    // Étape 7: Récupérer le nombre de vues par année depuis la base de données
    $views_by_year = $wpdb->get_results(
        "SELECT YEAR(view_date) AS view_year, COUNT(*) as view_count 
        FROM $table_name 
        GROUP BY view_year
        ORDER BY view_year DESC"
    );

    // Étape 8: Afficher les tableaux avec le nombre de vues par page et par article, par jour, par mois et par année
?>
<div class="wrap">
    <h1>Nombre de vues</h1>

    <!-- Tableau pour le nombre de vues par page et par article -->
    <h2>Par Page ou Article</h2>
    <table class="widefat">
        <thead>
            <tr>
                <th>#</th>
                <th>Page ou Article</th>
                <th>Nombre de vues</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $count = 1;
                foreach ($views_by_page_article as $view) :
                    $post_title = get_the_title($view->post_id);
                ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo esc_html($post_title); ?></td>
                <td><?php echo $view->view_count; ?></td>
            </tr>
            <?php
                    $count++;
                endforeach;
                ?>
        </tbody>
    </table>

    <!-- Tableau pour le nombre de vues par jour -->
    <h2>Par Jour</h2>
    <table class="widefat">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Nombre de vues</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $count = 1;
                foreach ($views_by_day as $view) :
                ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo $view->view_date; ?></td>
                <td><?php echo $view->view_count; ?></td>
            </tr>
            <?php
                    $count++;
                endforeach;
                ?>
        </tbody>
    </table>

    <!-- Tableau pour le nombre de vues par mois -->
    <h2>Par Mois</h2>
    <table class="widefat">
        <thead>
            <tr>
                <th>#</th>
                <th>Mois</th>
                <th>Nombre de vues</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $count = 1;
                foreach ($views_by_month as $view) :
                ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo $view->view_month; ?></td>
                <td><?php echo $view->view_count; ?></td>
            </tr>
            <?php
                    $count++;
                endforeach;
                ?>
        </tbody>
    </table>

    <!-- Tableau pour le nombre de vues par année -->
    <h2>Par Année</h2>
    <table class="widefat">
        <thead>
            <tr>
                <th>#</th>
                <th>Année</th>
                <th>Nombre de vues</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $count = 1;
                foreach ($views_by_year as $view) :
                ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo $view->view_year; ?></td>
                <td><?php echo $view->view_count; ?></td>
            </tr>
            <?php
                    $count++;
                endforeach;
                ?>
        </tbody>
    </table>
</div>
<?php
}