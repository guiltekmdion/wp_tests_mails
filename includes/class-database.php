<?php
/**
 * Classe pour gérer la base de données
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPDSC_Database {
    
    /**
     * Créer les tables nécessaires
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'domain_security_tests';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            domain_name varchar(255) NOT NULL,
            user_name varchar(255) NOT NULL,
            user_email varchar(255) NOT NULL,
            dns_results longtext,
            email_security_results longtext,
            test_date datetime DEFAULT CURRENT_TIMESTAMP,
            callback_requested tinyint(1) DEFAULT 0,
            ip_address varchar(45),
            PRIMARY KEY (id),
            KEY domain_name (domain_name),
            KEY user_email (user_email),
            KEY test_date (test_date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Enregistrer un test
     */
    public function save_test($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'domain_security_tests';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'domain_name' => sanitize_text_field($data['domain_name']),
                'user_name' => sanitize_text_field($data['user_name']),
                'user_email' => sanitize_email($data['user_email']),
                'dns_results' => maybe_serialize($data['dns_results']),
                'email_security_results' => maybe_serialize($data['email_security_results']),
                'callback_requested' => isset($data['callback_requested']) ? 1 : 0,
                'ip_address' => $this->get_client_ip()
            ),
            array('%s', '%s', '%s', '%s', '%s', '%d', '%s')
        );
        
        return $result !== false ? $wpdb->insert_id : false;
    }
    
    /**
     * Récupérer tous les tests
     */
    public function get_all_tests($limit = 50, $offset = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'domain_security_tests';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY test_date DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            )
        );
        
        return $results;
    }
    
    /**
     * Récupérer le nombre total de tests
     */
    public function get_total_tests() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'domain_security_tests';
        
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }
    
    /**
     * Récupérer un test par ID
     */
    public function get_test_by_id($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'domain_security_tests';
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id)
        );
    }
    
    /**
     * Obtenir l'adresse IP du client
     */
    private function get_client_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }
}
