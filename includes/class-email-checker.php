<?php
/**
 * Classe pour vérifier la sécurité des emails (SPF, DMARC, DKIM)
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPDSC_Email_Checker {
    
    /**
     * Vérifier la sécurité email d'un domaine
     */
    public function check_email_security($domain) {
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
        
        // Vérifier SPF
        $results['checks']['spf'] = $this->check_spf($domain);
        
        // Vérifier DMARC
        $results['checks']['dmarc'] = $this->check_dmarc($domain);
        
        // Vérifier DKIM (vérification générique)
        $results['checks']['dkim'] = $this->check_dkim($domain);
        
        // Vérifier MTA-STS
        $results['checks']['mta_sts'] = $this->check_mta_sts($domain);
        
        // Vérifier TLS-RPT
        $results['checks']['tls_rpt'] = $this->check_tls_rpt($domain);
        
        // Calculer le score de sécurité
        $results['security_score'] = $this->calculate_security_score($results['checks']);
        
        $results['success'] = true;
        
        return $results;
    }
    
    /**
     * Vérifier SPF
     */
    private function check_spf($domain) {
        $txt_records = @dns_get_record($domain, DNS_TXT);
        
        $spf_record = null;
        if ($txt_records) {
            foreach ($txt_records as $record) {
                if (isset($record['txt']) && strpos($record['txt'], 'v=spf1') === 0) {
                    $spf_record = $record['txt'];
                    break;
                }
            }
        }
        
        $result = array(
            'found' => !empty($spf_record),
            'record' => $spf_record,
            'status' => !empty($spf_record) ? 'OK' : 'ERREUR',
            'message' => !empty($spf_record) ? 'SPF est configuré' : 'SPF n\'est pas configuré'
        );
        
        // Analyser le SPF pour détecter des problèmes
        if ($spf_record) {
            $result['analysis'] = $this->analyze_spf($spf_record);
        }
        
        return $result;
    }
    
    /**
     * Analyser un enregistrement SPF
     */
    private function analyze_spf($spf_record) {
        $analysis = array(
            'issues' => array(),
            'warnings' => array()
        );
        
        // Vérifier si l'enregistrement se termine par -all ou ~all
        if (strpos($spf_record, '-all') !== false) {
            $analysis['warnings'][] = 'Politique stricte (-all) : les emails non autorisés seront rejetés';
        } elseif (strpos($spf_record, '~all') !== false) {
            $analysis['warnings'][] = 'Politique souple (~all) : les emails non autorisés seront marqués comme suspects';
        } elseif (strpos($spf_record, '+all') !== false) {
            $analysis['issues'][] = 'DANGER : +all autorise tous les serveurs à envoyer des emails';
        }
        
        // Compter le nombre de lookups DNS
        $lookup_count = substr_count($spf_record, 'include:') + 
                       substr_count($spf_record, 'a:') + 
                       substr_count($spf_record, 'mx:') + 
                       substr_count($spf_record, 'exists:');
        
        if ($lookup_count > 10) {
            $analysis['issues'][] = 'Trop de lookups DNS (' . $lookup_count . ') - limite recommandée : 10';
        }
        
        return $analysis;
    }
    
    /**
     * Vérifier DMARC
     */
    private function check_dmarc($domain) {
        $dmarc_domain = '_dmarc.' . $domain;
        $txt_records = @dns_get_record($dmarc_domain, DNS_TXT);
        
        $dmarc_record = null;
        if ($txt_records) {
            foreach ($txt_records as $record) {
                if (isset($record['txt']) && strpos($record['txt'], 'v=DMARC1') === 0) {
                    $dmarc_record = $record['txt'];
                    break;
                }
            }
        }
        
        $result = array(
            'found' => !empty($dmarc_record),
            'record' => $dmarc_record,
            'status' => !empty($dmarc_record) ? 'OK' : 'AVERTISSEMENT',
            'message' => !empty($dmarc_record) ? 'DMARC est configuré' : 'DMARC n\'est pas configuré'
        );
        
        // Analyser le DMARC
        if ($dmarc_record) {
            $result['analysis'] = $this->analyze_dmarc($dmarc_record);
        }
        
        return $result;
    }
    
    /**
     * Analyser un enregistrement DMARC
     */
    private function analyze_dmarc($dmarc_record) {
        $analysis = array(
            'policy' => null,
            'warnings' => array()
        );
        
        // Extraire la politique
        if (preg_match('/p=(none|quarantine|reject)/i', $dmarc_record, $matches)) {
            $analysis['policy'] = $matches[1];
            
            if ($matches[1] === 'none') {
                $analysis['warnings'][] = 'Politique "none" : aucune action n\'est prise sur les emails échoués';
            } elseif ($matches[1] === 'quarantine') {
                $analysis['warnings'][] = 'Politique "quarantine" : les emails échoués sont mis en quarantaine';
            } else {
                $analysis['warnings'][] = 'Politique "reject" : les emails échoués sont rejetés (recommandé)';
            }
        }
        
        // Vérifier la présence de rapports
        if (strpos($dmarc_record, 'rua=') !== false) {
            $analysis['warnings'][] = 'Rapports agrégés configurés';
        } else {
            $analysis['warnings'][] = 'Aucun rapport agrégé configuré';
        }
        
        return $analysis;
    }
    
    /**
     * Vérifier DKIM (vérification générique)
     */
    private function check_dkim($domain) {
        // DKIM nécessite de connaître le sélecteur, on vérifie juste les sélecteurs courants
        $common_selectors = array('default', 'mail', 'dkim', 'google', 'k1', 's1', 's2');
        $dkim_found = false;
        $found_selectors = array();
        
        foreach ($common_selectors as $selector) {
            $dkim_domain = $selector . '._domainkey.' . $domain;
            $txt_records = @dns_get_record($dkim_domain, DNS_TXT);
            
            if ($txt_records) {
                foreach ($txt_records as $record) {
                    if (isset($record['txt']) && (strpos($record['txt'], 'v=DKIM1') !== false || strpos($record['txt'], 'k=rsa') !== false)) {
                        $dkim_found = true;
                        $found_selectors[] = $selector;
                        break;
                    }
                }
            }
        }
        
        return array(
            'found' => $dkim_found,
            'selectors' => $found_selectors,
            'status' => $dkim_found ? 'OK' : 'AVERTISSEMENT',
            'message' => $dkim_found ? 
                'DKIM trouvé (sélecteurs: ' . implode(', ', $found_selectors) . ')' : 
                'DKIM non détecté (vérification limitée)'
        );
    }
    
    /**
     * Vérifier MTA-STS
     */
    private function check_mta_sts($domain) {
        $mta_sts_domain = '_mta-sts.' . $domain;
        $txt_records = @dns_get_record($mta_sts_domain, DNS_TXT);
        
        $mta_sts_found = false;
        if ($txt_records) {
            foreach ($txt_records as $record) {
                if (isset($record['txt']) && strpos($record['txt'], 'v=STSv1') !== false) {
                    $mta_sts_found = true;
                    break;
                }
            }
        }
        
        return array(
            'found' => $mta_sts_found,
            'status' => $mta_sts_found ? 'OK' : 'INFO',
            'message' => $mta_sts_found ? 'MTA-STS est activé' : 'MTA-STS n\'est pas configuré'
        );
    }
    
    /**
     * Vérifier TLS-RPT
     */
    private function check_tls_rpt($domain) {
        $tls_rpt_domain = '_smtp._tls.' . $domain;
        $txt_records = @dns_get_record($tls_rpt_domain, DNS_TXT);
        
        $tls_rpt_found = false;
        if ($txt_records) {
            foreach ($txt_records as $record) {
                if (isset($record['txt']) && strpos($record['txt'], 'v=TLSRPTv1') !== false) {
                    $tls_rpt_found = true;
                    break;
                }
            }
        }
        
        return array(
            'found' => $tls_rpt_found,
            'status' => $tls_rpt_found ? 'OK' : 'INFO',
            'message' => $tls_rpt_found ? 'TLS-RPT est activé' : 'TLS-RPT n\'est pas configuré'
        );
    }
    
    /**
     * Calculer le score de sécurité email
     */
    private function calculate_security_score($checks) {
        $score = 0;
        $max_score = 100;
        
        // SPF (35 points)
        if (!empty($checks['spf']['found'])) {
            $score += 35;
        }
        
        // DMARC (35 points)
        if (!empty($checks['dmarc']['found'])) {
            $score += 35;
        }
        
        // DKIM (20 points)
        if (!empty($checks['dkim']['found'])) {
            $score += 20;
        }
        
        // MTA-STS (5 points)
        if (!empty($checks['mta_sts']['found'])) {
            $score += 5;
        }
        
        // TLS-RPT (5 points)
        if (!empty($checks['tls_rpt']['found'])) {
            $score += 5;
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
        } elseif ($percentage >= 70) {
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
