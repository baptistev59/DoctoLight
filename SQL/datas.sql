USE doctolight;

-- ========================
-- VIDER LES TABLES (ordre important à cause des FK)
-- ========================
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE audit_log;
TRUNCATE TABLE news;
TRUNCATE TABLE rdv;
TRUNCATE TABLE disponibilite_staff;
TRUNCATE TABLE disponibilite_service;
TRUNCATE TABLE user_roles;
TRUNCATE TABLE roles;
TRUNCATE TABLE services;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- ========================
-- ROLES
-- ========================
INSERT INTO roles (id, name) VALUES
(1, 'PATIENT'),
(2, 'SECRETAIRE'),
(3, 'MEDECIN'),
(4, 'ADMIN');

-- ========================
-- USERS
-- ========================
-- Toi en tant qu’admin
INSERT INTO users (id, nom, prenom, email, password, date_naissance, is_active)
VALUES (1, 'VANDAELE', 'Baptiste', 'contact@alkhabir-wa.com', 'adminpass', '1985-01-01', TRUE);

-- Un médecin
INSERT INTO users (id, nom, prenom, email, password, date_naissance, is_active)
VALUES (2, 'Dupont', 'Jean', 'jean.dupont@docto.com', 'medecinpass', '1975-05-15', TRUE);

-- Une secrétaire
INSERT INTO users (id, nom, prenom, email, password, date_naissance, is_active)
VALUES (3, 'Martin', 'Claire', 'claire.martin@docto.com', 'secretpass', '1990-09-20', TRUE);

-- Un patient
INSERT INTO users (id, nom, prenom, email, password, date_naissance, is_active)
VALUES (4, 'Durand', 'Paul', 'paul.durand@docto.com', 'patientpass', '2000-12-10', TRUE);

-- ========================
-- ASSIGNATION ROLES
-- ========================
INSERT INTO user_roles (user_id, role_id) VALUES
(1, 4), -- Admin
(2, 3), -- Médecin
(3, 2), -- Secrétaire
(4, 1); -- Patient

-- ========================
-- SERVICES
-- ========================
INSERT INTO services (id, nom) VALUES
(1, 'Blanchiment'),
(2, 'Consultation Générale');

-- ========================
-- DISPONIBILITES STAFF (Médecin Dupont)
-- ========================
INSERT INTO disponibilite_staff (id, user_id, jour_semaine, start_time, end_time) VALUES
(1, 2, 'LUNDI', '09:00:00', '12:00:00'),
(2, 2, 'LUNDI', '14:00:00', '18:00:00'),
(3, 2, 'MERCREDI', '09:00:00', '12:00:00'),
(4, 2, 'VENDREDI', '14:00:00', '18:00:00');

-- ========================
-- DISPONIBILITES SERVICE (Blanchiment & Consultation Générale)
-- ========================
INSERT INTO disponibilite_service (id, service_id, jour_semaine, start_time, end_time) VALUES
(1, 1, 'MARDI', '09:00:00', '12:00:00'),
(2, 1, 'JEUDI', '14:00:00', '18:00:00'),
(3, 2, 'LUNDI', '09:00:00', '12:00:00'),
(4, 2, 'VENDREDI', '14:00:00', '18:00:00');

-- ========================
-- RDV DE TEST
-- ========================

-- Paul Durand (patient) prend rendez-vous avec Dr Dupont (médecin) pour un blanchiment
INSERT INTO rdv (id, patient_id, staff_id, service_id, dispo_staff_id, dispo_service_id, date_rdv, statut)
VALUES (1, 4, 2, 1, 1, 1, '2025-09-29 09:00:00', 'PROGRAMME');

-- Paul Durand (patient) prend rendez-vous avec Dr Dupont pour une consultation générale
INSERT INTO rdv (id, patient_id, staff_id, service_id, dispo_staff_id, dispo_service_id, date_rdv, statut)
VALUES (2, 4, 2, 2, 3, 3, '2025-09-30 09:00:00', 'PROGRAMME');
