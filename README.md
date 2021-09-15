# Invader mapper

Invader-mapper est un outil multi-utilisateur de gestion et de cartographie des invaders pour le jeu [FlashInvaders](https://play.google.com/store/apps/details?id=com.ltu.flashInvader&hl=fr). 

C'est une application web, donc accessible depuis un ordinateur ou un smartphone.

*NB : Cet outil ne fourni les positions, ni la disponibilité des mosaïques.*

![Exemple de vue de l'outil](https://github.com/dbwa/inv_mapper/blob/main/img/extrait_vue.png)

# Installation

L'application à deux composantes : le système de fichier, géré par un serveur PHP, et une base de données PostgreSQL.

## Base de données

Installez un serveur **PostgreSQL**, sur lequel on executera le fichier `/postresql/dump[...].sql`. Des requetes utilitaires sont diponible dans le sous dossier `/utils` afin de pouvoir inviter un nouvel utilisateur.

## Fichiers

Téléchargez tout l'arche puis servez `index.php` depuis Apache ou Nginx.
Modifiez le fichier config.php afin de le lier à la base de données, puis ajoutez votre token pour le fond de carte.

# Préparation des données

Les données de sont pas fournies avec l'outil. Il revient à l'utilisateur de trouver les positions des invaders, de remplir les bases, de mettre à jour la distinction des invaders en bon état ou détruit, etc.

## Ajouter des utilisateurs

L'ajout d'utilisateurs se fait uniquement en bases. Utilisez la fonction d'invitation pour creer l'utilisateur, et le lien donné permettra de lui associer un mot de passe.

## Ajouter les invaders déjà fashés par un utilisateur.

Cette opération peut se faire directement en bases de données, ou via la page stat de l'application.
