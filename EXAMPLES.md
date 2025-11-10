# Exemples d'utilisation du plugin

## Exemple 1 : Résultat d'analyse pour un domaine bien configuré

### Domaine testé : `google.com`

#### Résultats DNS (Score : 90/100)
- ✅ **Enregistrements A** : OK - 1 enregistrement trouvé
- ✅ **Enregistrements MX** : OK - 5 serveurs de messagerie trouvés
- ✅ **Enregistrements NS** : OK - 4 serveurs de noms trouvés
- ⚠️ **DNSSEC** : AVERTISSEMENT - Non détecté
- ✅ **Enregistrements TXT** : INFO - 8 enregistrements trouvés

**Évaluation** : Excellent

#### Résultats Email (Score : 90/100)
- ✅ **SPF** : OK - SPF est configuré
  - Enregistrement : `v=spf1 include:_spf.google.com ~all`
  - Politique souple (~all)
- ✅ **DMARC** : OK - DMARC est configuré
  - Enregistrement : `v=DMARC1; p=quarantine; rua=mailto:...`
  - Politique : quarantine
- ✅ **DKIM** : OK - DKIM trouvé (sélecteurs: google, default)
- ✅ **MTA-STS** : OK - MTA-STS est activé
- ⚠️ **TLS-RPT** : INFO - TLS-RPT n'est pas configuré

**Évaluation** : Excellent

---

## Exemple 2 : Résultat pour un domaine avec problèmes

### Domaine testé : `exemple-non-securise.com`

#### Résultats DNS (Score : 50/100)
- ✅ **Enregistrements A** : OK - 1 enregistrement trouvé
- ✅ **Enregistrements MX** : OK - 1 serveur de messagerie trouvé
- ✅ **Enregistrements NS** : OK - 2 serveurs de noms trouvés
- ❌ **DNSSEC** : AVERTISSEMENT - DNSSEC n'est pas détecté
- ❌ **Enregistrements TXT** : INFO - Aucun enregistrement TXT trouvé

**Évaluation** : Moyen

#### Résultats Email (Score : 35/100)
- ✅ **SPF** : OK - SPF est configuré
  - Enregistrement : `v=spf1 +all`
  - ⚠️ DANGER : +all autorise tous les serveurs à envoyer des emails
- ❌ **DMARC** : AVERTISSEMENT - DMARC n'est pas configuré
- ❌ **DKIM** : AVERTISSEMENT - DKIM non détecté (vérification limitée)
- ❌ **MTA-STS** : INFO - MTA-STS n'est pas configuré
- ❌ **TLS-RPT** : INFO - TLS-RPT n'est pas configuré

**Évaluation** : Faible

**Recommandations** :
1. Configurez DNSSEC pour protéger votre DNS
2. Corrigez votre enregistrement SPF (remplacez +all par -all)
3. Ajoutez un enregistrement DMARC
4. Configurez DKIM pour authentifier vos emails

---

## Exemple 3 : Intégration dans différents types de pages

### Page de landing

```html
<div class="hero-section">
  <h1>Protégez votre entreprise avec une infrastructure email sécurisée</h1>
  <p>Testez gratuitement la sécurité de votre domaine en 30 secondes</p>
</div>

[domain_security_checker title="Analyse gratuite de sécurité" button_text="Tester maintenant"]

<div class="benefits">
  <h2>Pourquoi tester votre domaine ?</h2>
  <ul>
    <li>Éviter que vos emails soient marqués comme spam</li>
    <li>Protéger votre réputation en ligne</li>
    <li>Détecter les vulnérabilités de sécurité</li>
    <li>Améliorer la délivrabilité de vos emails</li>
  </ul>
</div>
```

### Page de services IT

```html
<h2>Services d'audit de sécurité</h2>

<div class="service-box">
  <h3>Audit DNS et Email gratuit</h3>
  <p>Notre outil vérifie automatiquement :</p>
  <ul>
    <li>La configuration DNS de votre domaine</li>
    <li>Les politiques de sécurité email (SPF, DMARC, DKIM)</li>
    <li>Les protocoles de transport sécurisé</li>
  </ul>
  
  [domain_security_checker]
</div>

<div class="contact-box">
  <h3>Besoin d'aide ?</h3>
  <p>Nos experts peuvent vous aider à corriger les problèmes détectés.</p>
</div>
```

### Page de blog

```html
<article>
  <h1>Comment sécuriser votre domaine contre les attaques par email</h1>
  
  <p>Dans cet article, nous allons voir pourquoi il est crucial de configurer correctement SPF, DMARC et DKIM...</p>
  
  <h2>Testez votre domaine maintenant</h2>
  <p>Avant de commencer, vérifiez l'état actuel de votre configuration :</p>
  
  [domain_security_checker button_text="Vérifier mon domaine"]
  
  <h2>Comprendre les résultats</h2>
  <p>Une fois l'analyse terminée, vous recevrez un rapport détaillé...</p>
</article>
```

---

## Exemple 4 : Email reçu par le client

```
De : VotreSite <admin@votresite.com>
À : client@exemple.com
Objet : Résultats de l'analyse de sécurité pour exemple.com

Bonjour Jean Dupont,

Voici les résultats de l'analyse de sécurité pour le domaine exemple.com.

═══════════════════════════════════════
SÉCURITÉ DNS
═══════════════════════════════════════
Score : 70/100
Évaluation : Bon

Détails :
✅ A records: OK
✅ Mx records: OK
✅ Ns records: OK
⚠️ Dnssec: AVERTISSEMENT - DNSSEC n'est pas détecté
✅ Txt records: INFO

═══════════════════════════════════════
SÉCURITÉ EMAIL
═══════════════════════════════════════
Score : 70/100
Évaluation : Bon

Détails :
✅ SPF: OK - SPF est configuré
✅ DMARC: OK - DMARC est configuré
⚠️ DKIM: AVERTISSEMENT - DKIM non détecté (vérification limitée)
⚠️ MTA_STS: INFO - MTA-STS n'est pas configuré
⚠️ TLS_RPT: INFO - TLS-RPT n'est pas configuré

═══════════════════════════════════════
RECOMMANDATIONS
═══════════════════════════════════════
• Configurez DKIM pour authentifier vos emails sortants.

Si vous avez des questions ou souhaitez améliorer la sécurité de votre domaine, 
n'hésitez pas à nous contacter.

Cordialement,
L'équipe VotreSite
```

---

## Exemple 5 : Email reçu par l'administrateur

```
De : WordPress <wordpress@votresite.com>
À : admin@votresite.com
Objet : Nouvelle analyse de domaine effectuée : exemple.com

═══════════════════════════════════════
NOUVELLE ANALYSE DE DOMAINE
═══════════════════════════════════════

Une nouvelle analyse de domaine a été effectuée sur votre site.

INFORMATIONS DU CLIENT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Nom : Jean Dupont
Email : client@exemple.com
Domaine testé : exemple.com
Date : 10/11/2025 à 14:45

⚠️ DEMANDE DE RAPPEL : Le client souhaite être rappelé.

Vous pouvez consulter les résultats détaillés dans le back-office de WordPress.

[Voir dans le back-office]

Cette notification a été générée automatiquement par le plugin 
Domain Security Checker.
```

---

## Exemple 6 : Code pour personnaliser l'apparence

### CSS personnalisé pour le formulaire

```css
/* Ajouter dans votre thème ou via Customizer */

/* Changer la couleur du bouton */
.wpdsc-submit-btn {
    background-color: #ff6b6b !important;
}

.wpdsc-submit-btn:hover {
    background-color: #ee5253 !important;
}

/* Changer la couleur des barres de score */
.wpdsc-score-value {
    background: linear-gradient(90deg, #667eea, #764ba2) !important;
}

/* Personnaliser le conteneur */
.wpdsc-container {
    max-width: 800px !important;
}

.wpdsc-form-wrapper {
    border: 2px solid #667eea !important;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2) !important;
}
```

---

## Exemple 7 : Utilisation avec Elementor ou autres page builders

### Avec Elementor
1. Ajoutez un widget "Shortcode"
2. Insérez : `[domain_security_checker]`
3. Personnalisez le style via les options Elementor

### Avec Gutenberg
1. Ajoutez un bloc "Shortcode"
2. Insérez : `[domain_security_checker]`

### Avec Classic Editor
Insérez simplement le shortcode dans le contenu :
```
[domain_security_checker]
```

---

## Exemple 8 : Interface Admin - Vue liste

```
┌─────────────────────────────────────────────────────────────────┐
│ Tests de sécurité de domaine                                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│ 📊 Tests effectués : 127                                        │
│                                                                  │
│ Shortcode : [domain_security_checker]                           │
│                                                                  │
├──────┬───────────────┬──────────────┬───────────────┬──────────┤
│ ID   │ Domaine       │ Nom          │ Email         │ Rappel   │
├──────┼───────────────┼──────────────┼───────────────┼──────────┤
│ 127  │ google.com    │ Jean Dupont  │ jean@test.com │    ✓     │
│ 126  │ example.com   │ Marie Claire │ marie@ex.com  │    ✗     │
│ 125  │ github.com    │ Paul Martin  │ paul@git.com  │    ✓     │
└──────┴───────────────┴──────────────┴───────────────┴──────────┘
```

---

## Exemple 9 : Données stockées dans la base de données

### Structure de la table `wp_domain_security_tests`

```sql
mysql> SELECT id, domain_name, user_name, user_email, callback_requested, test_date 
       FROM wp_domain_security_tests LIMIT 3;

+----+-------------+--------------+------------------+--------------------+---------------------+
| id | domain_name | user_name    | user_email       | callback_requested | test_date           |
+----+-------------+--------------+------------------+--------------------+---------------------+
|  1 | google.com  | Jean Dupont  | jean@test.com    |                  1 | 2025-11-10 10:30:45 |
|  2 | example.com | Marie Claire | marie@example.fr |                  0 | 2025-11-10 11:15:22 |
|  3 | github.com  | Paul Martin  | paul@github.fr   |                  1 | 2025-11-10 12:00:10 |
+----+-------------+--------------+------------------+--------------------+---------------------+
```

---

## Exemple 10 : Cas d'usage réels

### Agence web
Offrir un audit gratuit pour attirer des prospects

### Hébergeur web
Proposer un outil de diagnostic aux clients

### Service IT
Évaluation rapide de la configuration des clients

### Consultant sécurité
Rapport automatisé lors de la première prise de contact

### Formation
Outil pédagogique pour expliquer la sécurité email

---

## Support

Pour plus d'exemples ou des questions, consultez la documentation complète dans README-PLUGIN.md
