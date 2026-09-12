# API Invaders Mapper — v1

API REST permettant à vos utilisateurs de piloter leurs flashs Invaders depuis
leurs propres outils (script Python, application mobile, domotique, tableur…),
sans passer par la carte.

Chaque clé API ne donne accès **qu'aux données de son propriétaire** : les listes
renvoyées et les modifications effectuées sont toujours filtrées sur l'utilisateur
authentifié, exactement comme sur la carte.

---

## 1. Installation

### 1.1 Créer la table des clés API

À exécuter **une seule fois** dans phpMyAdmin (onglet SQL), sur la base
`nom_de_la_base` :

```sql
CREATE TABLE IF NOT EXISTS `api_keys` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_name`         VARCHAR(100) NOT NULL,
  `key_hash`          CHAR(64) NOT NULL,
  `label`             VARCHAR(100) NOT NULL DEFAULT '',
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_used_at`      DATETIME NULL DEFAULT NULL,
  `revoked_at`        DATETIME NULL DEFAULT NULL,
  `request_count`     INT UNSIGNED NOT NULL DEFAULT 0,
  `window_started_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_key_hash` (`key_hash`),
  KEY `idx_user_name` (`user_name`),
  KEY `idx_active` (`key_hash`, `revoked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Le script complet, commenté, se trouve dans `api/sql/01_api_keys.sql`.

**Aucune table existante n'est modifiée.** Cette table est nouvelle et
indépendante : elle ne touche ni `users`, ni `user_flash`, ni `positions`.

### 1.2 Vérifier la configuration

Dans `api/api_bootstrap.php`, trois constantes sont à ajuster si besoin :

| Constante | Valeur par défaut | Rôle |
|---|---|---|
| `API_DEBUG` | `false` | Passer à `true` temporairement pour voir les erreurs PHP |
| `API_RATE_LIMIT` | `600` | Requêtes max par heure et par clé (`0` = illimité) |
| `API_CORS_ORIGIN` | `'*'` | Domaine autorisé à appeler l'API depuis un navigateur |

### 1.3 Vérifier que la réécriture d'URL fonctionne

Testez dans un navigateur :

```
https://votre-site/api/v1/ping
```

- Si vous obtenez `{"data":{"api":"Invaders Mapper API","version":"v1",...}}` → tout va bien.
- Si vous obtenez une erreur 404, votre hébergement ne gère pas les `.htaccess`.
  Utilisez alors la forme explicite, qui fonctionne partout :

```
https://votre-site/api/v1/index.php/invaders?status=a_flasher
```

---

## 2. Authentification

### 2.1 Obtenir une clé

**Depuis le site** : page `settings.php`, onglet **API** → « Générer une nouvelle clé ».

**Depuis un outil externe** : `POST /api/v1/login`

```bash
curl -X POST -H "Content-Type: application/json" \
     -d '{"login":"mon_login","password":"mon_mot_de_passe","label":"mon script python"}' \
     "https://votre-site/api/v1/login"
```

Réponse :

```json
{
  "data": {
    "api_key": "inv_3f2a...",
    "label": "mon script python",
    "login": "mon_login",
    "created_at": "2026-09-12 10:00:00",
    "warning": "Conservez cette cle : elle ne sera plus jamais affichee."
  }
}
```

> La clé n'est affichée **qu'une seule fois**. Elle est stockée hachée en SHA-256
> en base : impossible de la retrouver ensuite. En cas de perte, révoquez-la et
> créez-en une nouvelle.

### 2.2 Utiliser la clé

Dans l'en-tête HTTP `Authorization` :

```
Authorization: Bearer inv_3f2a...
```

Par commodité, la clé peut aussi être passée en paramètre d'URL
(`?api_key=inv_3f2a...`), mais l'en-tête est préférable : il n'apparaît pas dans
les journaux du serveur.

---

## 3. Points d'entrée

| Méthode | URL | Description |
|---|---|---|
| `GET` | `/api/v1/ping` | Vérifie que l'API répond (pas de clé requise) |
| `GET` | `/api/v1/me` | Compte associé à la clé |
| `GET` | `/api/v1/stats` | Nombre d'invaders à flasher / flashés / détruits |
| `GET` | `/api/v1/invaders?status=a_flasher` | Invaders à flasher (GeoJSON) |
| `GET` | `/api/v1/invaders?status=deja_flashe` | Invaders déjà flashés (GeoJSON) |
| `GET` | `/api/v1/invaders?status=detruits` | Invaders détruits / inaccessibles (GeoJSON) |
| `GET` | `/api/v1/invaders?status=all` | Les trois listes d'un coup |
| `GET` | `/api/v1/invaders/{inv_name}` | Détail d'un invader |
| `POST` | `/api/v1/invaders/{inv_name}/flash` | Marquer comme flashé |
| `DELETE` | `/api/v1/invaders/{inv_name}/flash` | Annuler le flash |
| `PUT` | `/api/v1/invaders/{inv_name}/status` | Changer l'état (corps : `{"etat":"..."}`) |
| `DELETE` | `/api/v1/invaders/{inv_name}/status` | Revenir à l'état global |
| `POST` | `/api/v1/login` | Échanger login + mot de passe contre une clé |

---

## 4. Format des réponses

### Succès

Toutes les réponses de succès sont enveloppées dans une clé `data` :

```json
{ "data": { ... } }
```

### Erreur

```json
{
  "error": {
    "code": "invader_not_found",
    "message": "L'invader 'PA_9999' n'existe pas.",
    "details": { }
  }
}
```

| Code HTTP | Signification |
|---|---|
| `200` | Succès |
| `201` | Ressource créée (nouvelle clé API) |
| `400` | Paramètre manquant ou invalide |
| `401` | Clé API manquante, invalide ou révoquée |
| `404` | Invader ou route inconnue |
| `405` | Méthode HTTP non autorisée sur cette ressource |
| `429` | Trop de requêtes (limite horaire) ou trop de clés actives |

### Format GeoJSON des listes

Les listes reprennent **exactement** le format utilisé par la carte
(`get_geojson_a_flasher()`, `get_geojson_deja_flashe()`, `get_geojson_detruits()`) :

```json
{
  "data": {
    "type": "FeatureCollection",
    "features": [
      {
        "type": "Feature",
        "id": "1",
        "geometry": { "type": "Point", "coordinates": [2.5019, 48.67895] },
        "properties": {
          "name": "PA_1238",
          "points": "10",
          "etat": "OK",
          "last_maj": "2024-01-15",
          "image1": "./img_invader/pa_1238_1.png",
          "image1_c": "invader spotter",
          "image2": "/img/user_img/PA_1238_1234567890_abc.webp",
          "image2_c": "mon_login",
          "image2_d": "2024-01-14",
          "more_p": false
        }
      }
    ]
  }
}
```

> Les coordonnées sont au format GeoJSON standard : **`[longitude, latitude]`**
> (attention, c'est l'inverse de l'ordre habituel).

---

## 5. Exemples

### curl

```bash
CLE="inv_3f2a..."
BASE="https://votre-site/api/v1"

# Ce qu'il me reste à flasher
curl -H "Authorization: Bearer $CLE" "$BASE/invaders?status=a_flasher"

# J'ai flashé PA_1238
curl -X POST -H "Authorization: Bearer $CLE" "$BASE/invaders/PA_1238/flash"

# Finalement non, je ne l'ai pas
curl -X DELETE -H "Authorization: Bearer $CLE" "$BASE/invaders/PA_1238/flash"

# Il est détruit
curl -X PUT -H "Authorization: Bearer $CLE" \
     -H "Content-Type: application/json" \
     -d '{"etat":"Detruit !"}' \
     "$BASE/invaders/PA_1238/status"

# Revenir à l'état global
curl -X DELETE -H "Authorization: Bearer $CLE" "$BASE/invaders/PA_1238/status"
```

### Python

```python
import requests

API = "https://votre-site/api/v1"
HEADERS = {"Authorization": "Bearer inv_3f2a..."}

# Les invaders qu'il me reste à flasher
reponse = requests.get(f"{API}/invaders", params={"status": "a_flasher"}, headers=HEADERS)
reponse.raise_for_status()
invaders = reponse.json()["data"]["features"]

for invader in invaders:
    props = invader["properties"]
    lon, lat = invader["geometry"]["coordinates"]
    print(f"{props['name']} ({props['etat']}) -> {lat}, {lon}")

# J'ai flashé le premier de la liste
nom = invaders[0]["properties"]["name"]
requests.post(f"{API}/invaders/{nom}/flash", headers=HEADERS).raise_for_status()
print(f"{nom} marqué comme flashé")
```

### JavaScript (navigateur)

```javascript
const API = "https://votre-site/api/v1";
const CLE = "inv_3f2a...";

const reponse = await fetch(`${API}/invaders?status=a_flasher`, {
  headers: { "Authorization": `Bearer ${CLE}` }
});
const { data } = await reponse.json();

console.log(`${data.features.length} invaders à flasher`);
```

---

## 6. Valeurs d'état acceptées

Le champ `etat` de `PUT /invaders/{inv_name}/status` doit correspondre à un état
existant dans la table `etat`. Les valeurs actuellement utilisées par le site sont :

- `OK`
- `Un peu dégradé`
- `Dégradé`
- `Très dégradé`
- `Détruit !`
- `Non visible`
- `Inaccessible`
- `Inconnu`
- `no info`

Pour obtenir la liste exacte et à jour, appelez l'API avec un état invalide :
le message d'erreur renvoie la liste complète des valeurs acceptées.

---

## 7. Fichiers

```
api/
├── .htaccess                 Réécriture d'URL (racine de l'API)
├── api_bootstrap.php         Authentification, helpers JSON, CORS
├── README.md                 Ce fichier
├── settings_api_tab.php      Onglet "API" de settings.php
├── sql/
│   └── 01_api_keys.sql       Création de la table api_keys
└── v1/
    ├── .htaccess             Réécriture d'URL (niveau v1)
    ├── index.php             Routeur principal
    └── login.php             Échange login/mot de passe contre une clé
```

L'API réutilise les fonctions existantes de `fonctions.inc.php`
(`get_geojson_a_flasher()`, `ajout_flash()`, `suppri_flash()`, `update_status()`)
et la connexion PDO de `config.php`. Aucune logique métier n'est dupliquée :
si vous modifiez une fonction du site, l'API suit automatiquement.

---

## 8. Sécurité

- Les clés sont stockées **hachées en SHA-256**, jamais en clair.
- Une clé peut être **révoquée** à tout moment depuis `settings.php` ; les outils
  qui l'utilisent cessent immédiatement de fonctionner.
- Chaque requête est **limitée à 600 par heure** et par clé (paramétrable).
- Un utilisateur ne peut pas dépasser **10 clés actives**.
- Toutes les requêtes sont filtrées sur l'utilisateur propriétaire de la clé :
  il est impossible de lire ou de modifier les données d'un autre compte.
- L'API est **stateless** : elle n'utilise aucune session PHP et ne pose aucun cookie.

### À faire côté serveur

- Passer `API_DEBUG` à `false` en production (c'est la valeur par défaut).
- Servir le site en **HTTPS** : sans chiffrement, la clé circule en clair.
- Restreindre `API_CORS_ORIGIN` à l'URL du site si vous n'avez pas besoin que
  d'autres sites appellent l'API depuis un navigateur.
