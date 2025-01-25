DROP TABLE IF EXISTS utilisateur CASCADE;
DROP TABLE IF EXISTS client CASCADE;
DROP TABLE IF EXISTS formateur CASCADE;
DROP TABLE IF EXISTS admin CASCADE;
DROP TABLE IF EXISTS discussion CASCADE;
DROP TABLE IF EXISTS message CASCADE;
DROP TABLE IF EXISTS public CASCADE;
DROP TABLE IF EXISTS categorie CASCADE;
DROP TABLE IF EXISTS niveau CASCADE;
DROP TABLE IF EXISTS activite CASCADE;
DROP TABLE IF EXISTS moderateur CASCADE;
DROP TABLE IF EXISTS aExpeirencePeda CASCADE;
DROP TABLE IF EXISTS aExpertiseProfessionnelle CASCADE;
DROP TABLE IF EXISTS Affranchis CASCADE;
DROP TABLE IF EXISTS theme CASCADE;

CREATE TABLE utilisateur(
   id_utilisateur SERIAL,
   nom VARCHAR(64) NOT NULL,
   prenom VARCHAR(64) NOT NULL,
   password VARCHAR(256) NOT NULL,
   mail VARCHAR(128) NOT NULL,
   token VARCHAR(256) NOT NULL,
   photo_de_profil VARCHAR(64),
   date_de_creation DATE NOT NULL,
   mail_verifier BOOLEAN NOT NULL,
   PRIMARY KEY(id_utilisateur),
   UNIQUE(mail),
   UNIQUE(token)
);

CREATE TABLE client(
   id_utilisateur INT,
   societe VARCHAR(64),
   PRIMARY KEY(id_utilisateur),
   FOREIGN KEY(id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);

CREATE TABLE formateur(
   id_utilisateur INT,
   linkedin VARCHAR(128),
   cv VARCHAR(128),
   date_signature DATE,
   signature_electronique VARCHAR(128),
   total_heures INT,
   PRIMARY KEY(id_utilisateur),
   FOREIGN KEY(id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);

CREATE TABLE admin(
   id_utilisateur INT,
   PRIMARY KEY(id_utilisateur),
   FOREIGN KEY(id_utilisateur) REFERENCES formateur(id_utilisateur) 
);

CREATE TABLE discussion (
   id_utilisateur INT,
   id_utilisateur_1 INT,
   id_discussion SERIAL,
   PRIMARY KEY(id_utilisateur, id_utilisateur_1, id_discussion),
   FOREIGN KEY(id_utilisateur) REFERENCES client(id_utilisateur) ON DELETE CASCADE,
   FOREIGN KEY(id_utilisateur_1) REFERENCES formateur(id_utilisateur) ON DELETE CASCADE
);

CREATE TABLE message (
   id_utilisateur_1 INT,
   id_utilisateur_2 INT,
   id_discussion INT,
   id_message SERIAL,
   texte VARCHAR(512) NOT NULL,
   date_heure TIMESTAMP NOT NULL,
   validation_moderation BOOLEAN NOT NULL,
   lu BOOLEAN NOT NULL,
   id_utilisateur INT,
   PRIMARY KEY(id_utilisateur_1, id_utilisateur_2, id_discussion, id_message),
   FOREIGN KEY(id_utilisateur_1, id_utilisateur_2, id_discussion) REFERENCES discussion(id_utilisateur, id_utilisateur_1, id_discussion) ON DELETE CASCADE,
   FOREIGN KEY(id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);

CREATE TABLE public(
   id_public SERIAL,
   libelle_public VARCHAR(64) NOT NULL,
   PRIMARY KEY(id_public)
);

CREATE TABLE categorie(
   id_categorie SERIAL,
   nom_categorie VARCHAR(64) NOT NULL,
   valide_categorie BOOLEAN NOT NULL,
   id_categorie_composee INT,
   PRIMARY KEY(id_categorie),
   FOREIGN KEY(id_categorie_composee) REFERENCES categorie(id_categorie)
);

CREATE TABLE niveau(
   id_niveau SERIAL,
   libelle_niveau VARCHAR(64) NOT NULL,
   PRIMARY KEY(id_niveau)
);

CREATE TABLE activite(
   id_activite SERIAL,
   nom_activite VARCHAR(50) NOT NULL,
   image VARCHAR(64) NOT NULL,
   description VARCHAR(256) NOT NULL,
   id_utilisateur INT NOT NULL,
   PRIMARY KEY(id_activite),
   FOREIGN KEY(id_utilisateur) REFERENCES admin(id_utilisateur)
);

CREATE TABLE moderateur(
   id_moderateur SERIAL,
   id_utilisateur INT NOT NULL,
   id_utilisateur_1 INT NOT NULL,
   PRIMARY KEY(id_moderateur),
   FOREIGN KEY(id_utilisateur) REFERENCES admin(id_utilisateur),
   FOREIGN KEY(id_utilisateur_1) REFERENCES formateur(id_utilisateur) ON DELETE CASCADE
);

CREATE TABLE theme(
   id_theme SERIAL,
   nom_theme VARCHAR(64) NOT NULL,
   valide_theme BOOLEAN NOT NULL,
   id_categorie INT NOT NULL,
   PRIMARY KEY(id_theme),
   FOREIGN KEY(id_categorie) REFERENCES categorie(id_categorie)
);

CREATE TABLE Affranchis(
   id_moderateur INT,
   id_utilisateur INT,
   PRIMARY KEY(id_moderateur, id_utilisateur),
   /*FOREIGN KEY(id_moderateur) REFERENCES moderateur(id_utilisateur_1),*/
   FOREIGN KEY(id_utilisateur) REFERENCES utilisateur(id_utilisateur)
);

CREATE TABLE aExpertiseProfessionnelle(
    id_utilisateur INT,
    id_theme INT,
    duree_experience DECIMAL(15,2) NOT NULL,
    commentaire VARCHAR(128),
    id_niveau INT NOT NULL,
    id_categorie INT NOT NULL, -- Nouvelle colonne id_categorie
    PRIMARY KEY(id_utilisateur, id_theme),
    FOREIGN KEY(id_utilisateur) REFERENCES formateur(id_utilisateur),
    FOREIGN KEY(id_theme) REFERENCES theme(id_theme),
    FOREIGN KEY(id_niveau) REFERENCES niveau(id_niveau),
    FOREIGN KEY(id_categorie) REFERENCES categorie(id_categorie) -- Nouvelle clé étrangère
);

CREATE TABLE aExpeirencePeda(
   id_utilisateur INT,
   id_theme INT,
   id_public INT,
   volume_h_moyen_session DECIMAL(15,2) NOT NULL,
   nb_session_effectuee INT NOT NULL,
   commentaire VARCHAR(128),
   PRIMARY KEY(id_utilisateur, id_theme, id_public),
   FOREIGN KEY(id_utilisateur) REFERENCES formateur(id_utilisateur),
   FOREIGN KEY(id_theme) REFERENCES theme(id_theme),
   FOREIGN KEY(id_public) REFERENCES public(id_public)
);