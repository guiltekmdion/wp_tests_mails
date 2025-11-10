# WP Domain Security Checker

Plugin WordPress professionnel permettant de tester la sécurité d'un domaine (DNS et configuration email) via un shortcode simple.

## 🎯 Fonctionnalités principales

- ✅ **Vérification DNS complète** : A/AAAA, NS (cohérence), SOA, CNAME, TXT, PTR, MX, DNSSEC
- ✅ **Analyse de sécurité email** : SPF, DMARC, DKIM, MTA-STS, TLS-RPT
- ✅ **Shortcode WordPress** : `[domain_security_checker]`
- ✅ **Interface admin** : Consultation de tous les tests effectués
- ✅ **Notifications email** : Envoi automatique des résultats au client et à l'admin
- ✅ **Scoring intelligent** : Note sur 100 pour DNS et Email
- ✅ **Demande de rappel** : Option pour que les clients soient recontactés
- ✅ **Sécurité renforcée** : Validation, sanitization, nonces

## 📋 Prérequis

- WordPress 5.0 ou supérieur
- PHP 7.2 ou supérieur
- Fonctions DNS PHP activées

## 📦 Installation rapide

1. Téléchargez le plugin
2. Placez-le dans `/wp-content/plugins/wp-domain-security-checker/`
3. Activez le plugin dans WordPress
4. Utilisez le shortcode `[domain_security_checker]` dans vos pages

## 📚 Documentation

- **[README-PLUGIN.md](README-PLUGIN.md)** - Documentation complète du plugin
- **[INSTALLATION.md](INSTALLATION.md)** - Guide d'installation détaillé
- **[EXAMPLES.md](EXAMPLES.md)** - Exemples d'utilisation

## 🚀 Utilisation

### Shortcode basique
```
[domain_security_checker]
```

### Shortcode personnalisé
```
[domain_security_checker title="Vérifiez votre sécurité" button_text="Lancer le test"]
```

## 🎨 Captures d'écran

Le plugin inclut :
- Formulaire frontend responsive et moderne
- Interface admin pour consulter tous les tests
- Emails HTML professionnels
- Système de scoring visuel

## 🛠️ Ce qui est testé

### DNS (130 points)
- Enregistrements A/AAAA (20pts)
- Enregistrements NS - min 2 (15pts)
- Cohérence NS (10pts)
- Enregistrement SOA (10pts)
- Absence CNAME racine (5pts)
- Enregistrements TXT (10pts)
- Reverse DNS PTR (10pts)
- Enregistrements MX (20pts)
- DNSSEC (30pts)

### Email (100 points)
- SPF (35pts)
- DMARC (35pts)
- DKIM (20pts)
- MTA-STS (5pts)
- TLS-RPT (5pts)

## 🔒 Sécurité

- Validation et sanitization de toutes les entrées
- Protection contre les injections SQL
- Protection XSS
- Vérification des nonces AJAX
- Enregistrement des IP pour traçabilité

## 📧 Support

Pour toute question ou problème, créez une issue sur GitHub.

## 📄 Licence

GPL v2 or later

## 👨‍💻 Auteur

Développé pour wp_tests_mails
