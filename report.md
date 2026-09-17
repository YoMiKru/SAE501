BUT3 R&T : SAé 5.01

# Développer et déployer un microservice dans un environnement virtualisé
<span style="font-size: 1.6em; color: gray;"><em>Compte-rendu</em></span>

> Faire la partie objectif et problématique pro <br>
> Faire une description détaillé et non générique. <br>
> Expliquer ce qu'on a fait lors du SAE23

## Partie 0 : Configuration de la VM et installation des dépendances nécéssaires

La VM tourne sous Debian 6, la version de l'IUT sous serveur Proxmox privée. 
L'acccès à distance via SSH a été activé et **sécurisé** pour un utilisateur : root. 

Dans la configuration actuelle, la connexion à la VM passe via un **proxy SSH** et connexion via clé SSH (id_ed25519). On se connecte pas directement de l'exterieur vers la VM, ce qui assure la sécurité de cette équipement.

On a configuré un utilisateur supplémentaire avec lequel on fera toute les configurations des micro-services : admni. Il a été ajouté aux groupes SUDOERS et docker. 

Pour l'accès au site, la structure sera la suivante : 
- utilisation du nom de domaine yomikr.fr avec sae501.yomikr.fr
- chaque VISA aura leur propre fichier docker-compose.yaml et donc chaque VISA sra accessible via sae501.yomikr.fr/VISA_0X
- chaque VISA va utiliser le VISA précédent comme base. Chaque VISA est un palies de progression et agit comme backup. 
> Revoir comment on explique ça 

## Partie I : Réutilisation du projet SAE23 et mise au propre

Cette partie correspond à la version 1 : PHP et SQLite avec quelque changement de structure.

Initialement, le projet SAE501 tournait sans virtualisation (docker). Là ça change, dans cette partie la partie Web sera conteneurisé dans docker. Puisque Sqlite fonctionne sous la forme d'un simple fichier, sa conteneurisation n'est pas pertinente. 

L'environnement entre tourner sans conteunerisation et avec contenneuraisation change, les redirections doivent mis a jour pour être adaptée à leur nouveau environnement et la structuration a évolué.  

## I.1.




