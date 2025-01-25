<?php

/**
 * Controller_formateurs - Classe permettant de gérer les formateurs : Récupérer ses informations, affiche les vues formateurs.
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

class Controller_formateurs extends Controller
{
    public function action_default()
    {
        $this->action_formateurs();
    }

    /**
     * Action pour les formateurs.
     *
     * Cette fonction gère la logique de la page formateurs.
     * Elle vérifie si l'utilisateur a accès, en récupérant le rôle de l'utilisateur.
     * Enfin, elle affiche la vue 'formateurs' avec les données.
     *
     * @return void
     */
    public function action_formateurs()
    {
        $user = checkUserAccess();

        if (!$user) {
            // Si accès refusé, affiche un message et rend la vue d'authentification
            echo "Accès non autorisé.";
            $this->render('auth', []);
            return;
        }

        // Récupère le rôle de l'utilisateur
        $role = getUserRole($user);
   
        // Détermine la page à afficher, par défaut la page 1
        $page = 1;
        if (isset($_GET["page"]) && preg_match("/^\d+$/", $_GET["page"]) && $_GET["page"] > 0) {
            $page = e($_GET["page"]);
        }

        // Récupère le terme de recherche
        $search = isset($_POST['search']) ? trim(e($_POST['search'])) : '';

        // Obtient le modèle pour interagir avec les données
        $model = Model::getModel();
        $isAdmin= $model->verifAdmin($user['id_utilisateur']);
        $isModo= $model->verifModerateur($user['id_utilisateur']);
        // Récupère la catégorie et les thèmes sélectionnés
        $categorieId = isset($_POST['select-options']) ? $_POST['select-options'] : null;
        $themes = isset($_POST['selected-themes']) ? $_POST['selected-themes'] : null;

        // Récupère les formateurs selon la page, la catégorie et les thèmes sélectionnés
        $formateurs = $model->getFormateursBasicInfoByPageAndCategoryOrTheme3($page, $categorieId, $themes);
        $nbFormateurs = $model->getNbFormateurByThemeOrCategorie3($themes, $categorieId);
        $nb_total_pages = $model->getFormateurPagesByCategoryOrTheme3($categorieId, null);
 
     
        // Détermine les numéros de page à afficher pour la pagination
        $debut = $page - 5;
        if ($debut <= 0) {
            $debut = 1;
        }
        
        $fin = $debut + 9;
        if ($fin > $nb_total_pages) {
            $fin = $nb_total_pages;
        }

        // Prépare les données pour la vue
        $data = [
            'selectedThemes' => $themes,
            'selectedCategoryId' => $categorieId,
            'categories' => $model->getAllCategories(),
            'themes' => $model->getThemesByCategoryId($categorieId),
            'formateurs' => $formateurs,
            'active' => $page,
            'debut' => $debut,
            'fin' => $fin,
            'nb_total_pages' => $nb_total_pages,
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'photo_de_profil' => $user['photo_de_profil'],
            'role' => $role,
            'isAdmin' => $isAdmin,
            'isModo'=> $isModo
        ];

        // Rend la vue 'formateurs' avec les données
        $this->render('formateurs', $data);
    }

    /**
     * Action pour afficher les détails d'un formateur.
     *
     * Cette action vérifie si l'utilisateur a accès. Si l'accès est refusé, elle affiche un message d'erreur et affiche la vue       d'authentification.
     * Si l'accès est accordé, elle récupère le rôle de l'utilisateur. Ensuite, elle obtient le modèle pour interagir avec les données.
     * Elle récupère l'ID du formateur à partir des paramètres GET.
     * Elle récupère les détails du formateur par ID à partir du modèle.
     * Elle récupère les informations supplémentaires du formateur à partir du modèle.
     * elle affiche la vue 'formateurs_details' avec les données.
     *
     * @return void
     */
    public function action_details()
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
        $this->render('formateurs_details', [
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
}
?>
