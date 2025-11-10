# Guide d'installation et d'utilisation

## Installation du plugin

### Méthode 1 : Installation via WordPress (Recommandée)

1. Compressez tout le contenu du plugin dans un fichier ZIP nommé `wp-domain-security-checker.zip`
2. Dans WordPress, allez dans **Extensions** > **Ajouter**
3. Cliquez sur **Téléverser une extension**
4. Sélectionnez le fichier ZIP
5. Cliquez sur **Installer maintenant**
6. Activez l'extension

### Méthode 2 : Installation manuelle via FTP

1. Téléchargez tous les fichiers du plugin
2. Connectez-vous à votre serveur via FTP
3. Créez un dossier `wp-domain-security-checker` dans `/wp-content/plugins/`
4. Uploadez tous les fichiers dans ce dossier
5. Dans WordPress, allez dans **Extensions**
6. Trouvez "WP Domain Security Checker" et cliquez sur **Activer**

## Structure du plugin

```
wp-domain-security-checker/
├── wp-domain-security-checker.php  (Fichier principal)
├── includes/
│   ├── class-database.php          (Gestion base de données)
│   ├── class-dns-checker.php       (Vérifications DNS)
│   ├── class-email-checker.php     (Vérifications email)
│   ├── class-shortcode.php         (Shortcode et AJAX)
│   ├── class-admin.php             (Interface admin)
│   └── class-email-notifications.php (Notifications)
├── assets/
│   ├── css/
│   │   ├── style.css               (Styles frontend)
│   │   └── admin-style.css         (Styles admin)
│   └── js/
│       ├── script.js               (JavaScript frontend)
│       └── admin-script.js         (JavaScript admin)
├── README-PLUGIN.md
└── INSTALLATION.md
```

## Configuration

### 1. Après activation

Le plugin créera automatiquement une table dans votre base de données :
- `wp_domain_security_tests` (ou avec votre préfixe personnalisé)

### 2. Vérifier les prérequis

Assurez-vous que votre serveur possède :
- PHP 7.2 ou supérieur
- Les fonctions DNS PHP activées (`dns_get_record`)
- WordPress 5.0 ou supérieur

### 3. Configuration des emails

Les emails utilisent la fonction `wp_mail()` de WordPress. Pour améliorer la délivrabilité :
- Installez un plugin SMTP (WP Mail SMTP, Easy WP SMTP, etc.)
- Configurez l'email d'expéditeur dans **Réglages** > **Général**

## Utilisation

### 1. Ajouter le formulaire sur une page

1. Créez une nouvelle page ou éditez une page existante
2. Ajoutez le shortcode dans le contenu :

```
[domain_security_checker]
```

3. Publiez ou mettez à jour la page

### 2. Personnaliser le shortcode

Vous pouvez personnaliser le titre et le texte du bouton :

```
[domain_security_checker title="Testez la sécurité de votre domaine" button_text="Lancer l'analyse"]
```

### 3. Consulter les résultats dans l'admin

1. Dans WordPress, allez dans le menu **Security Tests**
2. Vous verrez la liste de tous les tests effectués
3. Cliquez sur **Voir détails** pour voir les résultats complets d'un test

### 4. Gérer les demandes de rappel

Les tests avec demande de rappel sont marqués avec ✓ dans la colonne "Rappel".

## Exemples d'utilisation

### Exemple 1 : Page de vérification de sécurité

```html
<h1>Vérifiez la sécurité de votre domaine</h1>
<p>Notre outil analyse gratuitement la sécurité de votre nom de domaine en quelques secondes.</p>

[domain_security_checker title="Analyse gratuite" button_text="Analyser maintenant"]
```

### Exemple 2 : Intégration dans une page de services

```html
<h2>Service d'audit de sécurité</h2>
<p>Nous vérifions la configuration DNS et les politiques de sécurité email de votre domaine.</p>

[domain_security_checker]

<h3>Ce que nous vérifions :</h3>
<ul>
  <li>Configuration DNS (A, MX, NS, DNSSEC)</li>
  <li>Politiques email (SPF, DMARC, DKIM)</li>
  <li>Sécurité du transport (MTA-STS, TLS-RPT)</li>
</ul>
```

## Tests effectués par le plugin

### Vérifications DNS (Score sur 100)
- ✓ Enregistrements A (20 points)
- ✓ Enregistrements MX (20 points)
- ✓ Enregistrements NS (20 points)
- ✓ DNSSEC (30 points)
- ✓ Enregistrements TXT (10 points)

### Vérifications Email (Score sur 100)
- ✓ SPF - Sender Policy Framework (35 points)
- ✓ DMARC - Domain-based Message Authentication (35 points)
- ✓ DKIM - DomainKeys Identified Mail (20 points)
- ✓ MTA-STS - SMTP Transport Security (5 points)
- ✓ TLS-RPT - TLS Reporting (5 points)

## Notifications par email

### Email au client
Le client reçoit automatiquement un email contenant :
- Les résultats détaillés de l'analyse
- Les scores DNS et Email
- Des recommandations d'amélioration

### Email à l'administrateur
L'administrateur du site reçoit une notification avec :
- Les informations du client (nom, email)
- Le domaine testé
- Indication si le client souhaite être rappelé

## Dépannage

### Le formulaire ne s'affiche pas
- Vérifiez que le plugin est bien activé
- Vérifiez que le shortcode est correctement saisi
- Consultez la console JavaScript pour les erreurs

### Les emails ne sont pas envoyés
- Vérifiez la configuration SMTP de WordPress
- Installez un plugin SMTP
- Vérifiez les logs d'erreur PHP

### Les vérifications DNS échouent
- Vérifiez que les fonctions DNS PHP sont activées
- Contactez votre hébergeur si nécessaire
- Vérifiez que le pare-feu autorise les requêtes DNS sortantes

### Les résultats ne s'enregistrent pas
- Vérifiez les permissions de la base de données
- Consultez les logs d'erreur WordPress
- Vérifiez que la table a été créée lors de l'activation

## Support et Maintenance

### Mise à jour du plugin
- Les mises à jour doivent être effectuées via WordPress
- Veillez à faire une sauvegarde avant toute mise à jour

### Base de données
La table des résultats peut croître avec le temps. Pour la nettoyer :
```sql
-- Supprimer les tests de plus de 6 mois
DELETE FROM wp_domain_security_tests WHERE test_date < DATE_SUB(NOW(), INTERVAL 6 MONTH);
```

### Logs
Les logs d'erreur sont enregistrés dans le fichier `debug.log` de WordPress si `WP_DEBUG_LOG` est activé.

## Sécurité

Le plugin implémente :
- ✓ Validation et sanitization de toutes les entrées
- ✓ Vérification des nonces AJAX
- ✓ Protection contre les injections SQL (via $wpdb->prepare)
- ✓ Protection XSS (via esc_html, esc_attr, etc.)
- ✓ Enregistrement des adresses IP pour traçabilité

## Performance

Pour optimiser les performances :
- Le plugin utilise des requêtes DNS natives PHP (rapides)
- Les résultats sont mis en cache dans la base de données
- L'interface utilise AJAX pour éviter les rechargements de page
- Les assets sont chargés uniquement où nécessaire

## Personnalisation avancée

### Modifier les styles CSS
Éditez les fichiers :
- `assets/css/style.css` pour le frontend
- `assets/css/admin-style.css` pour l'admin

### Ajouter des vérifications personnalisées
Modifiez les classes dans le dossier `includes/` :
- `class-dns-checker.php` pour les vérifications DNS
- `class-email-checker.php` pour les vérifications email

### Modifier les templates d'email
Éditez `includes/class-email-notifications.php`

## Licence

GPL v2 or later - Vous êtes libre de modifier et redistribuer ce plugin.
