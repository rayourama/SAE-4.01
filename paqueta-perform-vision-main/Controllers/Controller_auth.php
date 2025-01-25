<?php

/**
 * Controller_Auth - Classe permettant de gerer les actions de connexion.
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

class Controller_auth extends Controller
{
    /**
     * Affiche la vue "auth".
     *
     * Cette fonction est responsable d'afficher la vue "auth". Elle ne prend pas de paramètres et ne retourne aucune valeur.
     *
     * @return void
     */
    public function action_auth()
    {
        $this->render("auth", []);
    }

    /**
     * Fonction contrôlant le processus de connexion
     *
     * Cette fonction vérifie si la requête est de type POST et si les champs email et password sont définis et non vides.
     * @return void
     */
    public function action_default()
    {
        $this->action_auth();
    }

    // le systeme de la connexion
    public function action_login()
    {
        // on vérifie si la requête est de type POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // on vérifie egalement les champs email et password sont définis et non vides
            if (isset($_POST['email'], $_POST['password']) && !empty($_POST['email']) && !empty($_POST['password'])) {
                // Nettoie les valeurs de l'email et du mot de passe
                $email = e(trim($_POST['email']));
                $password = e(trim($_POST['password']));

                // on regarde les longueurs des champs email et mot de passe
                if (strlen($password) <= 256 && strlen($email) <= 128) {
                    // Vérifie si l'email est valide
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        // Récupère l'utilisateur par les identifiants
                        $user = Model::getModel()->getUserByCredentials($email, $password);

                        if ($user) {

                            var_dump($user);

                            session_start();

                            // Stocke les informations de l'utilisateur dans la session
                            $_SESSION['user_id'] = $user['id_utilisateur'];
                            $_SESSION['user_token'] = $user['token'];
                            $_SESSION['expire_time'] = time() + (30 * 60); // la session expire au bout de 30 minutes

                            // Redirige vers le tableau de bord après la connexion réussie
                            header("Location: ?controller=dashboard");
                            exit();
                        } else {
                            // Affiche un message d'erreur si l'identifiants est incorrect
                            echo "Identifiants incorrects.";
                        }
                    } else {
                        // Affiche un message d'erreur si le format de l'email est pas valide
                        echo "Format d'e-mail invalide.";
                    }
                } else {
                    // Affiche un message d'erreur si la donnée dépasse un certain nombre de lettre  
                    echo "Les données saisies dépassent les limites autorisées.";
                }
            } else {
                // Affiche un message d'erreur si les champs requis ne sont pas remplis
                echo "Veuillez remplir tous les champs requis.";
            }
        } else {
            // Affiche un message d'erreur si l'accès n'est pas autorisé (pas une requête POST)
            echo "Accès non autorisé.";
        }

        $this->render("auth", []);
    }

    /**
         * Inscription d'un nouvel utilisateur.
         *
         * Cette fonction vérifie si la requête est une requête POST et vérifie que les champs requis ne sont pas vides.
         * Elle nettoie ensuite les valeurs des champs et vérifie les longueurs des champs.
         * Elle vérifie également si l'email a un format valide et si le nom et le prénom ne contiennent que des lettres et des tirets.
         * Si le rôle de l'utilisateur est valide, elle crée un nouvel utilisateur et génère un lien de vérification envoyé par email.
         * Si la création de l'utilisateur réussit, elle affiche un message de succès et envoie un email de vérification.
         * Sinon, elle affiche un message d'erreur.
         * Si la requête n'est pas une requête POST, elle affiche un message d'erreur.
         * Enfin, elle rend la vue "auth" sans données spécifiques.
         */
         public function action_register() {

        // Vérifie si la requête est de type POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //verifie les champs pour qu'ils soit non vide et remplis celon les conditions 
            if (
                isset($_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['password'], $_POST['tabs'])
                && !empty($_POST['nom']) && !empty($_POST['prenom']) && !empty($_POST['email']) && !empty($_POST['password'])
            ) {
                // Nettoie les valeurs des champs
                $nom = e(trim($_POST['nom']));
                $prenom = e(trim($_POST['prenom']));
                $email = e(trim($_POST['email']));
                $password = e(trim($_POST['password']));

                // Vérifie les longueurs des champs
                if (strlen($nom) <= 64 && strlen($prenom) <= 64 && strlen($password) <= 256 && strlen($email) <= 128) {
                    // Vérifie si l'email a un format valide
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        // Vérifie si le nom et le prénom ne contiennent que des lettres et des tirets
                        if (preg_match('/^[a-zA-Z\-]+$/', $nom) && preg_match('/^[a-zA-Z\-]+$/', $prenom)) {

                            // Détermine le rôle de l'utilisateur
                            $role = isset($_POST['tabs']) ? ($_POST['tabs'] === 'client' ? 'client' : ($_POST['tabs'] === 'formateur' ? 'formateur' : '')) : '';

                            // Vérifie si le rôle est valide
                            if ($role !== '') {

                                if (strlen($password) >= 8) {
                                    if(preg_match('/[A-Z]/', $password)){
                                        if(preg_match('/[a-z]/', $password)) {
                                            if(preg_match('/[!@#$%^&*()\-_=+\[\]{}|;:\'",.<>?\/]/', $password)) {
                                                $result = Model::getModel()->creationUtilisateur($nom, $prenom, $password, $email, $role);
                                                // Si la création de l'utilisateur a réussi
                                                if ($result) {
                                                    echo "Inscription réussie!<br>";
                                                    $etat = "success";
                                                    // Récupère le token de vérification de l'utilisateur
                                                    $verificationToken = Model::getModel()->getTokenUtilisateur($email);
                                                    // Génère le lien de vérification
                                                    $verificationLink = 'http://localhost/perform_vision/?controller=auth&action=valide_email'. '&token=' . urlencode($verificationToken);
                        
                                                    // Envoie un email de vérification à l'utilisateur
                                                    EmailSender::sendVerificationEmail($email, 'Vérification de l\'adresse e-mail', 'Cliquez sur le lien suivant pour vérifier votre adresse e-mail: ' . $verificationLink);
                                                    
                                                    echo "<br> Un e-mail de vérification a été envoyé à votre adresse.<br>";
                                                                                
                                                } else {
                                                    // Affiche un message d'erreur si la création de l'utilisateur a échoué
                                                    echo "Erreur lors de l'inscription.";
                                                }
                                            } else {
                                                echo "Le mot de passe doit contenir au minimum un caractère spécial";

                                            }
                                        } else {
                                            echo "Le mot de passe doit contenir au minimum une minuscule.";
                                        }
                                    } else {
                                        echo "Le mot de passe doit contenir au minimum une majuscule.";
                                    }
                                } else {
                                    echo "Le mot de passe saisie doit faire au minimum 8 caractères.";
                                }
                            } else {
                                // Affiche un message d'erreur si le rôle est invalide
                                echo "Rôle invalide.";
                            }
                        } else {
                            // Affiche un message d'erreur si le nom et le prénom ne respectent pas les critères
                            echo "Le nom et le prénom ne doivent contenir que des lettres et des tirets.";
                        }
                    } else {
                        // Affiche un message d'erreur si le format de l'email est invalide
                        echo "Format d'email invalide.";
                    }
                } else {
                    // Affiche un message d'erreur si les données saisies dépassent les limites autorisées
                    echo "Les données saisies dépassent les limites autorisées.";
                }
            } else {
                // Affiche un message d'erreur si les champs requis ne sont pas remplis
                echo "Veuillez remplir tous les champs requis.";
            }
        } else {
            // Affiche un message d'erreur si l'accès n'est pas autorisé (pas une requête POST)
            echo  "Accès non autorisé.";
        }


       // Rend la vue "auth" sans données spécifiques
        $this->render("auth", []);
    }

    
         /**
         * Change le mot de passe de l'utilisateur quand le mot de passe est oublié
         *
         * @return void
        */
        public function action_change_mdp() {

            $model = Model::getModel();

        // Récupère le token depuis les paramètres de l'URL
        if (isset($_GET['email']) && preg_match('/^[^@]+@[^@]+\.[^@]+$/', $_GET['email'])) {
            if($model->change_mdp($_GET['email'],$model->generateRandomPassword(12))){
                $model->change_mdp($_GET['email'],$model->generateRandomPassword(12));
            }
        }
        
        $this->render("auth", 'le mot de passe a bien été changer');
    }

        /**
     * Détruis la session en déconnectant l'utilisateur.
     *
     * @return void
     */
    public function action_logout(){

        session_destroy();
        $this->render('auth', []);
        return;
    }
}
?>
