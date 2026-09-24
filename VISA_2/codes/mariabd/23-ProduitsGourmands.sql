-- on modifie le scipt SQL pour une seule "bdd" mergant comptes.sqlite et produit.db


CREATE DATABASE IF NOT EXISTS BDD_VISA2;

USE BDD_VISA2;

-- On reprend la config pour la table Producteur
CREATE TABLE Producteur (
    NoProducteur INTEGER PRIMARY KEY AUTO_INCREMENT,
    Nom VARCHAR(40) NOT NULL,
    Prenom VARCHAR(40) NOT NULL,
    Ville VARCHAR(30)
);

-- On reprend la config pour la table Produit
CREATE TABLE Produit (
    NoProduit INTEGER PRIMARY KEY AUTO_INCREMENT,
    Nom VARCHAR(30) NOT NULL,
    NoProducteur INTEGER NOT NULL,
    PrixKilo FLOAT,
    Bio BOOLEAN,
    ImagePath VARCHAR(255),

    CONSTRAINT fk_NoProducteur
        FOREIGN KEY (NoProducteur)
        REFERENCES Producteur (NoProducteur)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- On créé une table Utilisateur
CREATE TABLE Utilisateur (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Pass VARCHAR(255) NOT NULL,
    Statut ENUM('user', 'admin') NOT NULL DEFAULT 'user'
);

-- on renseigne les utilisateurs par défaut ; on ne prévoit pas la fonctionnalité d'ajouter des utilisateurs sur le siteweb. 
INSERT INTO Utilisateur (Email, Pass, Statut)
VALUES
    ('superuser@test.fr', 'L@nnion', 'admin'),
    ('simpleuser@test.fr', 'L@nnion', 'user');