# Critères de notation

**Rendu : envoi par email de l'URL de votre dépôt GitHub**

**Application hébergée et accessible en ligne**

---

## 1. Source code

<table id="bkmrk-crit%C3%A8re-description-"><thead><tr><th>Critère</th><th>Description</th></tr></thead><tbody><tr><td>**GIT**</td><td>Dépôt GitHub propre, README présent</td></tr><tr><td>**GitFlow**</td><td>Utilisation de branches `feature/*`, `fix/*` — pas de commit direct sur `main`</td></tr><tr><td>**Conventional Commits**</td><td>Messages de commit normalisés : `feat:`, `fix:`, `chore:`, `test:`, `docs:`</td></tr><tr><td>**PHPUnit**</td><td>Tests unitaires et fonctionnels présents et passants</td></tr></tbody></table>

---

## 2. Projet

### 2.1 Routes publiques

Accessibles sans authentification.

<table id="bkmrk-route-description-%2F-"><thead><tr><th>Route</th><th>Description</th></tr></thead><tbody><tr><td>`/`</td><td>Page d'accueil présentant le service, les outils disponibles et les plans tarifaires</td></tr><tr><td>`/register`</td><td>Formulaire d'inscription avec vérification de l'adresse email</td></tr><tr><td>`/login`</td><td>Formulaire de connexion</td></tr><tr><td>`/reset-password`</td><td>Demande de réinitialisation de mot de passe par email

</td></tr><tr><td>/XXXX</td><td>Autres si conversion "gratuite"

</td></tr></tbody></table>

---

### 2.2 Routes sécurisées

Accessibles uniquement aux utilisateurs authentifiés (`ROLE_USER` minimum).

#### Abonnements

<table id="bkmrk-route-description-%2Fp"><thead><tr><th>Route</th><th>Description</th></tr></thead><tbody><tr><td>`/payment/checkout/{plan}`</td><td>Redirection vers Stripe Checkout pour souscrire à un plan</td></tr><tr><td>`/payment/success`</td><td>Page de confirmation après paiement</td></tr><tr><td>`/payment/cancel`</td><td>Page d'annulation</td></tr><tr><td>`/payment/webhook`</td><td>Réception des événements Stripe — mise à jour du plan en base</td></tr></tbody></table>

> L'utilisateur doit pouvoir changer **facilement** d'abonnement depuis l'interface, via STRIPE

#### Historique

<table id="bkmrk-route-description-%2Fa"><thead><tr><th>Route</th><th>Description</th></tr></thead><tbody><tr><td>`/account/history`</td><td>Liste des PDFs générés par l'utilisateur avec date, type et bouton de re-téléchargement</td></tr></tbody></table>

#### Génération de PDFs

<table id="bkmrk-route-outil-%2Fconvert"><thead><tr><th>Route</th><th>Outil</th></tr></thead><tbody><tr><td>`/convert/url`</td><td>URL → PDF</td></tr><tr><td>`/convert/html`</td><td>Fichier HTML → PDF</td></tr><tr><td>`/convert/markdown`</td><td>Fichier Markdown → PDF</td></tr><tr><td>`/convert/office`</td><td>Document Office (Word, Excel, PowerPoint) → PDF</td></tr><tr><td>`/convert/merge`</td><td>Fusion de plusieurs PDFs en un seul</td></tr><tr><td>`/convert/screenshot`</td><td>Capture d'écran d'une URL → PNG</td></tr><tr><td>`/convert/wysiwyg`</td><td>Éditeur WYSIWYG (texte riche) → PDF</td></tr></tbody></table>

> Chaque outil doit vérifier que le plan de l'utilisateur lui donne accès à la fonctionnalité.

#### Quota de générations

- Le nombre de PDFs générés dans la journée est enregistré en base.
- Si l'utilisateur a atteint la limite de son plan, la génération est bloquée.
- Le quota restant est affiché à l'utilisateur (ex : "3 / 5 générations utilisées aujourd'hui").

---

### 2.3 Contrôle d'accès

<table id="bkmrk-r%C3%A8gle-comportement-a"><thead><tr><th>Règle</th><th>Comportement attendu</th></tr></thead><tbody><tr><td>Outil non inclus dans le plan</td><td>Bouton verrouillé sur la homepage, erreur 403 si accès direct à la route</td></tr><tr><td>Quota journalier dépassé</td><td>Génération bloquée avec message explicite</td></tr><tr><td>Utilisateur non connecté</td><td>Redirection vers `/login`</td></tr><tr><td>Page d'erreur 403</td><td>Page personnalisée avec le plan actuel et lien vers les offres</td></tr></tbody></table>

---

### 2.4 Frontend

- Templates pour toutes les pages listées ci-dessus
- Affichage conditionnel selon l'état de l'utilisateur (connecté / non connecté / plan)
- Charte graphique cohérente sur l'ensemble des pages

---

## 3. Autres éléments

<table id="bkmrk-%C3%89l%C3%A9ment-description-"><thead><tr><th>Élément</th><th>Description</th></tr></thead><tbody><tr><td>**Envoi par email**</td><td>Après génération, envoi du PDF en pièce jointe à l'utilisateur</td></tr><tr><td>**Partage PDF aux contacts**</td><td>Possibilité d'envoyer des contacts le PDF</td></tr><tr><td>**Commande `app:handle-queue`**</td><td>Merge de PDFs </td></tr></tbody></table>

### Commande `app:handle-queue`

1. Désactiver la génération immédiate depuis le contrôleur — les demandes sont ajoutées dans une file d'attente (`queue`)
2. La commande récupère les X prochains éléments en attente et les génère
3. La commande est exécutée automatiquement toutes les 10 minutes via crontab
4. La commande lance le "merge" des PDFS donnés dans l'interface