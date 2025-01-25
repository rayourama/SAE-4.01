<?php

/**
 * Controller_Dashboard - Classe permettant de gerer la partie du dashboard pour un client et un formateur.
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

class Controller_dashboard extends Controller
{
    public function action_default()
    {
        $this->action_dashboard();
    }

    /**
     * Affiche la vue "dashboard" pour un utilisateur avec son rôle et ses discussions.
     *
     * @throws Exception
     */
    public function action_dashboard()
    {
        $user = checkUserAccess();

        // Si accès refusé, affiche un message et rend la vue d'authentification
        if (!$user) {
            echo "Accès non autorisé.";
            $this->render('auth', []);
            return;  // Empêche l'exécution du reste du code
        }

        // Récupère le rôle de l'utilisateur
        $role = getUserRole($user);

        // Récupère le modèle
        $model = Model::getModel();

        // Récupère les discussions de l'utilisateur
        $discussions = $model->recupererDiscussion($user['id_utilisateur']);
        $isAdmin = $model->verifAdmin($user['id_utilisateur']);
        $isModo = $model->verifModerateur($user['id_utilisateur']);

        $discussionList = [];

        // Parcourt les discussions pour obtenir les détails nécessaires
        foreach ($discussions as $discussion) {
            $interlocuteurId = ($role === 'Client') ? $discussion['id_utilisateur_1'] : $discussion['id_utilisateur'];
            $interlocuteur = $model->getUserById($interlocuteurId);
    
            if (!$interlocuteur) continue;

            $lastMessage = $model->getLastMessageInfo($interlocuteurId, $discussion['id_discussion']);
    
            $discussionList[] = [
                'discussion_id' => $discussion['id_discussion'],
                'nom_interlocuteur' => $interlocuteur['nom'],
                'prenom_interlocuteur' => $interlocuteur['prenom'],
                'photo_interlocuteur' => $interlocuteur['photo_de_profil'],
                'lastMessage' => $lastMessage,
            ];
        }

        // Rend la vue "dashboard" avec les données utilisateur et les discussions
        $this->render('dashboard', [
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'photo_de_profil' => $user['photo_de_profil'],
            'role' => $role,
            'discussions' => $discussionList,
            'isAdmin'=>$isAdmin,
            'isModo'=>$isModo
        ]);
    }

    
}
?>
