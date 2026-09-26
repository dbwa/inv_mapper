# Environnement de developpement local

Ce dossier permet de faire tourner le site en local avec Docker, sans jamais
toucher a la production.

## Version de PHP

L'environnement Docker tourne sous PHP 8.4 (Apache) et MariaDB 11.8, aligne sur
la cible de migration. La production est encore en PHP 7.4.33.

## Prerequis

Docker et Docker Compose.

## Demarrage

```bash
docker compose up -d --build
```

Le site est ensuite accessible sur <http://localhost:8080>.

La base de donnees est exposee sur le port **3307** (et non 3306, pour ne pas
entrer en conflit avec un MySQL deja installe sur la machine).

## Ce qui est charge dans la base

Au premier demarrage, MariaDB execute les fichiers de `sql/init/` dans l'ordre :

| Fichier | Contenu |
|---|---|
| `01-schema.sql` | Structure des tables et des vues |
| `02-donnees-reference.sql` | Villes et badges |
| `03-donnees-jeu.sql` | Positions des invaders et leur etat |
| `04-fonctions.sql` | Fonctions stockees (badges, distances, flashs) |
| `05-tables-extra.sql` | Tables supplementaires de production (api_players, invit_users, last_maj_invaders, user_info) |

Ces cinq fichiers ne contiennent **aucune donnee personnelle** et sont
versionnes.

## Charger les donnees reelles (optionnel)

Pour travailler avec les vraies donnees de production, un dump complet est
disponible dans `sql/donnees-locales.sql`. Il est **exclu du depot git** car il
contient des donnees personnelles (comptes, jetons de session, cles API).

Le dump brut n'est pas directement chargeable : les vues `liste_etats` et
`ville_centroides` y sont tronquees, les INSERT de `user_badges` et
`user_photos` arrivent avant la table `users` (cle etrangere), et des clauses
de definisseur referencent l'utilisateur de production. Le script
`scripts/import-donnees-reelles.sh` corrige tout cela automatiquement.

```bash
./scripts/import-donnees-reelles.sh
```

Le script nettoie le dump (definisseurs, vues tronquees, ordre des INSERT),
charge le resultat dans la base Docker, puis affiche un resume (utilisateurs,
positions, flashs, photos, badges). Il est **rejouable a volonte** : chaque
lancement repart d'une base propre.

## Identifiants de la base locale

| Parametre | Valeur |
|---|---|
| Hote (depuis le conteneur web) | `db` |
| Hote (depuis la machine) | `127.0.0.1` |
| Port (depuis la machine) | `3307` |
| Base | `invaders` |
| Utilisateur | `invader` |
| Mot de passe | `invader` |

Ces identifiants sont ceux de la base **locale** uniquement. Ils sont sans
rapport avec ceux de la production.

## Configuration du site

Le site lit ses identifiants dans `config.local.php`, qui n'est pas versionne.
Pour le local, il faut le creer a partir du modele :

```bash
cp config.local.php.example config.local.php
```

Puis renseigner :

```php
$host = "db";
$port = 3306;
$dbname = "invaders";
$user = "invader";
$password = "invader";
```

## Commandes utiles

```bash
# Voir les journaux
docker compose logs -f web

# Ouvrir un shell dans le conteneur web
docker compose exec web bash

# Se connecter a la base
docker compose exec db mariadb -u invader -pinvader invaders

# Tout arreter
docker compose down

# Tout arreter et effacer la base (repart de zero au prochain demarrage)
docker compose down -v
```

## Notes

- Le code du site est monte en volume : toute modification d'un fichier PHP est
  prise en compte immediatement, sans reconstruction.
- Une reconstruction (`--build`) n'est necessaire que si `docker/php/Dockerfile`
  ou `docker/php/php.ini` change.
- Les dossiers exclus du depot git (`img_invader/`, `img/user_img/`, `scripts/`,
  `cache/`, `wfs/`, `test_holo/`) sont presents localement et donc utilisables
  par le site, mais ne sont jamais versionnes.
