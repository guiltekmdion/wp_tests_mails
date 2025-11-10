<?php
/**
 * Classe pour vérifier la sécurité DNS
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPDSC_DNS_Checker {
    
    /**
     * Vérifier le DNS d'un domaine
     */
    public function check_dns($domain) {
        $domain = $this->sanitize_domain($domain);
        
        if (!$domain) {
            return array(
                'success' => false,
                'error' => 'Nom de domaine invalide'
            );
        }
        
        $results = array(
            'domain' => $domain,
            'checks' => array()
        );
        
        // Vérifier les enregistrements A
        $results['checks']['a_records'] = $this->check_a_records($domain);
        
        // Vérifier les enregistrements MX
        $results['checks']['mx_records'] = $this->check_mx_records($domain);
        
        // Vérifier les enregistrements NS
        $results['checks']['ns_records'] = $this->check_ns_records($domain);
        
        // Vérifier DNSSEC
        $results['checks']['dnssec'] = $this->check_dnssec($domain);
        
        // Vérifier les enregistrements TXT
        $results['checks']['txt_records'] = $this->check_txt_records($domain);
        
        // Calculer le score de sécurité
        $results['security_score'] = $this->calculate_security_score($results['checks']);
        
        $results['success'] = true;
        
        return $results;
    }
    
    /**
     * Vérifier les enregistrements A
     */
    private function check_a_records($domain) {
        $records = @dns_get_record($domain, DNS_A);
        
        return array(
            'found' => !empty($records),
            'count' => count($records),
            'records' => $records ? array_map(function($r) {
                return $r['ip'];
            }, $records) : array(),
            'status' => !empty($records) ? 'OK' : 'ERREUR'
        );
    }
    
    /**
     * Vérifier les enregistrements MX
     */
    private function check_mx_records($domain) {
        $records = @dns_get_record($domain, DNS_MX);
        
        return array(
            'found' => !empty($records),
            'count' => count($records),
            'records' => $records ? array_map(function($r) {
                return array(
                    'target' => $r['target'],
                    'priority' => $r['pri']
                );
            }, $records) : array(),
            'status' => !empty($records) ? 'OK' : 'AVERTISSEMENT'
        );
    }
    
    /**
     * Vérifier les enregistrements NS
     */
    private function check_ns_records($domain) {
        $records = @dns_get_record($domain, DNS_NS);
        
        return array(
            'found' => !empty($records),
            'count' => count($records),
            'records' => $records ? array_map(function($r) {
                return $r['target'];
            }, $records) : array(),
            'status' => !empty($records) ? 'OK' : 'ERREUR'
        );
    }
    
    /**
     * Vérifier DNSSEC (vérification basique)
     */
    private function check_dnssec($domain) {
        $records = @dns_get_record($domain, DNS_ANY);
        
        $has_dnssec = false;
        if ($records) {
            foreach ($records as $record) {
                if (isset($record['type']) && in_array($record['type'], array('RRSIG', 'DNSKEY', 'DS'))) {
                    $has_dnssec = true;
                    break;
                }
            }
        }
        
        return array(
            'enabled' => $has_dnssec,
            'status' => $has_dnssec ? 'OK' : 'AVERTISSEMENT',
            'message' => $has_dnssec ? 'DNSSEC est activé' : 'DNSSEC n\'est pas détecté'
        );
    }
    
    /**
     * Vérifier les enregistrements TXT
     */
    private function check_txt_records($domain) {
        $records = @dns_get_record($domain, DNS_TXT);
        
        return array(
            'found' => !empty($records),
            'count' => count($records),
            'records' => $records ? array_map(function($r) {
                return $r['txt'];
            }, $records) : array(),
            'status' => 'INFO'
        );
    }
    
    /**
     * Calculer le score de sécurité DNS
     */
    private function calculate_security_score($checks) {
        $score = 0;
        $max_score = 100;
        
        // A records (20 points)
        if (!empty($checks['a_records']['found'])) {
            $score += 20;
        }
        
        // MX records (20 points)
        if (!empty($checks['mx_records']['found'])) {
            $score += 20;
        }
        
        // NS records (20 points)
        if (!empty($checks['ns_records']['found'])) {
            $score += 20;
        }
        
        // DNSSEC (30 points)
        if (!empty($checks['dnssec']['enabled'])) {
            $score += 30;
        }
        
        // TXT records (10 points)
        if (!empty($checks['txt_records']['found'])) {
            $score += 10;
        }
        
        return array(
            'score' => $score,
            'max_score' => $max_score,
            'percentage' => round(($score / $max_score) * 100, 2),
            'rating' => $this->get_rating($score, $max_score)
        );
    }
    
    /**
     * Obtenir l'évaluation textuelle
     */
    private function get_rating($score, $max_score) {
        $percentage = ($score / $max_score) * 100;
        
        if ($percentage >= 90) {
            return 'Excellent';
        } elseif ($percentage >= 75) {
            return 'Bon';
        } elseif ($percentage >= 50) {
            return 'Moyen';
        } else {
            return 'Faible';
        }
    }
    
    /**
     * Nettoyer et valider le nom de domaine
     */
    private function sanitize_domain($domain) {
        // Supprimer http://, https://, www.
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = preg_replace('#^www\.#', '', $domain);
        
        // Supprimer les chemins et paramètres
        $domain = strtok($domain, '/');
        $domain = strtok($domain, '?');
        
        // Valider le format du domaine
        if (!preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/', $domain)) {
            return false;
        }
        
        return strtolower($domain);
    }
}
