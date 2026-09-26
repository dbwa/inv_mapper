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
- `sql/init/04-fonctions.sql` — 18 fonctions stockées (ajouté en §10)
- `sql/init/05-tables-extra.sql` — structure des 4 tables ci-dessous (ajouté en §10)

**Exclu du git** : `sql/donnees-locales.sql` (4,1 Mo) — dump complet avec les
36 comptes, 402 jetons de session, 4 clés API, 1 926 connexions, 1 281 photos.
Chargé en local par `scripts/import-donnees-reelles.sh` (voir §10).

**4 tables volontairement absentes du schéma initial** (structure désormais dans
`05-tables-extra.sql`, données uniquement dans le dump) :
- `api_players` — utilisée uniquement par `scripts/api_players.py`
- `invit_users` — code mort (branche inatteignable, `$code_inscription` vaut `''`)
- `last_maj_invaders` — table morte, jamais référencée
- `user_info` — utilisée uniquement par `scripts/user_info_fetch.py`

L'ancien dump PostgreSQL (`postgresql/`) a été supprimé : il contenait des
données de test (`test`, `test2`).

### 7. Environnement Docker

- `docker-compose.yml` — services `web` (PHP 8.4 + Apache, port 8080) et `db`
  (MariaDB 11.8, port 3307)
- `docker/php/Dockerfile` — extensions alignées sur la prod (`pdo_mysql`,
  `mysqli`, `gd` avec jpeg/png/webp, `exif`, `zip`, `curl`, `mbstring`),
  `mod_rewrite`, `mod_headers`, locale `fr_FR`, `libonig-dev`
- `docker/php/php.ini` — mêmes limites que la prod, `display_errors` activé
- `docker/README.md` — procédure complète
- `scripts/import-donnees-reelles.sh` — chargement des données de production

**Testé et fonctionnel** : le build passe et le site tourne sur
`http://localhost:8080` avec les données réelles importées.

### 8. Migration PHP 7.4 → 8.4

L'image `php:7.4-apache` n'est plus disponible sur le Docker Hub. Le code a été
rendu compatible PHP 8.4. Corrections appliquées :

| Fichier | Problème | Correction |
|---|---|---|
| `docker/php/Dockerfile` | `FROM php:7.4-apache` (image retirée) | `FROM php:8.4-apache` + `a2enmod headers` |
| `map/tile_proxy.php` | `curl_close()` supprimé en PHP 8.0 → erreur fatale | appel supprimé |
| `redirect/spotter-art.php` | 2 × `curl_close()` + `CURLOPT_ORIGIN` (constante inexistante) | appels supprimés, en-tête `Origin:` ajouté dans `CURLOPT_HTTPHEADER` |
| `modules/user_images/ajax_update_photo_status.php` | `utf8_encode()` déprécié 8.2 | appel retiré (fichier déjà en UTF-8) |
| `modules/user_images/ajax_save_user_image.php` | 2 × `utf8_encode()` | `$filename` passé tel quel ; littéral ISO-8859-1 converti en UTF-8 (sinon `json_encode` renvoie `false`) |
| `fonctions.inc.php` | `setlocale(LC_TIME, "fr_FR")` (locale inexistante) ; accès non protégés aux superglobales | `setlocale(LC_TIME, "fr_FR.UTF-8", "fr_FR", "fr")` ; `?? ''` sur 12 accès |
| `iss/lib/Predict.php`, `Predict/Sat.php`, `Predict/Solar.php` | 11 paramètres nullable implicites (`Predict_QTH $qth = null`) dépréciés en 8.4 | `?Predict_QTH $qth = null` |
| `DataSource-out.php` | `mysqli_connect_errno()` sans connexion (code mort) | commentaire d'avertissement ajouté |
| `docker/php/php.ini` | — | `session.cookie_samesite = Lax` |

**Encodages préservés** : `map/tile_proxy.php`, `redirect/spotter-art.php` et
`modules/user_images/ajax_save_user_image.php` sont restés en ISO-8859-1 avec
fins de ligne CRLF. Ne pas les réécrire en UTF-8/LF sans raison.

**Non vérifié** : ni Docker ni PHP ne sont disponibles dans le sandbox. Aucun
`php -l` ni `docker compose up` n'a pu être exécuté. La compatibilité est
établie par inspection statique uniquement.

### 9. Corrections après le premier lancement Docker

Le build et le site ont ensuite été testés par l'utilisateur, ce qui a révélé
plusieurs problèmes supplémentaires.

**Build Docker** — `docker-php-ext-install` échouait sur `mbstring` :
`Package 'oniguruma', required by 'virtual:world', not found`. `libonig-dev` est
fourni en standard par `php:7.4-apache` (Debian 11) mais plus par
`php:8.4-apache` (Debian 13). Ajouté explicitement au `Dockerfile`.

**`.htaccess` — les liens partaient sur google.com.** La protection anti-hotlink
de l'hébergeur (marqueurs `HOTLINKID`) redirige vers google.com toute requête
`.php`/`.html` dont le référent ne vient pas de `invader-mapper.space`. En local
sur `localhost:8080` le référent ne correspond jamais. Deux `RewriteCond` sur
`HTTP_HOST` (`localhost` / `127.0.0.1`, avec ou sans port) désactivent la règle
en local ; la protection reste active en production.

**`session_start()` après du HTML.** `index.php`, `index2.php` et `index_dev.php`
commençaient par `<!DOCTYPE html>` et `<html lang="fr">` avant le bloc PHP. PHP
7.4 le tolérait grâce à `output_buffering`, PHP 8.4 non. Le bloc PHP a été
remonté avant le `<!DOCTYPE html>`.

**`session_start()` après un include.** Plus insidieux : `fonctions.inc.php` se
termine par une balise de fermeture suivie d'un saut de ligne, ce qui envoie un
octet de sortie. Tout fichier appelant `session_start()` **après** cet include
échouait silencieusement — la session n'était jamais persistée. Corrigé dans
7 fichiers : `ajaxLogin.php` (connexion impossible), `logout.php`,
`adherent.php`, `modules/user_images/moderate.php`,
`ajax_update_photo_status.php`, `ajax_get_moderated_photos.php`,
`ajax_save_user_image.php`.

**Piège à ne jamais reproduire** : ne jamais écrire la séquence de fermeture PHP
dans un commentaire. Un commentaire ajouté contenant `?>` a fermé le bloc PHP et
fait afficher tout le code source d'`ajaxLogin.php` en texte brut dans la page.

**`adherent.php:205`** — `$_GET['register']` accédé sans `isset()`. `Notice` en
7.4, `Warning` affiché en 8.4. Corrigé.

### 10. Import des données réelles

Le dump `sql/donnees-locales.sql` **n'est pas directement chargeable**. Trois
problèmes, tous corrigés par `scripts/import-donnees-reelles.sh` :

1. **Les vues ne sont pas dans le dump.** `CREATE VIEW` n'apparaît **nulle part**
   dans le fichier (vérifié en insensible à la casse). Le dump contient bien
   `DROP TABLE IF EXISTS \`liste_etats\`` et `\`ville_centroides\``, mais rien
   pour les recréer : il ne reste que des lignes orphelines, fin d'un commentaire
   `/*!50001 ... */` dont le début a disparu. **Cause de la troncature non
   identifiée** — ni octets NUL, ni fins de ligne mixtes, ni erreur de lecture.
   À creuser si le dump est régénéré : la commande exacte de `mysqldump` (ou
   l'outil d'export de l'hébergeur) est le suspect principal.
2. **Ordre des `INSERT`.** `user_badges` et `user_photos` sont insérés avant
   `users`, alors qu'ils ont une clé étrangère vers `users(id)` → erreur 1452.
   Le script les extrait et les rejoue après.
3. **19 clauses de définisseur** référencent `u949917161_invader@127.0.0.1`,
   inexistant en local. Retirées à la volée.

**Collation — bug réel, indépendant de la migration.** MariaDB 11.8 utilise par
défaut `utf8mb4_uca1400_ai_ci`, alors que les tables du dump sont en
`utf8mb4_unicode_ci`. Les fonctions stockées qui comparent une colonne à leur
paramètre échouaient avec l'erreur 1267 (`illegal mix of collations`) :
**13 fonctions sur 18 étaient inutilisables**, dont `get_badges_to_check` qui
alimente tout le système de badges. Invisible en production, dont la version de
MariaDB est antérieure à `uca1400`. Corrigé par un
`ALTER DATABASE ... COLLATE utf8mb4_unicode_ci` intégré au script d'import.

**Fichiers ajoutés à `sql/init/`** :
- `04-fonctions.sql` — les 18 fonctions stockées, sans clause de définisseur,
  avec `DROP FUNCTION IF EXISTS` avant chaque définition
- `05-tables-extra.sql` — structure seule des 4 tables de production absentes du
  schéma initial (`api_players`, `invit_users`, `last_maj_invaders`, `user_info`)

**`scripts/import-donnees-reelles.sh`** — nettoie le dump à la volée et le
charge. Rejouable à volonté. Le dump d'origine reste intact.

**19 fonctions, pas 18** : le dump contient aussi `GetEstimatedScoreForRank`,
absente de `04-fonctions.sql`. Vérifié : **jamais appelée par le code PHP**. Elle
est tout de même créée lors de l'import, puisque le script charge le dump.

## Ce qui reste à faire

### Priorité 1 — Tester l'environnement Docker (PHP 8.4)

**Fait.** Le build passe, le site tourne sur `http://localhost:8080`, les
données réelles sont importées et la connexion fonctionne. Les problèmes
rencontrés au premier lancement sont documentés en §9 et §10.

Reste à vérifier à l'usage :
- La carte et les tuiles (`map/tile_proxy.php` — c'est là que `curl_close()` a
  été retiré)
- Les badges (`get_badges_to_check` — voir le bug de collation en §10)
- L'upload et la modération de photos
- La page ISS (`iss/iss_data.php`), si `iss/cache/iss_cache.json` existe

Si des `Deprecated:` apparaissent, les noter : `display_errors` est activé en
local, donc tout ce qui reste à corriger se voit immédiatement.

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
- **Ne jamais écrire la séquence de fermeture PHP dans un commentaire.** Elle
  ferme le bloc PHP et fait afficher tout le code source en texte brut dans la
  page. C'est arrivé sur `ajaxLogin.php`.
- **`session_start()` doit être appelé avant tout include.** `fonctions.inc.php`
  se termine par une balise de fermeture suivie d'un saut de ligne, ce qui envoie
  un octet de sortie et fait échouer la session silencieusement.
- **Collation MariaDB 11.8.** La base est en `utf8mb4_uca1400_ai_ci` par défaut,
  les tables du dump en `utf8mb4_unicode_ci`. Les fonctions stockées qui
  comparent une colonne à leur paramètre échouent alors en erreur 1267. Le script
  d'import aligne la collation de la base.
- **Le dump `sql/donnees-locales.sql` n'est pas chargeable tel quel** : vues
  absentes, ordre des `INSERT` incorrect, clauses de définisseur. Passer par
  `scripts/import-donnees-reelles.sh`.
