# Frontend Verification Tests

## Installation

1. Copier `test_frontend.py` dans le conteneur Docker :
```bash
docker cp test_frontend.py symfony-web-v2:/var/www/
```

2. Installer Playwright dans le conteneur :
```bash
docker exec -w /var/www symfony-web-v2 bash -c "pip3 install playwright"
docker exec -w /var/www symfony-web-v2 bash -c "playwright install chromium"
```

## Exécution

### Option 1 : Script automatique
```bash
bash run_frontend_tests.sh
```

### Option 2 : Manuelle
```bash
docker exec -w /var/www symfony-web-v2 python3 test_frontend.py
```

## Ce que le script teste

1. ✅ **Homepage** - Vérifie Bootstrap, navbar, hero section
2. ✅ **Registration Page** - Vérifie tous les champs (email, firstname, lastname, dob, password)
3. ✅ **Login Page** - Vérifie le formulaire de connexion
4. ✅ **Registration Flow** - Crée un compte complet
5. ✅ **Login Flow** - Se connecte avec le compte créé
6. ✅ **Dashboard Stats** - Vérifie l'affichage du plan et de l'utilisation
7. ✅ **Subscription Page** - Vérifie les cartes de pricing
8. ✅ **PDF Generation** - Vérifie les 3 onglets (URL, File, WYSIWYG)
9. ✅ **History Page** - Vérifie la page d'historique

## Screenshots

Tous les screenshots sont sauvegardés dans `screenshots/` :
- `homepage.png`
- `registration.png`
- `login.png`
- `dashboard_stats.png`
- `subscription.png`
- `pdf_generation.png`
- `history.png`

## Résolution des problèmes

### Erreur "HOME environment variable"
Le script inclut déjà le patch : `os.environ["HOME"] = tempfile.gettempdir()`

### Erreur "playwright not installed"
```bash
docker exec -w /var/www symfony-web-v2 pip3 install playwright
docker exec -w /var/www symfony-web-v2 playwright install chromium
```

### Erreur "Connection refused"
Vérifiez que le serveur Symfony tourne sur `http://localhost:8320`
