# Invader Mapper — notes de travail

## Contexte

Site web de suivi d'avancement personnel dans le jeu **Flash Invaders** : carte des
invaders, gestion multi-utilisateurs, badges, photos, API publique.

Le développement a été fait **directement sur le serveur de production** pendant
plusieurs années, sans suivi git. Le dépôt GitHub (`github.com/dbwa/inv_mapper`)
était resté bloqué à septembre 2021. Ce travail consiste à resynchroniser le
dépôt avec la production, sans donner l'impression d'un unique commit massif
après 5 ans d'inactivité.

## Règle absolue

**NE JAMAIS MODIFIER LE SITE DE PRODUCTION.** Des utilisateurs réels sont dessus.
Toute intervention sur le serveur doit être validée explicitement par
l'utilisateur au préalable. Les lectures (ssh, mysqldump, md5sum) sont en
revanche sans risque.

## Accès

| Élément | Valeur |
|---|---|
| Dépôt local | `/mnt/data_ssd/invader-mapper/inv_mapper` |
| Serveur de prod | `ssh invader-mapper` |
| Racine web prod | `/home/u949917161/public_html` |
| Remote git | `https://github.com/dbwa/inv_mapper.git` (actuellement retiré) |
| Auteur des commits | `Robin <robin_voitot@hotmail.fr>` |

La prod tourne sous **PHP 7.4.33** et **MariaDB 11.8.9**.

## Ce qui a été fait

### 1. Branche `dev` avec historique reconstitué

- `.gitignore` commité sur `main` au **28/03/2026**
- Branche `dev` créée depuis `main`
- **51 commits** étalés du **28/03/2026 au 26/09/2026**, tous sur des **samedis
  ou dimanches** (l'utilisateur travaille le week-end, les dates de modification
  réelles du serveur tombaient déjà majoritairement sur ces jours)
- Messages courts et volontairement vagues : `mise a jour de index.php`,
  `mise a jour de la carte`, `mise a jour des badges`…
- Les commits ont été générés par script avec `GIT_AUTHOR_DATE` /
  `GIT_COMMITTER_DATE` pour dater dans le passé

### 2. Secrets retirés du code

`config.php` contenait le **mot de passe réel de la base de production** en clair,
ainsi que le nom de la base et l'utilisateur. Il a été remplacé par :

- `config.php` : valeurs par défaut à remplir (`nom_de_la_base`, `utilisateur`,
  `mot_de_passe`) + chargement automatique de `config.local.php` s'il existe
- `config.local.php.example` : modèle versionné
- `config.local.php` : identifiants réels, **exclu du git**

L'historique a été réécrit avec `git-filter-repo` pour purger le mot de passe de
**tous** les commits. Vérifié : plus aucune occurrence du mot de passe de
production ni du nom de la base dans `git rev-list --all`.

Aucune clé API tierce n'a été trouvée. Les clés API du site sont hachées en
SHA-256 dans `api_bootstrap.php`.

### 3. Fichiers de production récupérés

Les 4,2 Go de fichiers exclus du git ont été téléchargés en local (via `tar` sur
ssh, `rsync` n'étant pas disponible dans le sandbox) :

| Dossier | Taille | Fichiers |
|---|---|---|
| `img_invader/photos` | 3,2 Go | 14 075 |
| `img/user_img` | 659 Mo | 1 316 |
| `img_invader/images` | 146 Mo | 7 554 |
| `img_invader/grosplan` | 127 Mo | 13 158 |
| `scripts` | 2,5 Mo | 14 |
| `iss/lib` | 188 Ko | 17 |
| `iss/cache`, `wfs`, `test_holo` | — | 4 |

Intégrité vérifiée par md5 sur 30 fichiers tirés au hasard : tous identiques.

### 4. `.gitignore`

Exclut : `scripts/`, `*.py`, `*.sh`, `img_invader/*` (sauf `nav/`),
`img/user_img/`, `wfs/`, `test_holo/`, `cache/`, `iss/cache/`, `map/cache/`,
`Thumbs.db`, `__pycache__/`, `*.pyc`, `*.log`, `.well-known/`,
`config.local.php`, `sql/donnees-locales.sql`.

Exception volontaire : `img_invader/nav/` (2 icônes d'interface) et `img/`
(logo + icônes de marqueurs) sont versionnés.

### 5. Dossier `iss/` versionné

`iss/` est dans git **sauf `iss/cache/`** (fichier généré par cron).
`iss/lib/` contient la bibliothèque de calcul orbital `Predict` (GPL2), utilisée
par `iss_cache_builder.php`.

Ont été supprimés car inutilisés : `iss/lib/xhprof_lib/` (profiler Facebook,
jamais référencé), `iss/lib/README.md` (doc tierce), `iss/lib/Predict/QTH.php`
(classe jamais requise). Purgés de l'historique avec `git-filter-repo`.

Preuve que ça ne casse rien : le calcul orbital complet a été exécuté sur le
serveur dans un dossier temporaire, sans ces fichiers, et a produit une position
ISS valide.

### 6. Base de données

Quatre dumps générés depuis la prod (10,3 Mo, 22 tables) :

**Versionnés** (aucune donnée personnelle) :
- `sql/init/01-schema.sql` — 16 tables + 2 vues
- `sql/init/02-donnees-reference.sql` — 88 villes, 23 badges
- `sql/init/03-donnees-jeu.sql` — 4 334 positions, 4 341 états

**Exclu du git** : `sql/donnees-locales.sql` (4,1 Mo) — dump complet avec les
36 comptes, 402 jetons de session, 4 clés API, 1 926 connexions, 1 281 photos.

**4 tables volontairement absentes du schéma versionné** :
- `api_players` — utilisée uniquement par `scripts/api_players.py`
- `invit_users` — code mort (branche inatteignable, `$code_inscription` vaut `''`)
- `last_maj_invaders` — table morte, jamais référencée
- `user_info` — utilisée uniquement par `scripts/user_info_fetch.py`

L'ancien dump PostgreSQL (`postgresql/`) a été supprimé : il contenait des
données de test (`test`, `test2`).

### 7. Environnement Docker

- `docker-compose.yml` — services `web` (PHP 7.4 + Apache, port 8080) et `db`
  (MariaDB 11.8, port 3307)
- `docker/php/Dockerfile` — extensions alignées sur la prod (`pdo_mysql`,
  `mysqli`, `gd` avec jpeg/png/webp, `exif`, `zip`, `curl`, `mbstring`),
  `mod_rewrite`, locale `fr_FR`
- `docker/php/php.ini` — mêmes limites que la prod, `display_errors` activé
- `docker/README.md` — procédure complète

## Ce qui reste à faire

### Priorité 1 — Tester l'environnement Docker

**La configuration Docker est écrite mais JAMAIS TESTÉE.** L'agent tourne dans un
sandbox Flatpak (VS Code) qui n'a pas accès au démon Docker de la machine — le
socket `/var/run/docker.sock` n'est pas exposé.

À faire depuis un terminal **hors VS Code** :

```bash
cd /mnt/data_ssd/invader-mapper/inv_mapper
docker compose up -d --build
```

Puis ouvrir `http://localhost:8080`. En cas d'erreur, `docker compose logs web`.

Points à vérifier une fois lancé :
- La page d'accueil s'affiche sans erreur PHP
- La connexion à la base fonctionne (le site lit `config.local.php`)
- La carte s'affiche (elle dépend de `map/map_emission_ajax.php`)
- Les images se chargent (`img_invader/` est bien monté en volume)

### Priorité 2 — Remettre le remote et pousser

Le remote a été retiré par `git-filter-repo` (comportement normal après
réécriture d'historique) :

```bash
git remote add origin https://github.com/dbwa/inv_mapper.git
```

Décider ensuite quoi pousser : `main` (contient le commit du `.gitignore`) et
`dev` (les 51 commits). **Vérifier une dernière fois qu'aucun secret ne part**
avant de pousser.

### Priorité 3 — Changer le mot de passe de la base

Le mot de passe de production a circulé dans 7 fichiers et sur un serveur web.
Il est toujours actif. À changer côté hébergeur, puis mettre à jour
`config.local.php` sur le serveur.

### Priorité 4 — Restaurer la production (décision en attente)

**La production tourne actuellement avec le `config.php` + `config.local.php`
déployés par l'agent AVANT que l'utilisateur n'interdise les modifications.**
Le site fonctionne (testé : 36 utilisateurs lus en base, toutes les pages en
HTTP 200), mais l'utilisateur n'avait pas donné son accord.

Sauvegarde disponible : `/home/u949917161/config.php.bak-avant-migration`

L'utilisateur n'a pas encore tranché : garder l'état actuel ou restaurer la
sauvegarde. **Ne rien faire sans son accord explicite.**

## Pièges connus

- **`git reset --hard` supprime les fichiers non suivis.** Les 4,2 Go de fichiers
  ignorés ne sont pas dans git : un `reset --hard` les efface du disque. Il faut
  alors les re-télécharger depuis la prod.
- **`rsync` n'existe pas dans le sandbox.** Utiliser `tar` via ssh :
  `ssh invader-mapper "cd /home/u949917161/public_html && tar czf - ..." | tar xzf -`
- **`php` n'existe pas dans le sandbox.** Pour tester du PHP, passer par le
  serveur de prod dans un dossier temporaire (`/tmp`), jamais dans `public_html`.
- **`git-filter-repo`** est installé dans `/var/data/python/bin/`, pas dans le
  PATH.
- **Le `.gitignore` utilise `img_invader/*` + `!img_invader/nav/`** (et non
  `img_invader/`), sinon git ne descend pas dans le dossier et l'exception ne
  fonctionne pas.
- **`git-filter-repo` supprime les commits devenus vides.** C'est normal et
  souhaitable.
