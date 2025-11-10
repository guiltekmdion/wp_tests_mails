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
        
        // Vérifier les enregistrements AAAA (IPv6)
        $results['checks']['aaaa_records'] = $this->check_aaaa_records($domain);
        
        // Vérifier les enregistrements NS
        $results['checks']['ns_records'] = $this->check_ns_records($domain);
        
        // Vérifier la cohérence des NS
        $results['checks']['ns_coherence'] = $this->check_ns_coherence($domain, $results['checks']['ns_records']);
        
        // Vérifier l'enregistrement SOA
        $results['checks']['soa_record'] = $this->check_soa_record($domain);
        
        // Vérifier l'absence de CNAME sur la racine
        $results['checks']['cname_check'] = $this->check_cname_root($domain);
        
        // Vérifier les enregistrements TXT
        $results['checks']['txt_records'] = $this->check_txt_records($domain);
        
        // Vérifier le reverse DNS (PTR)
        $results['checks']['reverse_dns'] = $this->check_reverse_dns($domain, $results['checks']['a_records']);
        
        // Vérifier les enregistrements MX
        $results['checks']['mx_records'] = $this->check_mx_records($domain);
        
        // Vérifier DNSSEC
        $results['checks']['dnssec'] = $this->check_dnssec($domain);
        
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
     * Vérifier les enregistrements AAAA (IPv6)
     */
    private function check_aaaa_records($domain) {
        $records = @dns_get_record($domain, DNS_AAAA);
        
        return array(
            'found' => !empty($records),
            'count' => count($records),
            'records' => $records ? array_map(function($r) {
                return $r['ipv6'];
            }, $records) : array(),
            'status' => !empty($records) ? 'OK' : 'INFO'
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
        
        $ns_servers = $records ? array_map(function($r) {
            return $r['target'];
        }, $records) : array();
        
        $has_multiple = count($ns_servers) >= 2;
        $status = 'ERREUR';
        
        if ($has_multiple) {
            $status = 'OK';
        } elseif (count($ns_servers) === 1) {
            $status = 'AVERTISSEMENT';
        }
        
        return array(
            'found' => !empty($records),
            'count' => count($records),
            'records' => $ns_servers,
            'has_multiple' => $has_multiple,
            'status' => $status,
            'message' => $has_multiple ? 'Au moins 2 serveurs NS distincts' : 'Moins de 2 serveurs NS'
        );
    }
    
    /**
     * Vérifier la cohérence des serveurs NS
     */
    private function check_ns_coherence($domain, $ns_check) {
        if (!$ns_check['found'] || empty($ns_check['records'])) {
            return array(
                'coherent' => false,
                'status' => 'ERREUR',
                'message' => 'Aucun serveur NS à vérifier'
            );
        }
        
        $soa_responses = array();
        $coherent = true;
        
        // Vérifier chaque NS
        foreach ($ns_check['records'] as $ns) {
            $soa = @dns_get_record($domain, DNS_SOA, $nameservers);
            if ($soa && isset($soa[0]['serial'])) {
                $soa_responses[] = $soa[0]['serial'];
            }
        }
        
        // Vérifier la cohérence
        if (count($soa_responses) > 1) {
            $first_serial = $soa_responses[0];
            foreach ($soa_responses as $serial) {
                if ($serial !== $first_serial) {
                    $coherent = false;
                    break;
                }
            }
        }
        
        return array(
            'coherent' => $coherent,
            'checked_ns' => count($soa_responses),
            'status' => $coherent ? 'OK' : 'AVERTISSEMENT',
            'message' => $coherent ? 'Tous les NS répondent de manière cohérente' : 'Incohérences détectées entre les NS'
        );
    }
    
    /**
     * Vérifier l'enregistrement SOA
     */
    private function check_soa_record($domain) {
        $records = @dns_get_record($domain, DNS_SOA);
        
        if (empty($records)) {
            return array(
                'found' => false,
                'status' => 'ERREUR',
                'message' => 'Enregistrement SOA manquant'
            );
        }
        
        $soa = $records[0];
        $valid = isset($soa['serial']) && isset($soa['rname']) && $soa['serial'] > 0;
        
        return array(
            'found' => true,
            'valid' => $valid,
            'serial' => isset($soa['serial']) ? $soa['serial'] : null,
            'primary_ns' => isset($soa['mname']) ? $soa['mname'] : null,
            'admin_email' => isset($soa['rname']) ? $soa['rname'] : null,
            'status' => $valid ? 'OK' : 'AVERTISSEMENT',
            'message' => $valid ? 'Enregistrement SOA valide' : 'Enregistrement SOA présent mais incomplet'
        );
    }
    
    /**
     * Vérifier l'absence de CNAME sur la racine du domaine
     */
    private function check_cname_root($domain) {
        $records = @dns_get_record($domain, DNS_CNAME);
        
        $has_cname = !empty($records);
        
        return array(
            'has_cname' => $has_cname,
            'status' => !$has_cname ? 'OK' : 'AVERTISSEMENT',
            'message' => !$has_cname ? 'Pas de CNAME sur la racine (bonne pratique)' : 'CNAME détecté sur la racine du domaine (non recommandé)'
        );
    }
    
    /**
     * Vérifier le reverse DNS (PTR)
     */
    private function check_reverse_dns($domain, $a_check) {
        if (!$a_check['found'] || empty($a_check['records'])) {
            return array(
                'found' => false,
                'status' => 'INFO',
                'message' => 'Pas d\'IP A pour vérifier le PTR'
            );
        }
        
        // Prendre la première IP
        $ip = $a_check['records'][0];
        
        // Vérifier le PTR
        $hostname = @gethostbyaddr($ip);
        $has_ptr = ($hostname !== $ip && $hostname !== false);
        
        return array(
            'found' => $has_ptr,
            'ip' => $ip,
            'hostname' => $has_ptr ? $hostname : null,
            'status' => $has_ptr ? 'OK' : 'AVERTISSEMENT',
            'message' => $has_ptr ? 'Reverse DNS configuré pour ' . $ip : 'Pas de reverse DNS pour ' . $ip
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
        $max_score = 130;
        
        // A / AAAA records (20 points)
        if (!empty($checks['a_records']['found'])) {
            $score += 20;
        }
        
        // NS records - présence d'au moins 2 serveurs (15 points)
        if (!empty($checks['ns_records']['has_multiple'])) {
            $score += 15;
        } elseif (!empty($checks['ns_records']['found'])) {
            $score += 7; // Points partiels si seulement 1 NS
        }
        
        // NS cohérence (10 points)
        if (!empty($checks['ns_coherence']['coherent'])) {
            $score += 10;
        }
        
        // SOA record (10 points)
        if (!empty($checks['soa_record']['valid'])) {
            $score += 10;
        } elseif (!empty($checks['soa_record']['found'])) {
            $score += 5; // Points partiels si présent mais incomplet
        }
        
        // CNAME check - pas de CNAME sur racine (5 points)
        if (!empty($checks['cname_check']) && !$checks['cname_check']['has_cname']) {
            $score += 5;
        }
        
        // TXT records (10 points)
        if (!empty($checks['txt_records']['found'])) {
            $score += 10;
        }
        
        // Reverse DNS PTR (10 points)
        if (!empty($checks['reverse_dns']['found'])) {
            $score += 10;
        }
        
        // MX records (20 points) - gardé pour compatibilité
        if (!empty($checks['mx_records']['found'])) {
            $score += 20;
        }
        
        // DNSSEC (30 points) - gardé pour compatibilité
        if (!empty($checks['dnssec']['enabled'])) {
            $score += 30;
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
