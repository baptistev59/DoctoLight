-- =====================================================
--  Schema Doctolight - MySQL
-- =====================================================
-- =====================================================
--  Création de la base de données
-- =====================================================
CREATE DATABASE IF NOT EXISTS doctolight
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE doctolight;

-- ========================
-- USERS
-- ========================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    date_naissance DATE NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT  CURRENT_TIMESTAMP
);

-- ========================
-- ROLES
-- ========================
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name ENUM('PATIENT', 'SECRETAIRE', 'MEDECIN', 'ADMIN') NOT NULL UNIQUE
);

-- ========================
-- USER_ROLE (table de jointure)
-- ========================
CREATE TABLE IF NOT EXISTS user_roles (
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
);

-- ========================
-- SERVICES
-- ========================
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL
);

-- ========================
-- DISPONIBILITE STAFF
-- ========================
CREATE TABLE IF NOT EXISTS disponibilite_staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================
-- DISPONIBILITE SERVICE
-- ========================
CREATE TABLE IF NOT EXISTS disponibilite_service (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- ========================
-- RDV
-- ========================
CREATE TABLE IF NOT EXISTS rdv (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    staff_id INT NOT NULL,
    service_id INT NOT NULL,
    dispo_staff_id INT NULL,
    dispo_service_id INT NULL,
    date_rdv TIMESTAMP NOT NULL,
    statut ENUM('PROGRAMME', 'ANNULE', 'TERMINE') NOT NULL DEFAULT 'PROGRAMME',

    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT,
    FOREIGN KEY (dispo_staff_id) REFERENCES disponibilite_staff(id) ON DELETE RESTRICT,
    FOREIGN KEY (dispo_service_id) REFERENCES disponibilite_service(id) ON DELETE RESTRICT,

    CONSTRAINT unique_patient_rdv UNIQUE (patient_id, date_rdv),
    CONSTRAINT unique_staff_rdv   UNIQUE (staff_id, date_rdv)
);

-- ========================
-- NEWS
-- ========================
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    contenu TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
);

-- ========================
-- AUDIT LOG
-- ========================
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    entity_id INT NOT NULL,
    action TEXT NOT NULL,
    action_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id INT NOT NULL,
    ip_address VARCHAR(45) NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
);

-- ========================
-- INDEXES
-- ========================
CREATE INDEX idx_rdv_date ON rdv(date_rdv);
CREATE INDEX idx_rdv_patient_date ON rdv(patient_id, date_rdv);
CREATE INDEX idx_audit_table_date ON audit_log(table_name, action_date);

-- =====================================================
-- FIN DU SCHEMA
-- =====================================================
