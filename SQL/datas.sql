-- =====================================================
-- Peuplement complet de Doctolight
-- =====================================================

USE doctolight;

-- ========================
-- ROLES
-- ========================
INSERT IGNORE INTO roles (id, name) VALUES
(1,'PATIENT'),
(2,'SECRETAIRE'),
(3,'MEDECIN'),
(4,'ADMIN');

-- ========================
-- USERS
-- ========================
INSERT IGNORE INTO users (nom, prenom, email, password, date_naissance, is_active) VALUES
-- Admin
('Admin','Principal','admin@cabinet.com','hash_admin','1980-01-01',TRUE),
-- Secrétaires
('Durand','Claire','claire.durand@cabinet.com','hash_sec1','1990-05-10',TRUE),
('Martin','Lucie','lucie.martin@cabinet.com','hash_sec2','1992-09-15',TRUE),
-- Dentistes
('Dupont','Paul','paul.durand@cabinet.com','hash_doc1','1975-03-20',TRUE),
('Durant','Hélène','helene.moreau@cabinet.com','hash_doc2','1979-03-18',TRUE),
-- Patients
('Leroy','Jean','jean.leroy@example.com','hash_pat1','1985-06-12',TRUE),
('Dupont','Sophie','sophie.dupont@example.com','hash_pat2','1992-07-21',TRUE),
('Legrand','Isabelle','isabelle.legrand@example.com','hash_pat3','1982-04-12',TRUE),
('Petit','Nicolas','nicolas.petit@example.com','hash_pat4','1995-07-23',TRUE),
('Morel','Laura','laura.morel@example.com','hash_pat5','1988-11-30',TRUE),
('Giraud','Thomas','thomas.giraud@example.com','hash_pat6','1990-02-14',TRUE),
('Faure','Claire','claire.faure@example.com','hash_pat7','1987-08-19',TRUE),
('Benoit','Antoine','antoine.benoit@example.com','hash_pat8','1993-12-01',TRUE),
('Roux','Emma','emma.roux@example.com','hash_pat9','1991-05-05',TRUE),
('Meyer','Lucas','lucas.meyer@example.com','hash_pat10','1986-09-09',TRUE);

-- ========================
-- USER_ROLES
-- ========================
INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(1,4), -- Admin
(2,2),(3,2), -- Secrétaires
(4,3),(5,3), -- Dentistes
(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1); -- Patients

-- ========================
-- SERVICES
-- ========================
INSERT IGNORE INTO services (nom) VALUES
('Consultation générale'),
('Détartrage'),
('Extraction'),
('Orthodontie'),
('Blanchiment'),
('Implantologie'),
('Prothèses'),
('Soins conservateurs');

-- ========================
-- DISPONIBILITE STAFF (Dentistes)
-- ========================
INSERT IGNORE INTO disponibilite_staff (user_id, start_time, end_time) VALUES
-- Paul Durand
(4,'2025-09-22 09:00:00','2025-09-22 12:00:00'),
(4,'2025-09-22 14:00:00','2025-09-22 18:00:00'),
(4,'2025-09-23 09:00:00','2025-09-23 12:00:00'),
-- Hélène Moreau
(5,'2025-09-23 14:00:00','2025-09-23 18:00:00'),
(5,'2025-09-24 09:00:00','2025-09-24 12:00:00'),
(5,'2025-09-24 14:00:00','2025-09-24 18:00:00');

-- ========================
-- DISPONIBILITE SERVICE
-- ========================
INSERT IGNORE INTO disponibilite_service (service_id, start_time, end_time) VALUES
(1,'2025-09-22 09:00:00','2025-09-22 18:00:00'),
(2,'2025-09-22 09:00:00','2025-09-22 18:00:00'),
(3,'2025-09-23 09:00:00','2025-09-23 18:00:00'),
(4,'2025-09-23 09:00:00','2025-09-23 18:00:00'),
(5,'2025-09-24 09:00:00','2025-09-24 18:00:00'),
(6,'2025-09-24 09:00:00','2025-09-24 18:00:00');

-- ========================
-- RDV (Patients -> Dentistes)
-- ========================
INSERT IGNORE INTO rdv (patient_id, staff_id, service_id, dispo_staff_id, dispo_service_id, date_rdv, statut) VALUES
(6,4,1,1,1,'2025-09-22 09:30:00','PROGRAMME'),
(7,4,2,1,2,'2025-09-22 10:00:00','PROGRAMME'),
(8,4,3,3,3,'2025-09-23 09:30:00','PROGRAMME'),
(9,5,4,4,4,'2025-09-23 15:00:00','PROGRAMME'),
(10,5,5,5,5,'2025-09-24 10:00:00','PROGRAMME'),
(11,5,6,5,6,'2025-09-24 11:00:00','PROGRAMME'),
(12,4,1,3,1,'2025-09-23 11:00:00','PROGRAMME'),
(13,4,2,3,2,'2025-09-23 11:30:00','PROGRAMME'),
(14,5,3,5,3,'2025-09-24 14:30:00','PROGRAMME'),
(15,5,5,6,5,'2025-09-24 15:30:00','PROGRAMME');

-- ========================
-- NEWS
-- ========================
INSERT IGNORE INTO news (titre, contenu, created_by) VALUES
('Nouvelles horaires pour consultations','Nous avons étendu nos horaires de consultations pour mieux vous servir.',1),
('Bienvenue au Dr Hélène Durant','Le Dr Durant rejoint notre équipe de dentistes.',1),
('Conseils pour un bon brossage','Découvrez nos astuces pour maintenir vos dents en bonne santé.',2),
('Offre spéciale blanchiment','Profitez d\'une remise de 20% sur le blanchiment jusqu\'à fin septembre.',3);

-- ========================
-- AUDIT LOG
-- ========================
INSERT IGNORE INTO audit_log (table_name, entity_id, action, user_id, ip_address) VALUES
('users',4,'INSERT INTO users (...)',1,'192.168.1.10'),
('rdv',1,'INSERT INTO rdv (...)',2,'192.168.1.11'),
('services',3,'INSERT INTO services (...)',3,'192.168.1.12');
