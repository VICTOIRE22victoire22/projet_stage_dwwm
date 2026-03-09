# 📡 Application de gestion d'infrastructure et téléphonie

Cette application web permet de gérer différents éléments d’une infrastructure
informatique et téléphonique d’entreprise :

- sites
- bâtiments
- équipements
- lignes téléphoniques
- mobiles
- agents
- fournisseurs
- factures
- numéros SDA

Le projet est développé en **PHP avec une architecture MVC** et fonctionne dans
un environnement **Docker** avec une base de données **MySQL** accessible via **phpMyAdmin**.

---


# 🚀 Lancement du projet

## Prérequis

- Docker
- Docker Compose

---

## Démarrer l'application

Depuis le dossier du projet :

```bash
docker compose up --build


## Accès aux services

Application web :

http://localhost:8080

phpMyAdmin :

http://localhost:8081


🏗️ Architecture du projet

projet_stage/
│
├── classes/                # Classes utilitaires (navbar, CSRF token, tri)
│
├── config/
│   └── db.php              # Connexion à la base de données
│
├── controller/             # Contrôleurs MVC
│   ├── AgentController.php
│   ├── BuildingController.php
│   ├── EquipmentController.php
│   ├── InvoiceController.php
│   ├── MobileController.php
│   ├── PhoneController.php
│   ├── ProviderController.php
│   ├── SiteController.php
│   └── UsersController.php
│
├── authentification.php    # Gestion de l'authentification
│
├── composer.json           # Dépendances PHP
│
├── .env                    # Variables d'environnement
│
└── docker-compose.yml      # Configuration Docker


Le projet suit une architecture MVC :

Controllers → logique métier

Classes → outils réutilisables

Config → connexion base de données

Vues → affichage des données


🔐 Sécurité

Le projet inclut plusieurs mécanismes de sécurité :

gestion de l'authentification utilisateur

protection CSRF Token

séparation logique MVC

gestion des accès aux ressources

🗄️ Base de données

La base de données est accessible via phpMyAdmin :

http://localhost:8081

Les informations de connexion sont définies dans :

.env
config/db.php
⚙️ Technologies utilisées
```markdown
| Technologie | Rôle |
|-------------|------|
| PHP | Backend |
| MySQL | Base de données |
| Docker | Environnement de développement |
| phpMyAdmin | Administration base de données |
| Composer | Gestion des dépendances PHP |


📦 Fonctionnalités principales

L'application permet de gérer :

👤 utilisateurs

🏢 sites

🏬 bâtiments

📞 téléphones

📱 mobiles

🔢 numéros SDA

🧰 équipements

📄 factures

🧑‍💼 agents

🏭 fournisseurs

Chaque entité dispose de contrôleurs dédiés permettant :

consultation

ajout

modification

suppression

🧪 Environnement de développement

Le projet utilise Docker Compose pour simplifier l'installation :

docker compose up --build

Cela lance :

le serveur web PHP

la base de données MySQL

phpMyAdmin

👩‍💻 Auteur

Projet réalisé par Odile

Formation : Développeur Web et Web Mobile

GitHub :
https://github.com/VICTOIRE22victoire22

