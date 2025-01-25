-- Suppression des données existantes
DELETE FROM aExpertiseProfessionnelle;
DELETE FROM aExpeirencePeda;
DELETE FROM Affranchis;
DELETE FROM theme;
DELETE FROM moderateur;
DELETE FROM activite;
DELETE FROM niveau;
DELETE FROM categorie;
DELETE FROM public;
DELETE FROM message;
DELETE FROM discussion;
DELETE FROM admin;
DELETE FROM formateur;
DELETE FROM client;
DELETE FROM utilisateur;

-- Réinitialisation de l'auto-incrémentation des ID
ALTER SEQUENCE utilisateur_id_utilisateur_seq RESTART WITH 1;
ALTER SEQUENCE client_id_utilisateur_seq RESTART WITH 1;
ALTER SEQUENCE formateur_id_utilisateur_seq RESTART WITH 1;
ALTER SEQUENCE admin_id_utilisateur_seq RESTART WITH 1;
ALTER SEQUENCE discussion_id_discussion_seq RESTART WITH 1;
ALTER SEQUENCE message_id_message_seq RESTART WITH 1;
ALTER SEQUENCE public_id_public_seq RESTART WITH 1;
ALTER SEQUENCE categorie_id_categorie_seq RESTART WITH 1;
ALTER SEQUENCE niveau_id_niveau_seq RESTART WITH 1;
ALTER SEQUENCE activite_id_activite_seq RESTART WITH 1;
ALTER SEQUENCE moderateur_id_moderateur_seq RESTART WITH 1;
ALTER SEQUENCE theme_id_theme_seq RESTART WITH 1;
ALTER SEQUENCE Affranchis_id_moderateur_seq RESTART WITH 1;
ALTER SEQUENCE aExpertiseProfessionnelle_id_utilisateur_seq RESTART WITH 1;
ALTER SEQUENCE aExpertiseProfessionnelle_id_theme_seq RESTART WITH 1;
ALTER SEQUENCE aExpeirencePeda_id_utilisateur_seq RESTART WITH 1;
ALTER SEQUENCE aExpeirencePeda_id_theme_seq RESTART WITH 1;
ALTER SEQUENCE aExpeirencePeda_id_public_seq RESTART WITH 1;

-- Ajout des niveaux
INSERT INTO niveau (libelle_niveau) VALUES
   ('Expert'),
   ('Confirmé'),
   ('Initié'),
   ('Débutant');

-- Ajout des utilisateurs
INSERT INTO utilisateur (nom, prenom, password, mail, token, photo_de_profil, date_de_creation, mail_verifier)
VALUES 
  ('Doe', 'John', '$2y$10$DVC5X.gdRgRV.UYRPvqnUuoMr/3yPoqMFemUmLOScWzgZ9s3fyANK', 'john.doe@email.com', 'tokenjohn', 'default.png', '2022-01-01', true),
  ('Smith', 'Jane', '$2y$10$DVC5X.gdRgRV.UYRPvqnUuoMr/3yPoqMFemUmLOScWzgZ9s3fyANK', 'jane.smith@email.com', 'tokenjane', 'default.png', '2022-01-02', true),
  ('Johnson', 'Bob', '$2y$10$DVC5X.gdRgRV.UYRPvqnUuoMr/3yPoqMFemUmLOScWzgZ9s3fyANK', 'bob.johnson@email.com', 'tokenbob', 'default.png', '2022-01-03', true),
  ('Brown', 'Alice', '$2y$10$DVC5X.gdRgRV.UYRPvqnUuoMr/3yPoqMFemUmLOScWzgZ9s3fyANK', 'alice.brown@email.com', 'tokenalice', 'default.png', '2022-01-04', true),
  ('Wilson', 'Charlie', '$2y$10$DVC5X.gdRgRV.UYRPvqnUuoMr/3yPoqMFemUmLOScWzgZ9s3fyANK', 'charlie.wilson@email.com', 'tokencharlie', 'default.png', '2022-01-05', true);

-- Ajout des clients
INSERT INTO client (id_utilisateur, societe) VALUES
  (1, 'Company A'),
  (2, 'Company B'),
  (4, 'Company C');

-- Ajout des formateurs
INSERT INTO formateur (id_utilisateur, linkedin, cv, date_signature, signature_electronique, total_heures) VALUES
  (3, 'linkedin.com/bobjohnson', 'cv_bob.pdf', '2022-01-04', 'signature_bob', 1000),
  (5, 'linkedin.com/charliewilson', 'cv_charlie.pdf', '2022-01-06', 'signature_charlie', 800);

-- Ajout des admins
INSERT INTO admin (id_utilisateur) VALUES (3);

-- Ajout des publics
INSERT INTO public (libelle_public) VALUES
  ('Public A'),
  ('Public B');

-- Ajout des catégories
INSERT INTO categorie (nom_categorie, valide_categorie, id_categorie_composee) VALUES
  ('Python', true, NULL),
  ('Java', true, 1),
  ('C++', true, 2);

-- Ajout des thèmes
INSERT INTO theme (nom_theme, valide_theme, id_categorie) VALUES
  ('Bases', true, 1),
  ('POO', true, 1),
  ('Communication C/S', true, 1);

-- Ajout des expertises professionnelles
INSERT INTO aExpertiseProfessionnelle (id_utilisateur, id_theme, duree_experience, commentaire, id_niveau,id_categorie) VALUES
  (3, 1, 5.5, 'Expert en Python Bases', 1,1),
  (5, 2, 3.5, 'Confirmé en POO 3', 2,2);

-- Ajout des expériences pédagogiques
INSERT INTO aExpeirencePeda (id_utilisateur, id_theme, id_public, volume_h_moyen_session, nb_session_effectuee, commentaire) VALUES
  (3, 2, 1, 3.5, 10, 'Expérience avec Public A'),
  (5, 1, 2, 2.0, 5, 'Expérience avec Public B');
