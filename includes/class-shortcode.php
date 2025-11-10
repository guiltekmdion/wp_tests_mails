<?php
/**
 * Classe pour gérer le shortcode
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPDSC_Shortcode {
    
    public function __construct() {
        add_shortcode('domain_security_checker', array($this, 'render_shortcode'));
        add_action('wp_ajax_wpdsc_check_domain', array($this, 'ajax_check_domain'));
        add_action('wp_ajax_nopriv_wpdsc_check_domain', array($this, 'ajax_check_domain'));
    }
    
    /**
     * Render le shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => 'Testez la sécurité de votre domaine',
            'button_text' => 'Analyser'
        ), $atts);
        
        ob_start();
        ?>
        <div class="wpdsc-container">
            <div class="wpdsc-form-wrapper">
                <h2><?php echo esc_html($atts['title']); ?></h2>
                <form id="wpdsc-form" class="wpdsc-form">
                    <div class="wpdsc-form-group">
                        <label for="wpdsc-domain">Nom de domaine *</label>
                        <input type="text" id="wpdsc-domain" name="domain" placeholder="exemple.com" required>
                    </div>
                    
                    <div class="wpdsc-form-group">
                        <label for="wpdsc-name">Votre nom *</label>
                        <input type="text" id="wpdsc-name" name="name" required>
                    </div>
                    
                    <div class="wpdsc-form-group">
                        <label for="wpdsc-email">Votre email *</label>
                        <input type="email" id="wpdsc-email" name="email" required>
                    </div>
                    
                    <div class="wpdsc-form-group wpdsc-checkbox-group">
                        <label>
                            <input type="checkbox" id="wpdsc-callback" name="callback">
                            <span>Je souhaite être rappelé(e)</span>
                        </label>
                    </div>
                    
                    <div class="wpdsc-form-group">
                        <button type="submit" class="wpdsc-submit-btn"><?php echo esc_html($atts['button_text']); ?></button>
                    </div>
                </form>
                
                <div id="wpdsc-loading" class="wpdsc-loading" style="display: none;">
                    <div class="wpdsc-spinner"></div>
                    <p>Analyse en cours...</p>
                </div>
                
                <div id="wpdsc-results" class="wpdsc-results" style="display: none;"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Traiter la requête AJAX
     */
    public function ajax_check_domain() {
        // Vérifier le nonce
        check_ajax_referer('wpdsc_nonce', 'nonce');
        
        // Récupérer et valider les données
        $domain = isset($_POST['domain']) ? sanitize_text_field($_POST['domain']) : '';
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $callback = isset($_POST['callback']) ? (bool) $_POST['callback'] : false;
        
        // Validation
        if (empty($domain) || empty($name) || empty($email)) {
            wp_send_json_error(array(
                'message' => 'Tous les champs requis doivent être remplis.'
            ));
            return;
        }
        
        if (!is_email($email)) {
            wp_send_json_error(array(
                'message' => 'Adresse email invalide.'
            ));
            return;
        }
        
        // Effectuer les vérifications
        $dns_checker = new WPDSC_DNS_Checker();
        $email_checker = new WPDSC_Email_Checker();
        
        $dns_results = $dns_checker->check_dns($domain);
        $email_results = $email_checker->check_email_security($domain);
        
        // Vérifier les erreurs
        if (!$dns_results['success'] || !$email_results['success']) {
            wp_send_json_error(array(
                'message' => 'Erreur lors de l\'analyse du domaine. Veuillez vérifier le nom de domaine.'
            ));
            return;
        }
        
        // Enregistrer dans la base de données
        $database = new WPDSC_Database();
        $test_id = $database->save_test(array(
            'domain_name' => $domain,
            'user_name' => $name,
            'user_email' => $email,
            'dns_results' => $dns_results,
            'email_security_results' => $email_results,
            'callback_requested' => $callback
        ));
        
        if (!$test_id) {
            wp_send_json_error(array(
                'message' => 'Erreur lors de l\'enregistrement des résultats.'
            ));
            return;
        }
        
        // Envoyer les emails de notification
        $email_notifier = new WPDSC_Email_Notifications();
        $email_notifier->send_results_to_client($email, $name, $domain, $dns_results, $email_results);
        $email_notifier->send_notification_to_admin($name, $email, $domain, $callback);
        
        // Préparer les résultats pour l'affichage
        $html_results = $this->format_results_html($domain, $dns_results, $email_results);
        
        wp_send_json_success(array(
            'message' => 'Analyse terminée avec succès !',
            'results' => $html_results
        ));
    }
    
    /**
     * Formater les résultats en HTML
     */
    private function format_results_html($domain, $dns_results, $email_results) {
        ob_start();
        ?>
        <div class="wpdsc-results-content">
            <h3>Résultats de l'analyse pour : <?php echo esc_html($domain); ?></h3>
            
            <!-- Score DNS -->
            <div class="wpdsc-score-section">
                <h4>Sécurité DNS</h4>
                <div class="wpdsc-score-bar">
                    <div class="wpdsc-score-value" style="width: <?php echo esc_attr($dns_results['security_score']['percentage']); ?>%;">
                        <?php echo esc_html($dns_results['security_score']['percentage']); ?>%
                    </div>
                </div>
                <p class="wpdsc-rating">Évaluation : <strong><?php echo esc_html($dns_results['security_score']['rating']); ?></strong></p>
                
                <div class="wpdsc-checks-list">
                    <?php foreach ($dns_results['checks'] as $check_name => $check_data): ?>
                        <div class="wpdsc-check-item wpdsc-status-<?php echo esc_attr(strtolower($check_data['status'])); ?>">
                            <span class="wpdsc-check-icon"></span>
                            <strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $check_name))); ?>:</strong>
                            <?php echo esc_html($check_data['status']); ?>
                            <?php if (isset($check_data['message'])): ?>
                                - <?php echo esc_html($check_data['message']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Score Email -->
            <div class="wpdsc-score-section">
                <h4>Sécurité Email</h4>
                <div class="wpdsc-score-bar">
                    <div class="wpdsc-score-value" style="width: <?php echo esc_attr($email_results['security_score']['percentage']); ?>%;">
                        <?php echo esc_html($email_results['security_score']['percentage']); ?>%
                    </div>
                </div>
                <p class="wpdsc-rating">Évaluation : <strong><?php echo esc_html($email_results['security_score']['rating']); ?></strong></p>
                
                <div class="wpdsc-checks-list">
                    <?php foreach ($email_results['checks'] as $check_name => $check_data): ?>
                        <div class="wpdsc-check-item wpdsc-status-<?php echo esc_attr(strtolower($check_data['status'])); ?>">
                            <span class="wpdsc-check-icon"></span>
                            <strong><?php echo esc_html(strtoupper($check_name)); ?>:</strong>
                            <?php echo esc_html($check_data['status']); ?>
                            <?php if (isset($check_data['message'])): ?>
                                - <?php echo esc_html($check_data['message']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="wpdsc-notification">
                <p>✉️ Un email avec les résultats détaillés vous a été envoyé.</p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
