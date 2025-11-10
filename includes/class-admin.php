<?php
/**
 * Classe pour gérer l'interface admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPDSC_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Ajouter le menu admin
     */
    public function add_admin_menu() {
        add_menu_page(
            'Domain Security Checker',
            'Security Tests',
            'manage_options',
            'domain-security-checker',
            array($this, 'render_admin_page'),
            'dashicons-shield',
            30
        );
    }
    
    /**
     * Render la page admin
     */
    public function render_admin_page() {
        $database = new WPDSC_Database();
        
        // Pagination
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        $total_tests = $database->get_total_tests();
        $total_pages = ceil($total_tests / $per_page);
        
        $tests = $database->get_all_tests($per_page, $offset);
        
        // Afficher un test détaillé si demandé
        if (isset($_GET['view']) && !empty($_GET['view'])) {
            $test_id = intval($_GET['view']);
            $test = $database->get_test_by_id($test_id);
            
            if ($test) {
                $this->render_test_detail($test);
                return;
            }
        }
        
        ?>
        <div class="wrap">
            <h1>Tests de sécurité de domaine</h1>
            
            <div class="wpdsc-admin-stats">
                <div class="wpdsc-stat-box">
                    <h3><?php echo esc_html($total_tests); ?></h3>
                    <p>Tests effectués</p>
                </div>
            </div>
            
            <div class="wpdsc-admin-info">
                <p><strong>Shortcode à utiliser :</strong> <code>[domain_security_checker]</code></p>
                <p>Vous pouvez personnaliser le shortcode avec les attributs suivants :</p>
                <ul>
                    <li><code>title="Votre titre"</code> - Modifier le titre du formulaire</li>
                    <li><code>button_text="Texte du bouton"</code> - Modifier le texte du bouton</li>
                </ul>
                <p><strong>Exemple :</strong> <code>[domain_security_checker title="Vérifiez votre sécurité" button_text="Lancer le test"]</code></p>
            </div>
            
            <?php if (empty($tests)): ?>
                <div class="notice notice-info">
                    <p>Aucun test n'a encore été effectué.</p>
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Domaine</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Date</th>
                            <th>Rappel</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tests as $test): ?>
                            <tr>
                                <td><?php echo esc_html($test->id); ?></td>
                                <td><strong><?php echo esc_html($test->domain_name); ?></strong></td>
                                <td><?php echo esc_html($test->user_name); ?></td>
                                <td><a href="mailto:<?php echo esc_attr($test->user_email); ?>"><?php echo esc_html($test->user_email); ?></a></td>
                                <td><?php echo esc_html(date('d/m/Y H:i', strtotime($test->test_date))); ?></td>
                                <td>
                                    <?php if ($test->callback_requested): ?>
                                        <span class="dashicons dashicons-yes" style="color: #46b450;"></span>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-no" style="color: #dc3232;"></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=domain-security-checker&view=' . $test->id); ?>" class="button button-small">Voir détails</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if ($total_pages > 1): ?>
                    <div class="tablenav">
                        <div class="tablenav-pages">
                            <?php
                            echo paginate_links(array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'total' => $total_pages,
                                'current' => $current_page
                            ));
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Afficher les détails d'un test
     */
    private function render_test_detail($test) {
        $dns_results = maybe_unserialize($test->dns_results);
        $email_results = maybe_unserialize($test->email_security_results);
        
        ?>
        <div class="wrap">
            <h1>Détails du test #<?php echo esc_html($test->id); ?></h1>
            
            <p><a href="<?php echo admin_url('admin.php?page=domain-security-checker'); ?>" class="button">&larr; Retour à la liste</a></p>
            
            <div class="wpdsc-admin-detail">
                <h2>Informations générales</h2>
                <table class="form-table">
                    <tr>
                        <th>Domaine testé :</th>
                        <td><strong><?php echo esc_html($test->domain_name); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Nom du client :</th>
                        <td><?php echo esc_html($test->user_name); ?></td>
                    </tr>
                    <tr>
                        <th>Email du client :</th>
                        <td><a href="mailto:<?php echo esc_attr($test->user_email); ?>"><?php echo esc_html($test->user_email); ?></a></td>
                    </tr>
                    <tr>
                        <th>Date du test :</th>
                        <td><?php echo esc_html(date('d/m/Y à H:i:s', strtotime($test->test_date))); ?></td>
                    </tr>
                    <tr>
                        <th>Demande de rappel :</th>
                        <td>
                            <?php if ($test->callback_requested): ?>
                                <span style="color: #46b450; font-weight: bold;">✓ Oui</span>
                            <?php else: ?>
                                <span style="color: #999;">✗ Non</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Adresse IP :</th>
                        <td><?php echo esc_html($test->ip_address); ?></td>
                    </tr>
                </table>
                
                <h2>Résultats DNS</h2>
                <?php if ($dns_results && isset($dns_results['security_score'])): ?>
                    <div class="wpdsc-score-display">
                        <div class="wpdsc-score-bar-admin">
                            <div class="wpdsc-score-value-admin" style="width: <?php echo esc_attr($dns_results['security_score']['percentage']); ?>%; background-color: <?php echo $this->get_score_color($dns_results['security_score']['percentage']); ?>">
                                <?php echo esc_html($dns_results['security_score']['percentage']); ?>%
                            </div>
                        </div>
                        <p><strong>Évaluation :</strong> <?php echo esc_html($dns_results['security_score']['rating']); ?></p>
                    </div>
                    
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>Vérification</th>
                                <th>Statut</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dns_results['checks'] as $check_name => $check_data): ?>
                                <tr>
                                    <td><strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $check_name))); ?></strong></td>
                                    <td>
                                        <span class="wpdsc-status-badge wpdsc-status-<?php echo esc_attr(strtolower($check_data['status'])); ?>">
                                            <?php echo esc_html($check_data['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (isset($check_data['message'])): ?>
                                            <?php echo esc_html($check_data['message']); ?>
                                        <?php endif; ?>
                                        <?php if (isset($check_data['count'])): ?>
                                            <br><small>Nombre : <?php echo esc_html($check_data['count']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                
                <h2>Résultats Email</h2>
                <?php if ($email_results && isset($email_results['security_score'])): ?>
                    <div class="wpdsc-score-display">
                        <div class="wpdsc-score-bar-admin">
                            <div class="wpdsc-score-value-admin" style="width: <?php echo esc_attr($email_results['security_score']['percentage']); ?>%; background-color: <?php echo $this->get_score_color($email_results['security_score']['percentage']); ?>">
                                <?php echo esc_html($email_results['security_score']['percentage']); ?>%
                            </div>
                        </div>
                        <p><strong>Évaluation :</strong> <?php echo esc_html($email_results['security_score']['rating']); ?></p>
                    </div>
                    
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>Vérification</th>
                                <th>Statut</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($email_results['checks'] as $check_name => $check_data): ?>
                                <tr>
                                    <td><strong><?php echo esc_html(strtoupper($check_name)); ?></strong></td>
                                    <td>
                                        <span class="wpdsc-status-badge wpdsc-status-<?php echo esc_attr(strtolower($check_data['status'])); ?>">
                                            <?php echo esc_html($check_data['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (isset($check_data['message'])): ?>
                                            <?php echo esc_html($check_data['message']); ?>
                                        <?php endif; ?>
                                        <?php if (isset($check_data['record'])): ?>
                                            <br><small><code><?php echo esc_html($check_data['record']); ?></code></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Obtenir la couleur en fonction du score
     */
    private function get_score_color($percentage) {
        if ($percentage >= 90) {
            return '#46b450';
        } elseif ($percentage >= 70) {
            return '#00a0d2';
        } elseif ($percentage >= 50) {
            return '#ffb900';
        } else {
            return '#dc3232';
        }
    }
}
