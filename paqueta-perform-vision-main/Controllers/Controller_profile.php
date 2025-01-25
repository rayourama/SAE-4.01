<?php

/**
 * Controller_profile - Classe permettant de gérer le profil de l'utilisateur : Récupérer ses informations et les modifier.
 * 
 * @see       https://gitlab.sorbonne-paris-nord.fr/12105377/pand_artist Le projet de l'équipe Pand'Artists !
 *
 * @author    Axel Giffard
 * @author    India Cabo
 * @author    Ryan Ramassamy
 * @author    Céline Jin
 * @author    Patrick Chen
 * 
 * @package pand_artist
 * @subpackage Controller
 * @version 1.0
 * 
 */

class Controller_profile extends Controller
{
    public function action_default()
    {
        $this->action_profile();
    }

    /**
     * Action pour afficher le profil de l'utilisateur connecté.
     
     * @return void
     */
    public function action_profile()
    {
        $user = checkUserAccess();

        // Vérifie si l'utilisateur est connecté, sinon affiche un message d'erreur et redirige vers la page d'authentification
        if (!$user) {
            echo "Accès non autorisé.";
            $this->render('auth', []);
            return;
        }

        $role = getUserRole($user);
        $model = Model::getModel();
        $isAdmin= $model->verifAdmin($user['id_utilisateur']);
        $isModo= $model->verifModerateur($user['id_utilisateur']);
        // Prépare les données de base pour la vue
        $data = [
            'mail' => $user['mail'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'photo_de_profil' => $user['photo_de_profil'],
            'role' => $role,
            'isAdmin' => $isAdmin,
            'isModo' => $isModo
        ];

        // Récupère les données spécifiques en fonction du rôle de l'utilisateur (Client ou Formateur)
        if ($role === 'Client') {
            $data['societe'] = $model->getClientById($user['id_utilisateur']);
            $this->render('monprofilclient', $data);
        } elseif ($role === 'Formateur') {
            $data['formateur'] = $model->getFormateurById($user['id_utilisateur']);
                $data['formateur'] = $model->getFormateurById($user['id_utilisateur']);
                $data['competences'] = $model->getCompetencesFormateurById($user['id_utilisateur']);
                $data['themes'] = $model->getAllThemes();
                $data['niveaux'] = $model->getAllNiveaux();
                $data['categories'] = $model->getCategoriesByUserId($user['id_utilisateur']); 
/*                  $data['competences'] = $model->getCategoriesByUserId($user['id_utilisateur']);
 */             $this->render('monprofilformateur', $data);
        } else {
            echo "Accès non autorisé.";
            $this->render('auth', []);
        }
    }

    /**
 * Action pour afficher la page de modification du profil.
 *
 * Vérifie si l'utilisateur est connecté
 * Prépare les données de base pour la vue en fonction du rôle de l'utilisateur (Client ou Formateur).
 * Récupère les données spécifiques en fonction du rôle et affiche la vue correspondante.
 *
 * @return void
 */
    public function action_modifier()
    {
        $user = checkUserAccess();

        // Vérifie si l'utilisateur est connecté, sinon affiche un message d'erreur et redirige vers la page d'authentification
        if (!$user) {
            echo "Accès non autorisé.";
            $this->render('auth', []);
            return;
        }

        $role = getUserRole($user);
        $model = Model::getModel();
        $isAdmin= $model->verifAdmin($user['id_utilisateur']);
        $isModo= $model->verifModerateur($user['id_utilisateur']);
        // Prépare les données de base pour la vue
        
        $data = [
            'mail' => $user['mail'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'photo_de_profil' => $user['photo_de_profil'],
            'role' => $role,
            'isAdmin' => $isAdmin,
            'isModo' => $isModo	
        ];

        // Récupère les données spécifiques en fonction du rôle de l'utilisateur (Client ou Formateur) et affiche la vue correspondante
        if ($role === 'Client') {
            $data['societe'] = $model->getClientById($user['id_utilisateur']);
            $this->render('modifiermonprofilClient', $data);
        } elseif ($role === 'Formateur') {
            $data['formateur'] = $model->getFormateurById($user['id_utilisateur']);
            $data['competences'] = $model->getCompetencesFormateurById($user['id_utilisateur']);
            $data['themes']=$model->getAllThemes();
            $data['niveaux']=$model->getAllNiveaux();
            $this->render('modifiermonprofilformateur', $data);
        } else {
            echo "Accès non autorisé.";
            $this->render('auth', []);
        }
    }

    /**
 * Action pour modifier les informations du profil.
 *
 * Vérifie si la requête est de type POST, sinon redirige vers le profil
 * Vérifie si l'utilisateur est connecté
 * Met à jour les informations du profil utilisateur
 * Redirige vers le profil après les edits.
 *
 * @return void
 */
    public function action_modifier_info()
    {
        // Vérifie si la requête est de type POST, sinon redirige vers le profil
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?controller=profile');
            exit();
        }

        $user = checkUserAccess();

        // Vérifie si l'utilisateur est connecté, sinon affiche un message d'erreur et redirige vers la page d'authentification
        if (!$user) {
            echo "Accès non autorisé.";
            $this->render('auth', []);
            return;
        }

        $role = getUserRole($user);
        $model = Model::getModel();

        /* Suppression de la vérification d'un nouvel email pour désactiver la mise à jour */ 
       

        // Met à jour le mot de passe de l'utilisateur s'il est fourni
        if (isset($_POST['nouveau_mot_de_passe']) && !empty($_POST['nouveau_mot_de_passe'])) {
            $nouveau_mot_de_passe = e(trim($_POST['nouveau_mot_de_passe']));
            if (strlen($nouveau_mot_de_passe) <= 256) {
                $model->updatePassword($user['id_utilisateur'], $nouveau_mot_de_passe);
            }
        }

        // Met à jour le nom de la société du client si l'utilisateur est un client
        if (isset($_POST['nouvelle_societe'])) {
            $nouvelle_societe = e(trim($_POST['nouvelle_societe']));
            if (!empty($nouvelle_societe) && $nouvelle_societe !== $model->getClientById($user['id_utilisateur'])['societe']) {
                $model->updateSociete($user['id_utilisateur'], $nouvelle_societe);
            }
        }

        // Met à jour le profil LinkedIn du formateur si l'utilisateur est un formateur
        if (isset($_POST['nouveau_linkedin'])) {
            $nouveau_linkedin = e(trim($_POST['nouveau_linkedin']));
            $ancien_linkedin = $model->getFormateurById($user['id_utilisateur'])['linkedin'];
        
            if (!empty($nouveau_linkedin) && $nouveau_linkedin !== $ancien_linkedin) {
                $model->updateLinkedIn($user['id_utilisateur'], $nouveau_linkedin);
            }
        }

        // Met à jour le CV du formateur si l'utilisateur est un formateur
        if (isset($_POST['nouveau_cv'])) {
            $nouveau_cv = e(trim($_POST['nouveau_cv']));
            $ancien_cv = $model->getFormateurById($user['id_utilisateur'])['cv'];
        
            if (!empty($nouveau_cv) && $nouveau_cv !== $ancien_cv) {
                $model->updateCV($user['id_utilisateur'], $nouveau_cv);
            }
        }

        if (isset($_POST['competences'])) {
            $competences = json_decode($_POST['competences'], true); // Décoder le JSON
        
            foreach ($competences as $competence) {
                $nom_competence = e(trim($competence['skillName']));
        
                if ($model->addCategory($nom_competence, true)) {
                    $lastCategoryId = $model->getLastCategoryId();
                } else {
                    // Gérer le cas où la catégorie existe déjà
                }
        
                $id_theme = intval($competence['skillSpecialty']);
                $id_niveau = intval($competence['skillLevel']);
        
                $model->addProfessionalExpertise($user['id_utilisateur'], $id_theme, $id_niveau, $lastCategoryId);
            }
        }

       


        // Redirige vers le profil après modification
        header('Location: ?controller=profile');
        exit();
    }

}
