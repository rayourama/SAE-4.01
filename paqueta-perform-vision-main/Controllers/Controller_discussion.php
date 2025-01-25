<?php

/**
 * Controller_discussion - Classe permettant de gérer les discussions entre un client et un formateur.
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
 * @class Controller_discussion
 */

class Controller_discussion extends Controller
{
    public function action_default()
    {
        $this->action_list();
    }

    /**
     * Retourne une liste de discussions pour l'utilisateur authentifié.
     *
     * Cette méthode récupère toutes les discussions de l'utilisateur,
     * puis parcoure chaque discussion pour obtenir les détails nécessaires.
     * Les informations de chaque discussion, telles que le nom de l'interlocuteur,
     * le nombre de messages non lus, et l'URL de la discussion, sont stockées dans
     * un tableau. Ce tableau est ensuite utilisé pour afficher une liste
     * de discussions dans la vue 'discussion_list'.
     *
     * @return void
     */
    public function action_list()
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

        // Obtient le modèle pour interagir avec les données
        $model = Model::getModel();

        $isAdmin= $model->verifAdmin($user['id_utilisateur']);
        $isModo= $model->verifModerateur($user['id_utilisateur']);
        // Récupère les discussions de l'utilisateur
        $discussions = $model->recupererDiscussion($user['id_utilisateur']);
        $discussionList = [];

        // Parcourt chaque discussion pour obtenir les détails nécessaires
        foreach ($discussions as $discussion) {
            $interlocuteurId = ($role === 'Client') ? $discussion['id_utilisateur_1'] : $discussion['id_utilisateur'];
            $interlocuteur = $model->getUserById($interlocuteurId);

            if (!$interlocuteur) continue;

            // Compte les messages non lus
            $unreadMessages = $model->countUnreadMessages($interlocuteurId, $discussion['id_discussion']);

            // Ajoute les informations de la discussion à la liste
            $discussionList[] = [
                'discussion_id' => $discussion['id_discussion'],
                'nom_interlocuteur' => $interlocuteur['nom'],
                'prenom_interlocuteur' => $interlocuteur['prenom'],
                'photo_interlocuteur' => $interlocuteur['photo_de_profil'],
                'unread_messages' => ($unreadMessages > 0),
            ];
        }

        // Prépare les données pour la vue
        $data = [
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'photo_de_profil' => $user['photo_de_profil'],
            'role' => $role,
            'discussions' => $discussionList,
            'isAdmin' => $isAdmin,
            'isModo'=> $isModo
        ];

        // Rend la vue 'discussion_list' avec les données
        $this->render('discussion_list', $data);
    }

    /** 
      * Action pour afficher une discussion spécifique */    
    public function action_discussion()
    {
        // Vérifie si l'utilisateur a accès
        $user = checkUserAccess();

        if (!$user) {
            // Si accès refusé, affiche un message et rend la vue d'authentification
            echo "Accès non autorisé.";
            $this->render('auth', []);
            return;
        }

        // Obtient le modèle pour interagir avec les données
        $model = Model::getModel();

        // Récupère l'ID de la discussion depuis les paramètres GET
        $discussionId = isset($_GET['id']) ? e($_GET['id']) : null;

        if (!$discussionId) {
            // Redirige si l'ID de la discussion n'est pas fourni
            header('Location: ?controller=discussion');
            exit();
        }

        // Vérifie si l'utilisateur est modérateur
        $isModo = $model->verifModerateur($user['id_utilisateur']);
        $isAdmin= $model->verifAdmin($user['id_utilisateur']);
        // Récupère les détails de la discussion par ID
        $discussion = $model->getDiscussionById($discussionId);

        // Vérifie si la discussion est valide et si l'utilisateur en fait partie
        if (!$discussion || !($isModo || isUserInDiscussion($user['id_utilisateur'], $discussion))) {
            header('Location: ?controller=discussion');
            exit();
        }

        // Récupère le rôle de l'utilisateur et les détails de l'interlocuteur
        $role = getUserRole($user);
        $receiverId = ($role === 'Client') ? $discussion['id_utilisateur_1'] : $discussion['id_utilisateur'];
        $receiver = $model->getUserById($receiverId);

        if (!$receiver) {
            header('Location: ?controller=discussion');
            exit();
        }

        // Récupère les messages de la discussion
        $messages = $model->messagesDiscussion($discussionId);

        // Prépare les données pour la vue
        $data = [
            'nom_receiver' => $receiver['nom'],
            'prenom_receiver' => $receiver['prenom'],
            'photo_receiver' => $receiver['photo_de_profil'],
            'messages' => $messages,
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'photo_de_profil' => $user['photo_de_profil'],
            'role' => $role,
            'user_id' => $user['id_utilisateur'],
            'isModo' => $isModo,
            'isAdmin' => $isAdmin
        ];

        // Rend la vue 'discussion' avec les données
        $this->render('discussion', $data);
    }

    /** 
     * Action pour envoyer un message dans une discussion
     * */
    public function action_envoi_message()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Redirige si la méthode de requête n'est pas POST
            header('Location: ?controller=discussion');
            exit();
        }

        // Vérifie si l'utilisateur a accès
        $user = checkUserAccess();

        if (!$user) {
            // Si accès refusé, affiche un message et rend la vue d'authentification
            echo "Accès non autorisé.";
            $this->render('auth', []);
            return;
        }

        // Obtient le modèle pour interagir avec les données
        $model = Model::getModel();

        // Récupère l'ID de la discussion depuis les données POST
        $discussionId = isset($_POST['discussionId']) ? e($_POST['discussionId']) : null;

        if (!$discussionId) {
            // Redirige si l'ID de la discussion n'est pas fourni
            header('Location: ?controller=discussion');
            exit();
        }

        // Récupère les détails de la discussion par ID
        $discussion = $model->getDiscussionById($discussionId);

        // Vérifie si la discussion est valide et si l'utilisateur en fait partie
        if (!$discussion || !isUserInDiscussion($user['id_utilisateur'], $discussion)) {
            header('Location: ?controller=discussion');
            exit();
        }

        // Récupère le texte du message depuis les données POST
        $texteMessage = isset($_POST['texte_message']) ? e($_POST['texte_message']) : '';

        // Vérifie les rôles de l'utilisateur pour la modération
        $isAdmin = $model->verifAdmin($user['id_utilisateur']);
        $isModo = $model->verifModerateur($user['id_utilisateur']);
        $isAffranchi = $model->verifAffranchiModerateur($user['id_utilisateur']);

        // Détermine si la modération est requise
        $validation_moderation = ($isAdmin || $isModo || $isAffranchi);

        // Ajoute le message à la discussion
        $model->addMessageToDiscussion($texteMessage, $discussion['id_utilisateur'], $discussion['id_utilisateur_1'], $discussionId, $validation_moderation, $user['id_utilisateur']);

        // Redirige vers la discussion
        header('Location: ?controller=discussion&action=discussion&id=' . $discussionId);
        exit();
    }

    /** 
     * Action pour demarrer une nouvelle discussion */
    public function action_start_discussion()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Redirige si la méthode de requête n'est pas POST
            header('Location: ?controller=discussion');
            exit();
        }

        // Vérifie si l'utilisateur a accès
        $user = checkUserAccess();

        if (!$user) {
            // Si accès refusé, affiche un message et rend la vue d'authentification
            echo "Accès non autorisé.";
            $this->render('auth', []);
            return;
        }

        // Obtient le modèle pour interagir avec les données
        $model = Model::getModel();

        // Récupère l'ID du client et du formateur depuis les données POST
        $id_client = $user['id_utilisateur'];
        $id_formateur = isset($_POST['id_formateur']) ? e($_POST['id_formateur']) : null;

        // Démarre une nouvelle discussion
        $discussion_id = $model->startDiscussion($id_client, $id_formateur);

        if (!$discussion_id) {
            // Redirige si la création de la discussion a échoué
            header('Location: ?controller=discussion');
            exit();
        }

        // Redirige vers la nouvelle discussion
        header('Location: ?controller=discussion&action=discussion&id=' . $discussion_id);
        exit();
    }

    /**
     * Action pour valider un message (modération) */ 
    public function action_validate_message()
    {
        // Vérifie si l'utilisateur a accès
        $user = checkUserAccess();

        if (!$user) {
            // Si accès refusé, affiche un message et rend la vue d'authentification
            echo "Accès non autorisé.";
            $this->render('auth', []);
            return;
        }

        // Obtient le modèle pour interagir avec les données
        $model = Model::getModel();

        // Vérifie si l'utilisateur est modérateur
        $isModo = $model->verifModerateur($user['id_utilisateur']);
        if (!$isModo) {
            echo "Accès non autorisé.";
            $this->render('auth', []);
            return;
        }

        // Récupère l'ID du message à valider depuis les paramètres GET
        $id_message = isset($_GET['id_message']) ? e($_GET['id_message']) : null;

        if (!$id_message) {
            // Redirige si l'ID du message n'est pas fourni
            header('Location: ?controller=discussion');
            exit();
        }

        // Valide le message et récupère l'ID de la discussion correspondante
        $discussion_id = $model->validateMessage($id_message);

        if (!$discussion_id) {
            echo "Erreur lors de la validation du message.";
            exit();
        }

        // Redirige vers la discussion contenant le message validé
        header('Location: ?controller=discussion&action=discussion&id=' . $discussion_id);
        exit();
    }
}
?>
