<?php

/**
 * Controller_panel - Classe permettant de gérer l'administration des formateurs et des clients en tant qu'Administrateur et Modérateur.
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

class Controller_panel extends Controller
{
    public function action_default()
    {
        $this->action_panel();
    }

    /**
     * Gère l'action de l'administration.
     *
     * Si l'utilisateur n'est ni administrateur ni modérateur, elle affiche un message d'erreur et redirige vers la page d'authentification.
     * Sinon, si l'utilisateur est modérateur, elle récupère les discussions et les utilisateurs non affranchis.
     * Sinon, si l'utilisateur est administrateur, elle récupère la liste des formateurs avec statut de modérateur.
     *
     * @return void
     */
    public function action_panel()
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


        $id_formateur = isset($_GET['id']) ? e($_GET['id']) : null;

        // Récupère les détails du formateur par ID
        $formateur = $model->getUserById($id_formateur);

        // Récupère les informations supplémentaires du formateur
        $niveaux = $model->getLevelDataById($id_formateur);
        $pedagogicalExperience = $model->getPedagogicalExperienceDataById($id_formateur);
        $categories = $model->getCategorieDataById($id_formateur);
        $themes = $model->getThemeDataById($id_formateur);
        $expertises = $model->getExpertiseDataById($id_formateur);


        $categorieId = isset($_POST['select-options']) ? $_POST['select-options'] : null;
        $themes = isset($_POST['selected-themes']) ? $_POST['selected-themes'] : null;
        $page = 1;

        // Vérifie si l'utilisateur est admin ou modérateur
        $isAdmin = $model->verifAdmin($user['id_utilisateur']);
        $isModo = $model->verifModerateur($user['id_utilisateur']);
        $formateurs = $model->getFormateursBasicInfoByPageAndCategoryOrTheme3($page, $categorieId, $themes);

        // Prépare les données de base pour la vue
        $data = [
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'photo_de_profil' => $user['photo_de_profil'],
            'role' => $role,
            'isAdmin' => $isAdmin,
            'isModo'=> $isModo,
            'formateurs'=>$formateurs,
        ];

        // Si l'utilisateur n'est ni admin ni modérateur, affiche un message d'erreur et redirige vers la page d'authentification
        if (!$isAdmin && !$isModo) {
            echo "Accès non autorisé.";
            $this->render('auth', []);
            return;
        } elseif ($isModo) {
            // Si l'utilisateur est modérateur, récupère les discussions et les utilisateurs non affranchis

          
            $data['discussions'] = $model->recupererToutesDiscussions();
            $data['utilisateurs'] = $model->recupererUtilisateursNonAffranchis();
            $data['formateurs'] = $model->listeFormateursAvecStatutModerateur();
            $data['competences'] = $model->getCompetencesFormateurById($user['id_utilisateur']);
            $data['categories'] = $categories;
            $data['niveaux']=$niveaux;
            $data['pedagogicalExperience']=$pedagogicalExperience;
            $data['themes']=$themes;
            $data['expertises']=$expertises;
            $this->render('panel_moderateur', $data);
        } else {
            // Si l'utilisateur est admin, récupère la liste des formateurs avec statut de modérateur
            $data['formateurs'] = $model->listeFormateursAvecStatutModerateur();
            $this->render('panel_administrateur', $data);
        }
    }

    /**
 * Gère l'action de gestion des modérateurs.
 *
 * Si la requête n'est pas une requête GET, redirige vers le panneau de contrôle.
 * Vérifie si l'utilisateur est connecté et a le rôle d'administrateur.
 * Récupère l'ID du formateur et l'action à effectuer (promotion ou rétrogradation).
 * Effectue l'action correspondante sur le formateur.
 * Redirige vers le panneau de contrôle après l'exécution de l'action.
 *
 * @return void
 */
    public function action_manage_moderator()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header('Location: ?controller=panel');
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

        // Récupère l'ID du formateur depuis les paramètres GET
        $formateurId = isset($_GET['id']) ? e($_GET['id']) : null;
        if (!$formateurId) {
            header('Location: ?controller=panel');
            exit();
        }

        // Vérifie si l'utilisateur est admin
        $isAdmin = $model->verifAdmin($user['id_utilisateur']);

        if (!$isAdmin) {
            echo "Accès non autorisé.";
            $this->render('auth', []);
            return;
        }

        // Récupère l'action à effectuer (promote/demote) depuis les paramètres GET
        $manage = isset($_GET['manage']) ? strtolower(trim(e($_GET['manage']))) : '';

        // Effectue l'action correspondante sur le formateur
        if ($manage === 'promote') {
            $model->addModerator($user['id_utilisateur'], $formateurId);
        } elseif ($manage === 'demote') {
            $model->removeModerator($formateurId);
        } else {
            echo "Accès non autorisé.";
            $this->render('auth', []);
            return;
        }

        header('Location: ?controller=panel');
        exit();
    }

    /**
 * Action pour ajouter une nouvelle activité.
 *
 * Vérifie si l'utilisateur est connecté et a le rôle d'administrateur.
 * créer une nouvelle activité via la requête POST
 * Gère l'upload de l'image associée à l'activité.
 * Ajoute l'activité avec les données récupérées.
 * Redirige vers le panneau de contrôle après l'ajout de l'activité.
 *
 * @return void
 */
    public function action_add_activity()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?controller=panel');
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

        // Vérifie si l'utilisateur est admin
        $isAdmin = $model->verifAdmin($user['id_utilisateur']);

        if (!$isAdmin) {
            echo "Accès non autorisé.";
            $this->render('auth', []);
            return;
        }

        // Récupère les données du formulaire POST
        $nom_activite = isset($_POST['name']) ? e($_POST['name']) : null;
        $description = isset($_POST['description']) ? e($_POST['description']) : null;

        // Gestion de l'upload de l'image
        $image = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'Content/data/';
            $uploadPath = $uploadDir . basename($_FILES['photo']['name']);

            // Vérifie que le répertoire de téléchargement existe, sinon le crée
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Déplace le fichier téléchargé dans le répertoire spécifié
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
                $image = $_FILES['photo']['name'];
            } else {
                // Gestion de l'échec du téléchargement de l'image
                echo "Image upload failed.";
                exit();
            }
        }

        // Ajoute l'activité avec les données récupérées
        $model->addActivity($nom_activite, $image, $description, $user['id_utilisateur']);

        header('Location: ?controller=panel');
        exit();
    }

    /**
 * Action pour affranchir un utilisateur.
 *
 * Vérifie si l'utilisateur est connecté et a le rôle de modérateur.
 * Récupère l'ID de l'utilisateur
 * Affranchit l'utilisateur.
 * Redirige vers le panneau de contrôle après l'affranchissement.
 *
 * @return void
 */
    public function action_add_affranchi()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header('Location: ?controller=panel');
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

        // Récupère l'ID de l'utilisateur à affranchir depuis les paramètres GET
        $userId = isset($_GET['id']) ? e($_GET['id']) : null;
        if (!$userId) {
            header('Location: ?controller=panel');
            exit();
        }

        // Vérifie si l'utilisateur est modérateur
        $isModo = $model->verifModerateur($user['id_utilisateur']);

        if (!$isModo) {
            echo "Accès non autorisé.";
            $this->render('auth', []);
            return;
        }

        // Affranchit l'utilisateur spécifié
        $model->affranchirUtilisateur($user['id_utilisateur'], $userId);

        header('Location: ?controller=panel');
        exit();
    }
    
    public function action_details_form()
    {
        // Vérifie si l'utilisateur a accès
        $user = checkUserAccess();

        if (!$user) {
            // Si accès refusé, affiche un message et rend la vue d'authentification
            echo "Accès non autorisé.";
            $this->render('auth', []);
            return;
        }

        // Récupère le rôle de l'utilisateur
        $role = getUserRole($user);

        // Obtient le modèle pour interagir avec les données
        $modele = Model::getModel();
        $isAdmin= $modele->verifAdmin($user['id_utilisateur']);
        $isModo= $modele->verifModerateur($user['id_utilisateur']);
        // Récupère l'ID du formateur depuis les paramètres GET
        $id_formateur = isset($_GET['id']) ? e($_GET['id']) : null;

        // Récupère les détails du formateur par ID
        $formateur = $modele->getUserById($id_formateur);

        // Récupère les informations supplémentaires du formateur
        $niveaux = $modele->getLevelDataById($id_formateur);
        $pedagogicalExperience = $modele->getPedagogicalExperienceDataById($id_formateur);
        $categories = $modele->getCategorieDataById($id_formateur);
        $themes = $modele->getThemeDataById($id_formateur);
        $expertises = $modele->getExpertiseDataById($id_formateur);

        // Rend la vue 'formateurs_details' avec les données
        $this->render('panel_moderateur', [
            'formateur' => $formateur,
            'niveaux' => $niveaux,
            'pedagogicalExperience' => $pedagogicalExperience,
            'categories' => $categories,
            'themes' => $themes,
            'expertises' => $expertises,
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'photo_de_profil' => $user['photo_de_profil'],
            'role' => $role,
            'isAdmin' => $isAdmin,
            'isModo'=> $isModo
        ]);
    }

       /**
     * Gère l'action de récupération des compétences,themes,expertises de chaque formateur.
     *
     * Si l'utilisateur n'est ni administrateur ni modérateur, elle affiche un message d'erreur et redirige vers la page d'authentification.
     * Sinon, si l'utilisateur est modérateur, elle récupère les discussions et les utilisateurs non affranchis puis les compétences des formateurs.
     * Sinon, si l'utilisateur est administrateur, elle récupère la liste des formateurs avec statut de modérateur.
     *
     * @return void
     */
    public function action_getCompetences() {


        $id_formateur = isset($_GET['id']) ? e($_GET['id']) : null;

        if ($id_formateur) {
            $model = Model::getModel();
            $formateur = $model->getUserById($id_formateur);

            $niveaux = $model->getLevelDataById($id_formateur);
            $pedagogicalExperience = $model->getPedagogicalExperienceDataById($id_formateur);
            $themes = $model->getThemeDataById($id_formateur);
            $expertises = $model->getExpertiseDataById($id_formateur);
    
    
            $categorieId = isset($_POST['select-options']) ? $_POST['select-options'] : null;
            $themes = isset($_POST['selected-themes']) ? $_POST['selected-themes'] : null;
            $page = 1;
            $competences = $model->getCompetencesFormateurById($id_formateur);
          $categories = $model->getCategoriessByUserId($id_formateur);

            $formateurs = $model->getFormateursBasicInfoByPageAndCategoryOrTheme3($page, $categorieId, $themes);

    
        $html = "<div class='formateurs-container'>";
        $html .= "<article class='card'>";
        $html .= "<div class='content'>";
        $html .= "<div class='card-header'>";
        $html .= "<div class='latest-article'>";
        $html .= "<p>" . htmlspecialchars($formateur['prenom'] . ' ' . $formateur['nom']) . "</p>";
        $html .= "</div></div>";
        $html .= "<div class='competences title'>Compétences :</div>";
        $html .= "<div class='competences values'>";
        foreach ($categories as $categorie) {
            if($categorie['nom_categorie']==null){
                $html .= "<p>Aucune compétence pour le moment</p>";

            }
            $html .= "<p>" . htmlspecialchars($categorie['nom_categorie']) . "</p>";
        }
        $html .= "</div>";

        $html .= "<div class='expertise title'>Expertise professionnelle :</div>";
        $html .= "<div class='expertise values'><ul>";
        foreach ($expertises as $expertise) {
            $html .= "<li>";
            $html .= "<p>Thème: " . htmlspecialchars($expertise['nom_theme']) . "</p>";
            $html .= "<p>Durée de l'Expérience: " . htmlspecialchars($expertise['duree_experience']) . " heures</p>";
            foreach ($niveaux as $niveau) {
                if ($niveau['id_niveau'] === $expertise['id_niveau']) {
                    $html .= "<p>Niveau: " . htmlspecialchars($niveau['libelle_niveau']) . "</p>";
                }
            }
            $html .= "<p>Commentaire: " . htmlspecialchars($expertise['commentaire']) . "</p>";
            $html .= "</li>";
        }
        $html .= "</ul></div>";

        $html .= "<div class='experiences title'>Expériences pédagogiques :</div>";
        $html .= "<div class='experiences values'><ul>";
        foreach ($pedagogicalExperience as $experience) {
            $html .= "<li>";
            $html .= "<p>Thème: " . htmlspecialchars($experience['nom_theme']) . "</p>";
            $html .= "<p>Public: " . htmlspecialchars($experience['libelle_public']) . "</p>";
            $html .= "<p>Volume Horaire Moyen par Session: " . htmlspecialchars($experience['volume_h_moyen_session']) . " heures</p>";
            $html .= "<p>Nombre de Sessions Effectuées: " . htmlspecialchars($experience['nb_session_effectuee']) . "</p>";
            $html .= "<p>Commentaire: " . htmlspecialchars($experience['commentaire']) . "</p>";
            $html .= "</li>";
        }
        $html .= "</ul></div>";

        $html .= "</div></article></div>";

        echo $html;
        } else {
            echo "Aucune compétence trouvée pour ce formateur.";
        }
    }
}


