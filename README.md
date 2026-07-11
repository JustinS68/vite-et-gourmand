# vite et gourmand

Une application web de commande de menu traiteur pour l'entreprise Vite & Gourmand base sur Bordeaux depuis 25 ans.

## Stack technique
-Front: HTML, CSS et PHP
-Back: PHP 8.2 et PDO
-BDD: MySQL (MariaDB)
-Serveur local: XAMPP

## Installation en local
1. cloner le repo https://github.com/JustinS68/vite-et-gourmand.git
2. copier le dossier dans 'C:\xampp\htdocs\' 
3. Creer la base de donnees dans phpMyAdmin:
  -Creer une BDD nommee 'vite-gourmand'
  -Importer le fichier 'vite et gourmand.sql'
  -Importer le fichier 'seeds.sql'
4.Configurer la connexion dans 'config/database.php' :
   -host: localhost
   -dbname: vite-gourmand
   -username: root
   -password: (vide par defaut)
5.Lancer XAMPP (Apache + MySQL)
6.Acceder a : https://localhost/vite-gourmand/

##Identifiants de test
|Administrateur | Dupont_jose@viteetgourmand.fr | Jose33=! |
|Employe | Dupont_Julie@viteetgourmand.fr |
|Utilisateur | Bernard_aquitaine@gmail.com |

##Fonctionnalites
-Consultation des menus
-Inscription et connexion
-Commande des menus
-Espace utilisateur
-Espace employe
-Espace administrateur
-Mentions legales et CGV
-Page contact

