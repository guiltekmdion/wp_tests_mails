# Architecture du Plugin WP Domain Security Checker

## Vue d'ensemble du flux de données

```
┌─────────────────────────────────────────────────────────────────────┐
│                         UTILISATEUR FRONTEND                         │
└────────────────────────────────┬────────────────────────────────────┘
                                 │
                                 │ Visite la page avec shortcode
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    SHORTCODE [domain_security_checker]               │
│                                                                       │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Formulaire HTML (class-shortcode.php)                       │   │
│  │  - Nom de domaine                                            │   │
│  │  - Nom du client                                             │   │
│  │  - Email du client                                           │   │
│  │  - Checkbox "Je souhaite être rappelé"                      │   │
│  └─────────────────────────────────────────────────────────────┘   │
└────────────────────────────────┬────────────────────────────────────┘
                                 │
                                 │ Soumission du formulaire (AJAX)
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    TRAITEMENT AJAX (script.js)                       │
│  - Validation côté client                                           │
│  - Envoi via jQuery.ajax()                                          │
│  - Affichage du spinner de chargement                               │
└────────────────────────────────┬────────────────────────────────────┘
                                 │
                                 │ POST vers admin-ajax.php
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│              HANDLER AJAX (class-shortcode.php)                      │
│  - Vérification du nonce (sécurité)                                │
│  - Validation et sanitization des données                           │
└────────────────┬───────────────────────────────────┬────────────────┘
                 │                                   │
    ┌────────────▼────────────┐     ┌───────────────▼────────────┐
    │  DNS CHECKER            │     │  EMAIL CHECKER              │
    │  (class-dns-checker.php)│     │  (class-email-checker.php) │
    │                         │     │                             │
    │  Vérifie:               │     │  Vérifie:                   │
    │  ✓ A records            │     │  ✓ SPF                      │
    │  ✓ MX records           │     │  ✓ DMARC                    │
    │  ✓ NS records           │     │  ✓ DKIM                     │
    │  ✓ DNSSEC               │     │  ✓ MTA-STS                  │
    │  ✓ TXT records          │     │  ✓ TLS-RPT                  │
    │                         │     │                             │
    │  Score: 0-100           │     │  Score: 0-100               │
    └────────────┬────────────┘     └───────────────┬────────────┘
                 │                                   │
                 └───────────────┬───────────────────┘
                                 │
                                 │ Résultats compilés
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    BASE DE DONNÉES (class-database.php)              │
│                                                                       │
│  Table: wp_domain_security_tests                                    │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  - id                                                         │   │
│  │  - domain_name                                                │   │
│  │  - user_name                                                  │   │
│  │  - user_email                                                 │   │
│  │  - dns_results (serialized)                                   │   │
│  │  - email_security_results (serialized)                        │   │
│  │  - callback_requested                                         │   │
│  │  - test_date                                                  │   │
│  │  - ip_address                                                 │   │
│  └─────────────────────────────────────────────────────────────┘   │
└────────────────────────────────┬────────────────────────────────────┘
                                 │
                                 │ Données enregistrées
                                 │
                ┌────────────────┴────────────────┐
                │                                 │
                ▼                                 ▼
┌───────────────────────────────┐   ┌────────────────────────────────┐
│  EMAIL CLIENT                 │   │  EMAIL ADMIN                    │
│  (class-email-notifications)  │   │  (class-email-notifications)   │
│                               │   │                                 │
│  Envoyé à: client@email.com   │   │  Envoyé à: admin@site.com      │
│                               │   │                                 │
│  Contient:                    │   │  Contient:                      │
│  • Résultats DNS              │   │  • Info client                  │
│  • Résultats Email            │   │  • Domaine testé                │
│  • Scores et évaluations      │   │  • Demande de rappel            │
│  • Recommandations            │   │  • Date et heure                │
│  • Template HTML              │   │  • Lien vers le backoffice      │
└───────────────┬───────────────┘   └────────────────┬───────────────┘
                │                                     │
                └─────────────────┬───────────────────┘
                                  │
                                  │ Emails envoyés
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│                  RÉPONSE AU FRONTEND (JSON)                          │
│                                                                       │
│  {                                                                    │
│    "success": true,                                                  │
│    "data": {                                                         │
│      "message": "Analyse terminée avec succès!",                    │
│      "results": "<html>...</html>"                                  │
│    }                                                                 │
│  }                                                                   │
└────────────────────────────────┬────────────────────────────────────┘
                                 │
                                 │ Affichage des résultats
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     AFFICHAGE DES RÉSULTATS                          │
│                                                                       │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  Résultats pour: exemple.com                                  │  │
│  │                                                                │  │
│  │  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │  │
│  │  SÉCURITÉ DNS                                                 │  │
│  │  Score: 70/100                                                 │  │
│  │  ████████████████████▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒ 70%               │  │
│  │  Évaluation: Bon                                              │  │
│  │                                                                │  │
│  │  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │  │
│  │  SÉCURITÉ EMAIL                                               │  │
│  │  Score: 90/100                                                 │  │
│  │  ████████████████████████████████████▒▒▒▒ 90%                │  │
│  │  Évaluation: Excellent                                        │  │
│  │                                                                │  │
│  │  ✉️  Un email avec les résultats vous a été envoyé           │  │
│  └──────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
```

## Architecture des fichiers

```
wp-domain-security-checker.php (MAIN)
│
├─ Activation Hook
│  └─> class-database.php → create_tables()
│
├─ Init Hook
│  ├─> class-shortcode.php
│  │   ├─ register_shortcode()
│  │   └─ register_ajax_handlers()
│  │
│  └─> class-admin.php (si is_admin())
│      └─ add_admin_menu()
│
├─ Enqueue Scripts Hook
│  ├─> assets/css/style.css
│  └─> assets/js/script.js
│
└─ Admin Enqueue Scripts Hook
   ├─> assets/css/admin-style.css
   └─> assets/js/admin-script.js
```

## Flux de sécurité

```
┌────────────────────────────────────────────────────────────┐
│                    REQUÊTE UTILISATEUR                      │
└────────────────────────┬───────────────────────────────────┘
                         │
                         ▼
            ┌────────────────────────┐
            │  Vérification du nonce │
            │  (CSRF Protection)     │
            └────────────┬───────────┘
                         │ ✓ Valid
                         ▼
            ┌────────────────────────┐
            │  Validation des champs │
            │  - domain: non vide    │
            │  - email: format       │
            │  - name: non vide      │
            └────────────┬───────────┘
                         │ ✓ Valid
                         ▼
            ┌────────────────────────┐
            │  Sanitization          │
            │  - sanitize_text_field │
            │  - sanitize_email      │
            │  - strtok, preg_replace│
            └────────────┬───────────┘
                         │ ✓ Clean
                         ▼
            ┌────────────────────────┐
            │  Traitement            │
            │  - DNS Checks          │
            │  - Email Checks        │
            └────────────┬───────────┘
                         │
                         ▼
            ┌────────────────────────┐
            │  Enregistrement DB     │
            │  - $wpdb->prepare()    │
            │  - Prepared statements │
            └────────────┬───────────┘
                         │
                         ▼
            ┌────────────────────────┐
            │  Affichage résultats   │
            │  - esc_html()          │
            │  - esc_attr()          │
            │  - esc_url()           │
            └────────────────────────┘
```

## Classes et responsabilités

| Classe | Fichier | Responsabilité |
|--------|---------|----------------|
| `WPDSC_Database` | class-database.php | Gestion base de données, CRUD opérations |
| `WPDSC_DNS_Checker` | class-dns-checker.php | Vérifications DNS (A, MX, NS, DNSSEC, TXT) |
| `WPDSC_Email_Checker` | class-email-checker.php | Vérifications email (SPF, DMARC, DKIM, etc.) |
| `WPDSC_Shortcode` | class-shortcode.php | Gestion shortcode, AJAX, formulaire |
| `WPDSC_Admin` | class-admin.php | Interface admin, affichage des tests |
| `WPDSC_Email_Notifications` | class-email-notifications.php | Envoi d'emails client/admin |

## Points d'entrée

### Frontend
- **Shortcode**: `[domain_security_checker]`
- **AJAX Endpoint**: `admin-ajax.php?action=wpdsc_check_domain`

### Backend
- **Menu Admin**: "Security Tests" (dashicons-shield)
- **Page détails**: `admin.php?page=domain-security-checker&view={id}`

## Hooks WordPress utilisés

### Activation/Désactivation
- `register_activation_hook()` → Création des tables
- `register_deactivation_hook()` → Nettoyage

### Actions
- `plugins_loaded` → Initialisation du plugin
- `wp_enqueue_scripts` → Chargement CSS/JS frontend
- `admin_enqueue_scripts` → Chargement CSS/JS admin
- `admin_menu` → Ajout du menu admin
- `wp_ajax_wpdsc_check_domain` → Handler AJAX connecté
- `wp_ajax_nopriv_wpdsc_check_domain` → Handler AJAX non connecté

### Shortcodes
- `add_shortcode('domain_security_checker', ...)` → Enregistrement du shortcode

## Sécurité intégrée

✅ **Input Validation**: Tous les champs validés
✅ **Input Sanitization**: `sanitize_text_field()`, `sanitize_email()`
✅ **Output Escaping**: `esc_html()`, `esc_attr()`, `esc_url()`
✅ **SQL Injection Protection**: `$wpdb->prepare()`
✅ **CSRF Protection**: `wp_nonce_field()`, `check_ajax_referer()`
✅ **XSS Protection**: Escaping systématique
✅ **Permissions Check**: `current_user_can('manage_options')`

## Performance

- DNS queries: Native PHP `dns_get_record()` (rapide)
- AJAX: Pas de rechargement de page
- Base de données: Index sur colonnes clés
- Assets: Chargés uniquement où nécessaire
- Pas de bibliothèques externes lourdes

## Compatibilité

✅ WordPress 5.0+
✅ PHP 7.2+
✅ MySQL 5.6+
✅ Tous les thèmes WordPress
✅ Page builders (Gutenberg, Elementor, etc.)
✅ Multisite compatible
✅ Translation ready (Text Domain: wp-domain-security-checker)
