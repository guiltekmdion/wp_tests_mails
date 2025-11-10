# Guide de démarrage rapide - 5 minutes ⚡

## Installation en 3 étapes

### Étape 1 : Installer le plugin
```bash
# Créer le dossier du plugin
cd /chemin/vers/wordpress/wp-content/plugins/
mkdir wp-domain-security-checker

# Copier tous les fichiers du plugin dans ce dossier
cp -r /source/* wp-domain-security-checker/
```

### Étape 2 : Activer le plugin
1. Connectez-vous à WordPress
2. Allez dans **Extensions** → **Extensions installées**
3. Trouvez "WP Domain Security Checker"
4. Cliquez sur **Activer**

### Étape 3 : Utiliser le shortcode
1. Créez une nouvelle page : **Pages** → **Ajouter**
2. Titre : "Testez votre domaine"
3. Contenu : Ajoutez le shortcode
```
[domain_security_checker]
```
4. Cliquez sur **Publier**

## ✅ C'est fait !

Visitez votre page et vous verrez le formulaire de test.

---

## Test rapide

### Frontend (Visiteur)
1. Allez sur votre page
2. Remplissez le formulaire :
   - **Domaine** : `google.com`
   - **Nom** : `Jean Dupont`
   - **Email** : `votre@email.com`
3. Cliquez sur **Analyser**
4. Attendez 5-10 secondes
5. Résultats affichés ! ✨

### Backend (Admin)
1. Dans WordPress, allez dans **Security Tests**
2. Vous verrez le test que vous venez d'effectuer
3. Cliquez sur **Voir détails** pour plus d'informations

### Vérification des emails
1. Vérifiez l'email du client (celui que vous avez saisi)
2. Vérifiez l'email admin (votre email WordPress)

---

## Personnalisation rapide

### Changer le titre et le bouton
```
[domain_security_checker title="Audit gratuit" button_text="Lancer l'audit"]
```

### Modifier les couleurs (CSS personnalisé)
Dans votre thème, ajoutez :
```css
.wpdsc-submit-btn {
    background-color: #your-color !important;
}
```

---

## Dépannage express

### Le formulaire ne s'affiche pas
- ✅ Plugin activé ?
- ✅ Shortcode correct : `[domain_security_checker]`
- ✅ Vérifier la console JavaScript

### Les emails ne sont pas envoyés
- ✅ Installez un plugin SMTP (WP Mail SMTP)
- ✅ Configurez l'email dans **Réglages** → **Général**

### L'analyse échoue
- ✅ Vérifier que les fonctions DNS PHP sont activées
- ✅ Contactez votre hébergeur

---

## Fonctionnalités principales

| Fonction | Description |
|----------|-------------|
| 🔍 **DNS Check** | Vérifie A/AAAA, NS, SOA, CNAME, TXT, PTR, MX, DNSSEC |
| 📧 **Email Security** | Vérifie SPF, DMARC, DKIM |
| 📊 **Scoring** | Note sur 100 (DNS + Email) |
| 💾 **Storage** | Enregistre tous les tests en BDD |
| 📨 **Emails** | Envoi auto au client + admin |
| 🎨 **Responsive** | Fonctionne sur mobile/tablette |
| 🔒 **Sécurisé** | Validation, sanitization, nonces |

---

## Exemples de résultats

### Domaine bien configuré (Google.com)
```
DNS : 90/100 - Excellent
Email : 90/100 - Excellent

✅ SPF configuré
✅ DMARC configuré
✅ DKIM détecté
✅ Tous les enregistrements DNS OK
```

### Domaine avec problèmes
```
DNS : 50/100 - Moyen
Email : 35/100 - Faible

❌ SPF mal configuré (+all)
❌ DMARC manquant
❌ DNSSEC non activé

Recommandations:
• Corriger SPF
• Ajouter DMARC
• Activer DNSSEC
```

---

## Ressources

📚 **Documentation complète** : README-PLUGIN.md
🛠️ **Installation détaillée** : INSTALLATION.md
💡 **Exemples** : EXAMPLES.md
📋 **Résumé** : SUMMARY.md

---

## Support rapide

**Question** : Comment personnaliser les emails ?
**Réponse** : Éditez `includes/class-email-notifications.php`

**Question** : Comment ajouter d'autres vérifications ?
**Réponse** : Modifiez `includes/class-dns-checker.php` ou `includes/class-email-checker.php`

**Question** : Comment exporter les résultats ?
**Réponse** : Consultez la table `wp_domain_security_tests` dans phpMyAdmin

---

## Checklist finale

Avant de mettre en production :

- [ ] Plugin installé et activé
- [ ] Shortcode testé sur une page
- [ ] Email reçu par le client (test)
- [ ] Email reçu par l'admin (test)
- [ ] Interface admin accessible
- [ ] Design cohérent avec votre thème
- [ ] Plugin SMTP configuré (recommandé)
- [ ] Sauvegarde effectuée

---

## 🎉 Félicitations !

Votre plugin WordPress Domain Security Checker est maintenant opérationnel !

Pour aller plus loin, consultez la documentation complète.
