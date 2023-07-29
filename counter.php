<?php
function record_views()
{
    if (is_singular()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'scribo_views_counter';

        $user_ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $post_id = get_the_ID();

        $post_type = get_post_type($post_id);
        $post_articl_num = 0;
        $post_page_num = 0;
        if ($post_type === 'post') {
            // C'est un article (post)
            $post_articl_num = 1;
        } elseif ($post_type === 'page') {
            // C'est une page (page)
            $post_page_num = 1;
        }
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
            $wpdb->insert($table_name, array('post_id' => $post_id, 'if_article' => $post_articl_num, 'if_page' => $post_page_num, 'user_ip' => $user_ip, 'user_agent' => $user_agent, 'view_date' => $current_date));
        }
    }
}
add_action('wp', 'record_views');

// Étape 4: Fonction de rendu pour la sous-page du nombre de vues par date















function custom_views_counter_render_views_by_date()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'scribo_views_counter';

    $views_by_info = array();

    $results = $wpdb->get_results(
        "SELECT view_date, user_ip, user_agent, SUM(if_page) AS total_page, SUM(if_article) AS total_article 
         FROM $table_name
         GROUP BY view_date, user_ip, user_agent"
    );

    if ($results) {
        foreach ($results as $result) {
            $date = $result->view_date;
            $user_ip = $result->user_ip;
            $user_agent = $result->user_agent;
            $article_views = $result->total_article;
            $page_views = $result->total_page;

            $views_by_info[] = array(
                'date' => $date,
                'user_ip' => $user_ip,
                'user_agent' => $user_agent,
                'article_views' => $article_views,
                'page_views' => $page_views
            );
        }
    }

    // Affichage du tableau des vues par date, IP et PC
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Date</th><th>IP</th><th>User Agent</th><th>Articles vus</th><th>Pages vues</th></tr></thead>';
    echo '<tbody>';

    foreach ($views_by_info as $views) {
        echo '<tr>';
        echo '<td>' . $views['date'] . '</td>';
        echo '<td>' . $views['user_ip'] . '</td>';
        echo '<td>' . $views['user_agent'] . '</td>';
        echo '<td>' . $views['article_views'] . '</td>';
        echo '<td>' . $views['page_views'] . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
}























function custom_views_counter_render_views_by_page_article()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'scribo_views_counter';

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