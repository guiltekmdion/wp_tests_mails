<?php
/**
 * Classe pour gérer les notifications par email
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPDSC_Email_Notifications {
    
    /**
     * Envoyer les résultats au client
     */
    public function send_results_to_client($email, $name, $domain, $dns_results, $email_results) {
        $subject = 'Résultats de l\'analyse de sécurité pour ' . $domain;
        
        $message = $this->get_client_email_template($name, $domain, $dns_results, $email_results);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
        
        return wp_mail($email, $subject, $message, $headers);
    }
    
    /**
     * Envoyer une notification à l'administrateur
     */
    public function send_notification_to_admin($name, $email, $domain, $callback_requested) {
        $admin_email = get_option('admin_email');
        $subject = 'Nouvelle analyse de domaine effectuée : ' . $domain;
        
        $message = $this->get_admin_email_template($name, $email, $domain, $callback_requested);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: ' . $email
        );
        
        return wp_mail($admin_email, $subject, $message, $headers);
    }
    
    /**
     * Template d'email pour le client
     */
    private function get_client_email_template($name, $domain, $dns_results, $email_results) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #0073aa; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 20px; }
                .score-section { background-color: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .score-bar { background-color: #e0e0e0; height: 30px; border-radius: 5px; overflow: hidden; margin: 10px 0; }
                .score-value { background-color: #4caf50; height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }
                .check-item { padding: 8px; margin: 5px 0; border-left: 3px solid #ddd; }
                .status-ok { border-left-color: #4caf50; }
                .status-avertissement { border-left-color: #ff9800; }
                .status-erreur { border-left-color: #f44336; }
                .status-info { border-left-color: #2196f3; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Résultats de l'analyse de sécurité</h1>
                </div>
                
                <div class="content">
                    <p>Bonjour <?php echo esc_html($name); ?>,</p>
                    <p>Voici les résultats de l'analyse de sécurité pour le domaine <strong><?php echo esc_html($domain); ?></strong>.</p>
                    
                    <div class="score-section">
                        <h2>Sécurité DNS</h2>
                        <div class="score-bar">
                            <div class="score-value" style="width: <?php echo esc_attr($dns_results['security_score']['percentage']); ?>%;">
                                <?php echo esc_html($dns_results['security_score']['percentage']); ?>%
                            </div>
                        </div>
                        <p><strong>Évaluation :</strong> <?php echo esc_html($dns_results['security_score']['rating']); ?></p>
                        
                        <h3>Détails :</h3>
                        <?php foreach ($dns_results['checks'] as $check_name => $check_data): ?>
                            <div class="check-item status-<?php echo esc_attr(strtolower($check_data['status'])); ?>">
                                <strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $check_name))); ?>:</strong>
                                <?php echo esc_html($check_data['status']); ?>
                                <?php if (isset($check_data['message'])): ?>
                                    - <?php echo esc_html($check_data['message']); ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="score-section">
                        <h2>Sécurité Email</h2>
                        <div class="score-bar">
                            <div class="score-value" style="width: <?php echo esc_attr($email_results['security_score']['percentage']); ?>%;">
                                <?php echo esc_html($email_results['security_score']['percentage']); ?>%
                            </div>
                        </div>
                        <p><strong>Évaluation :</strong> <?php echo esc_html($email_results['security_score']['rating']); ?></p>
                        
                        <h3>Détails :</h3>
                        <?php foreach ($email_results['checks'] as $check_name => $check_data): ?>
                            <div class="check-item status-<?php echo esc_attr(strtolower($check_data['status'])); ?>">
                                <strong><?php echo esc_html(strtoupper($check_name)); ?>:</strong>
                                <?php echo esc_html($check_data['status']); ?>
                                <?php if (isset($check_data['message'])): ?>
                                    - <?php echo esc_html($check_data['message']); ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="score-section">
                        <h3>Recommandations</h3>
                        <ul>
                            <?php if ($dns_results['security_score']['percentage'] < 70): ?>
                                <li>Améliorez votre configuration DNS en activant DNSSEC pour une meilleure sécurité.</li>
                            <?php endif; ?>
                            <?php if (!$email_results['checks']['spf']['found']): ?>
                                <li>Configurez un enregistrement SPF pour protéger votre domaine contre le spam.</li>
                            <?php endif; ?>
                            <?php if (!$email_results['checks']['dmarc']['found']): ?>
                                <li>Ajoutez un enregistrement DMARC pour améliorer la délivrabilité de vos emails.</li>
                            <?php endif; ?>
                            <?php if (!$email_results['checks']['dkim']['found']): ?>
                                <li>Configurez DKIM pour authentifier vos emails sortants.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <p>Si vous avez des questions ou souhaitez améliorer la sécurité de votre domaine, n'hésitez pas à nous contacter.</p>
                    <p>Cordialement,<br><?php echo esc_html(get_bloginfo('name')); ?></p>
                </div>
                
                <div class="footer">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo esc_html(get_bloginfo('name')); ?>. Tous droits réservés.</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Template d'email pour l'administrateur
     */
    private function get_admin_email_template($name, $email, $domain, $callback_requested) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #0073aa; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 20px; }
                .info-box { background-color: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .info-row { padding: 8px 0; border-bottom: 1px solid #eee; }
                .info-row:last-child { border-bottom: none; }
                .label { font-weight: bold; color: #666; }
                .callback-notice { background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Nouvelle analyse de domaine</h1>
                </div>
                
                <div class="content">
                    <p>Une nouvelle analyse de domaine a été effectuée sur votre site.</p>
                    
                    <div class="info-box">
                        <h2>Informations du client</h2>
                        <div class="info-row">
                            <span class="label">Nom :</span> <?php echo esc_html($name); ?>
                        </div>
                        <div class="info-row">
                            <span class="label">Email :</span> <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                        </div>
                        <div class="info-row">
                            <span class="label">Domaine testé :</span> <?php echo esc_html($domain); ?>
                        </div>
                        <div class="info-row">
                            <span class="label">Date :</span> <?php echo date('d/m/Y à H:i'); ?>
                        </div>
                    </div>
                    
                    <?php if ($callback_requested): ?>
                        <div class="callback-notice">
                            <strong>⚠️ Demande de rappel :</strong> Le client souhaite être rappelé.
                        </div>
                    <?php endif; ?>
                    
                    <p>Vous pouvez consulter les résultats détaillés dans le back-office de WordPress.</p>
                    <p><a href="<?php echo admin_url('admin.php?page=domain-security-checker'); ?>" style="display: inline-block; padding: 10px 20px; background-color: #0073aa; color: white; text-decoration: none; border-radius: 5px;">Voir dans le back-office</a></p>
                </div>
                
                <div class="footer">
                    <p>Cette notification a été générée automatiquement par le plugin Domain Security Checker.</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
