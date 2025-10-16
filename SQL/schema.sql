-- =====================================================
--  Schema Doctolight - MySQL (Version finale adaptée)
-- =====================================================
DROP DATABASE IF EXISTS doctolight;
CREATE DATABASE doctolight
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE doctolight;

-- ========================
-- USERS
-- ========================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    date_naissance DATE NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ========================
-- ROLES
-- ========================
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name ENUM('PATIENT', 'SECRETAIRE', 'MEDECIN', 'ADMIN') NOT NULL UNIQUE
);

-- ========================
-- USER_ROLE (table de jointure)
-- ========================
CREATE TABLE user_roles (
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
);

-- ========================
-- SERVICES
-- ========================
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    duree INT NOT NULL DEFAULT 30, -- durée standard d’un RDV dans ces services
    description TEXT NULL AFTER nom,
    is_active BOOLEAN NOT NULL DEFAULT TRUE
);

-- ========================
-- DISPONIBILITE STAFF
-- ========================
CREATE TABLE disponibilite_staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    jour_semaine ENUM('LUNDI','MARDI','MERCREDI','JEUDI','VENDREDI','SAMEDI','DIMANCHE') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================
-- DISPONIBILITE SERVICE
-- ========================
CREATE TABLE disponibilite_service (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    jour_semaine ENUM('LUNDI','MARDI','MERCREDI','JEUDI','VENDREDI','SAMEDI','DIMANCHE') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- ========================
-- RDV
-- ========================
CREATE TABLE rdv (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    staff_id INT NOT NULL,
    service_id INT NOT NULL,
    dispo_staff_id INT NULL,
    dispo_service_id INT NULL,

    date_rdv DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    duree INT NOT NULL DEFAULT 30,
    statut ENUM('PROGRAMME', 'ANNULE', 'TERMINE') NOT NULL DEFAULT 'PROGRAMME',

    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (dispo_staff_id) REFERENCES disponibilite_staff(id) ON DELETE SET NULL,
    FOREIGN KEY (dispo_service_id) REFERENCES disponibilite_service(id) ON DELETE SET NULL
);

-- ========================
-- NEWS
-- ========================
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    contenu TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
);

-- ========================
-- AUDIT LOG
-- ========================
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    entity_id INT NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT') NOT NULL,
    description TEXT NULL,
    action_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id INT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    CONSTRAINT fk_auditlog_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL      
);

-- ========================
-- INDEXES
-- ========================
CREATE INDEX idx_rdv_date ON rdv(date_rdv);
CREATE INDEX idx_rdv_patient_date ON rdv(patient_id, date_rdv);
CREATE INDEX idx_rdv_staff_date ON rdv(staff_id, date_rdv, heure_debut, heure_fin);
CREATE INDEX idx_auditlog_table ON audit_log(table_name);
CREATE INDEX idx_auditlog_user ON audit_log(user_id);
CREATE INDEX idx_auditlog_actiondate ON audit_log(action_date);

-- =====================================================
-- FIN DU SCHEMA
-- =====================================================
