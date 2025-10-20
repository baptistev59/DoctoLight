# 🩺 DoctoLight

DoctoLight est un projet de formation développé en **PHP 8.2**, simulant une **application de gestion de cabinet médical ou paramédical**.  
Il permet de gérer la présentation du cabinet, les services, les membres de l’équipe soignante, les rendez-vous, les fermetures et les actualités.  
Le projet suit une **architecture MVC** maison, sans framework ni dépendance Composer.

---

## 🌐 Démo

🔗 **Démo en ligne :** [https://baptistev59.alwaysdata.net/doctolight/](https://baptistev59.alwaysdata.net/doctolight/)  
💻 **Dépôt GitHub :** [https://github.com/baptistev59/DoctoLight](https://github.com/baptistev59/DoctoLight)

---

## 👥 Comptes de test

| Role          | Email              | Password |
| ------------- | ------------------ | -------- |
| **Admin**     | admin1@test.fr     | 123      |
| **Secretary** | secretaire@test.fr | 123      |
| **Doctor**    | medecin1@test.fr   | 123      |
| **Patient**   | patient1@test.fr   | 123      |

---

## 🧱 Architecture du projet

Le projet suit une **structure MVC personnalisée** :

```
App/
 ├── Controllers/
 ├── Models/
 ├── Views/
 ├── Enums/
 ├── Autoload.php
Config/
 ├── Database.php
 ├── config.php
Public/
 ├── index.php
 ├── css/
 ├── scss/
 ├── images/
 ├── uploads/
SQL/
 ├── schema.sql
 ├── seed.sql
tests/
 ├── unit/
UML/
 ├── class.puml
 ├── MPD.puml
 ├── usercase.puml
```

---

## ⚙️ Installation

1. **Cloner le dépôt**

   ```bash
   git clone https://github.com/baptistev59/DoctoLight.git
   ```

2. **Importer la base de données**

   - Créer une base MySQL/MariaDB
   - Importer `SQL/schema.sql`
   - Optionnel : importer les données de test via `SQL/seed.sql`

3. **Configurer la connexion**
   Modifier le fichier `/Config/config.php` :

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'doctolight');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. **Lancer localement**
   - Placer le projet dans le dossier de votre serveur (XAMPP/WAMP)
   - Accéder à `http://localhost/doctolight/Public/index.php`

---

## 🧩 Fonctionnalités principales

### 👨‍⚕️ Gestion des rôles

4 rôles : **Admin**, **Secrétaire**, **Médecin**, **Patient**  
Chaque rôle possède ses propres droits et menus dédiés.

### 📅 Gestion des rendez-vous

- Création / modification / suppression
- Affichage des plannings par service ou praticien
- Intégration des jours de fermeture

### 🧑‍💼 Gestion des services et de l’équipe

- CRUD complet pour les services
- Disponibilités des services et du personnel
- Gestion des horaires du cabinet

### 📰 Gestion des actualités

Interface d’administration pour publier et modifier les actualités du cabinet avec images.

### 🔒 Sécurité

- Authentification par sessions PHP
- Vérification CSRF sur les formulaires sensibles
- Re-génération de session
- Requêtes PDO préparées (prévention injections SQL)

### 🧾 Journalisation (Audit Log)

Toutes les actions (ajout, modification, suppression) sont enregistrées dans la table `audit_log` avec :

- Identifiant utilisateur
- Type d’action
- IP de l’utilisateur
- Date et heure

---

## 🧠 Points techniques

- **Langage :** PHP 8.2
- **Base de données :** MySQL / MariaDB
- **Frontend :** Bootstrap 5 + SCSS compilé via `build-sass.bat`
- **Sécurité :** CSRF, sessions, PDO préparé
- **Architecture :** MVC sans framework
- **Tests :** PHPUnit (structure en place)
- **UML :** Diagrammes de classes, cas d’utilisation et MPD inclus

---

## 🧑‍💻 Auteur

**Baptiste VANDAELE**  
📧 [contact@alkhabir-wa.com](mailto:contact@alkhabir-wa.com)  
🌐 [https://alkhabir-wa.com](https://alkhabir-wa.com)

---

## 🪪 Licence

Projet distribué sous licence **MIT**.  
Libre de réutilisation, modification et distribution avec attribution.
