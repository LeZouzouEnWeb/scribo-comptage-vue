<?php


// Fonction de rendu pour la page de réglages
function custom_views_counter_render_exclude()
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
    $table_name = $wpdb->prefix . 'scribo_excluded_ips';

    // Vérifier si un formulaire de suppression a été soumis
    if (isset($_POST['submit'])) {
        $ip_id = absint($_POST['ip_id']);
        $wpdb->delete($table_name, array('id' => $ip_id));
    }

    // Récupérer la liste des adresses IP exclues
    $excluded_ips = $wpdb->get_results("SELECT * FROM $table_name");

?>
<div class="wrap">
    <h1 id="mydesc">IP Exclues</h1>
    <table class="widefat" aria-describedby="mydesc">
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