CREATE DATABASE IF NOT EXISTS vite_gourmand;
USE vite_gourmand;

CREATE TABLE UTILISATEUR (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR (150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    adresse VARCHAR(255),
    role ENUM('utilisateur','employe','administrateur') DEFAULT 'utilisateur',
    actif BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE MENU (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(150) NOT NULL,
    description TEXT,
    theme ENUM('noel','paques','vegetarien','evenement','romantique') NOT NULL,
    regime ENUM('vegetarien','vegan','classique') DEFAULT 'classique',
    nb_personnes_min INT NOT NULL,
    prix_base DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE PLAT (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(150) NOT NULL,
    type ENUM('entree','plat','dessert') NOT NULL,
    description TEXT
);

CREATE TABLE ALLERGENE (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL
);

CREATE TABLE IMAGE_MENU (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_menu INT NOT NULL,
    url VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_menu) REFERENCES MENU (id) ON DELETE CASCADE
);

CREATE TABLE MENU_PLAT (
    id_menu INT NOT NULL,
    id_plat INT NOT NULL,
    PRIMARY KEY (id_menu, id_plat),
    FOREIGN KEY (id_menu) REFERENCES MENU (id) ON DELETE CASCADE,
    FOREIGN KEY (id_plat) REFERENCES PLAT(id) ON DELETE CASCADE
);

CREATE TABLE PLAT_ALLERGENE (
    id_plat INT NOT NULL,
    id_allergene INT NOT NULL,
    PRIMARY KEY (id_plat, id_allergene),
    FOREIGN KEY (id_plat) REFERENCES PLAT(id) ON DELETE CASCADE,
    FOREIGN KEY (id_allergene) REFERENCES ALLERGENE(id) ON DELETE CASCADE
);

CREATE TABLE COMMANDE (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_utilisateur INT NOT NULL,
    id_menu INT NOT NULL,
    nb_personnes INT NOT NULL,
    date_prestation DATETIME NOT NULL, 
    heure_prestation TIME NOT NULL,
    adresse_prestation VARCHAR(255) NOT NULL,
    ville_prestation VARCHAR(100) NOT NULL, 
    statut ENUM('en attente','accepte','en preparation','en cours de livraison','livre','en attente retour materiel','terminee','annulee') DEFAULT 'en attente',
    prix_total DECIMAL(10,2),
    prix_livraison DECIMAL(10,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id),
    FOREIGN KEY (id_menu) REFERENCES MENU(id)
);

CREATE TABLE SUIVI_COMMANDE (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_commande INT NOT NULL,
    statut VARCHAR(100) NOT NULL,
    date_heure DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_employe INT,
    FOREIGN KEY (id_commande) REFERENCES COMMANDE(id) ON DELETE CASCADE,
    FOREIGN KEY (id_employe) REFERENCES UTILISATEUR(id)
);
ALTER TABLE SUIVI_COMMANDE 
    ADD COLUMN motif_annulation TEXT,
    ADD COLUMN mode_contact VARCHAR(50);



CREATE TABLE AVIS (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_commande INT NOT NULL,
    id_utilisateur INT NOT NULL,
    note INT CHECK (note BETWEEN 1 AND 5),
    commentaire TEXT,
    valide BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_commande) REFERENCES COMMANDE (id),
    FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
);

CREATE TABLE CONTACT (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(150) NOT NULL,
    titre VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);