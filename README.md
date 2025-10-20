# 🩺 DoctoLight

DoctoLight is a training project developed in PHP 8.2 that simulates a **medical or paramedical office management application**.  
It allows administrators and staff to manage the clinic’s presentation, services, schedules, and appointments, as well as handle news and closures.  
The project follows an **MVC architecture** without any external framework or Composer dependency.

---

## 🌐 Demo

🔗 **Live demo:** [https://baptistev59.alwaysdata.net/doctolight/](https://baptistev59.alwaysdata.net/doctolight/)  
💻 **Repository:** [https://github.com/baptistev59/DoctoLight](https://github.com/baptistev59/DoctoLight)

---

## 👥 Test Accounts

| Role          | Email              | Password |
| ------------- | ------------------ | -------- |
| **Admin**     | admin1@test.fr     | 123      |
| **Secretary** | secretaire@test.fr | 123      |
| **Doctor**    | medecin1@test.fr   | 123      |
| **Patient**   | patient1@test.fr   | 123      |

---

## 🧱 Architecture

The project follows a **custom MVC pattern**:

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

1. **Clone the repository**

   ```bash
   git clone https://github.com/baptistev59/DoctoLight.git
   ```

2. **Import the database**

   - Create a new database (MySQL/MariaDB)
   - Import the file: `SQL/schema.sql`
   - Optionally import demo data: `SQL/seed.sql`

3. **Configure the connection**

   - Edit `/Config/config.php` and set your database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'doctolight');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

4. **Run locally**
   - Place the project in your local server directory (XAMPP/WAMP)
   - Access it via: `http://localhost/doctolight/Public/index.php`

---

## 🧩 Main Features

### 👨‍⚕️ Role-Based System

- 4 roles: **Admin**, **Secretary**, **Doctor**, **Patient**
- Each role has its own restricted access and menus

### 📅 Appointment Management

- Creation, modification, and cancellation of appointments
- Display of schedules by staff or by service
- Integration with closure days

### 🧑‍💼 Service & Staff Management

- Add/edit medical services with related staff
- Define staff or service availability
- Automatic scheduling logic

### 📰 News Management

- CRUD interface for managing clinic news with images

### 🔒 Authentication & Security

- Secure login system using PHP sessions
- CSRF token verification on sensitive operations
- Session regeneration to prevent fixation attacks
- Input sanitization and prepared PDO queries to avoid SQL injection

### 🧾 Audit Log

- Every action (create, edit, delete) is stored in an `audit_log` table with:
  - User ID
  - Action type
  - IP address
  - Timestamp

---

## 🧠 Technical Highlights

- **Language:** PHP 8.2
- **Database:** MySQL / MariaDB
- **Frontend:** Bootstrap 5 + SCSS (custom build with `build-sass.bat`)
- **Security:** CSRF, sessions, prepared PDO statements
- **Architecture:** Pure MVC (no framework)
- **Testing:** Early PHPUnit setup (non-finalized)
- **UML:** Class, Use Case, and Physical Data Model diagrams included

---

## 🧑‍💻 Author

**Baptiste VANDAELE**  
📧 [contact@alkhabir-wa.com](mailto:contact@alkhabir-wa.com)  
🌐 [https://alkhabir-wa.com](https://alkhabir-wa.com)

---

## 🪪 License

This project is released under the **MIT License**.  
You are free to use, modify, and distribute it with attribution.
