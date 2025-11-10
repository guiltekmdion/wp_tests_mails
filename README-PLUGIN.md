# WP Domain Security Checker

Plugin WordPress permettant de tester la sécurité d'un nom de domaine via un shortcode. Le plugin vérifie les configurations DNS et les politiques de sécurité email (SPF, DMARC, DKIM).

## Fonctionnalités

### 1. Vérifications DNS
- **Enregistrements A** : Vérification de la résolution du domaine
- **Enregistrements MX** : Vérification des serveurs de messagerie
- **Enregistrements NS** : Vérification des serveurs de noms
- **DNSSEC** : Détection de la sécurité DNS
- **Enregistrements TXT** : Liste des enregistrements texte

### 2. Vérifications Email
- **SPF** (Sender Policy Framework) : Politique d'envoi d'emails
- **DMARC** (Domain-based Message Authentication) : Authentification des messages
- **DKIM** (DomainKeys Identified Mail) : Signature des emails
- **MTA-STS** : Sécurité du transport SMTP
- **TLS-RPT** : Rapports de sécurité TLS

### 3. Interface Utilisateur
- Formulaire frontend via shortcode
- Système de notation sur 100 points
- Affichage des résultats détaillés
- Interface d'administration pour consulter l'historique

### 4. Notifications
- Email au client avec les résultats détaillés
- Email à l'administrateur pour chaque nouveau test
- Option "demande de rappel"

### 5. Base de données
- Enregistrement de tous les tests effectués
- Stockage des informations client
- Historique complet des analyses

## Installation

1. Télécharger le plugin
2. Décompresser dans le dossier `/wp-content/plugins/`
3. Activer le plugin dans l'interface WordPress
4. Utiliser le shortcode dans vos pages ou articles

## Utilisation

### Shortcode de base

```
[domain_security_checker]
```

### Shortcode personnalisé

```
[domain_security_checker title="Vérifiez votre sécurité" button_text="Lancer le test"]
```

### Attributs disponibles

- `title` : Titre du formulaire (défaut: "Testez la sécurité de votre domaine")
- `button_text` : Texte du bouton (défaut: "Analyser")

## Interface Admin

Accédez à l'interface d'administration via le menu **Security Tests** dans WordPress.

### Fonctionnalités admin :
- Consultation de tous les tests effectués
- Vue détaillée de chaque test
- Statistiques globales
- Identification des demandes de rappel

## Scoring

### DNS (100 points)
- Enregistrements A : 20 points
- Enregistrements MX : 20 points
- Enregistrements NS : 20 points
- DNSSEC : 30 points
- Enregistrements TXT : 10 points

### Email (100 points)
- SPF : 35 points
- DMARC : 35 points
- DKIM : 20 points
- MTA-STS : 5 points
- TLS-RPT : 5 points

### Évaluations
- 90-100% : Excellent
- 70-89% : Bon
- 50-69% : Moyen
- 0-49% : Faible

## Sécurité

Le plugin implémente plusieurs mesures de sécurité :
- Validation et sanitization de toutes les entrées
- Vérification des nonces pour les requêtes AJAX
- Protection contre l'injection SQL
- Protection contre les attaques XSS
- Enregistrement de l'adresse IP pour traçabilité

## Compatibilité

- WordPress 5.0 ou supérieur
- PHP 7.2 ou supérieur
- Fonctions DNS PHP activées

## Support

Pour toute question ou problème, veuillez créer une issue sur le dépôt GitHub.

## Licence

GPL v2 or later

## Auteur

Développé pour wp_tests_mails
