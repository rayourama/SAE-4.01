<?php

class Model
{
    /**
     * Attribut contenant l'instance PDO
     */
    private $bd;

    /**
     * Attribut statique qui contiendra l'unique instance de Model
     */
    private static $instance = null;

    /**
     * Constructeur : effectue la connexion à la base de données.
     */
    private function __construct()
    {
        include "credentials.php";//ATTENTION !! le credentials.php actuel ne fonctionne que sur windows en LOCALHOST. 
        $this->bd = new PDO($dsn, $login, $mdp);
        $this->bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->bd->query("SET nameS 'utf8'");
    }

    /**
     * Méthode permettant de récupérer un modèle car le constructeur est privé (Implémentation du Design Pattern Singleton)
     */
    public static function getModel()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
//////////////////// -LES FONCTIONS- ////////////////////

////// -auth- //////

    /**
     * Get the token for a user by email.
     *
     * @param string $email User's email
     * @return string|false Token on success, false on failure
     */
    public function getTokenUtilisateur($email) {
        try {
            $stmt = $this->bd->prepare("SELECT token FROM utilisateur WHERE mail = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return $result['token'];
            } else {
                return false; // User not found or token not available
            }
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Valider le token d'un utilisateur.
     *
     * @param string $token Token de l'utilisateur
     * @return bool True en cas de succès, false en cas d'échec
     */
    public function validerTokenUtilisateur($token) {
        try {
            $stmt = $this->bd->prepare("UPDATE utilisateur SET mail_verifier = true WHERE token = :token");
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     *  Creer utilisateur
     * 
     * @param string $nom Nom de l'utilisateur
     * @param string $prenom Prenom de l'utilisateur
     * @param string $password  Mot de Passe de l'utilisateur
     * @param string $mail Mail utilisateur
     * @param string $role Formateur ou Client
     * @return Bool
     */
    public function creationUtilisateur($nom, $prenom, $password, $mail, $role)
    {
        try {
            // Generate token
            $token = bin2hex(random_bytes(128)); // 256 characters
    
            // hash
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $photo_de_profil='default.png';

            //requete 
            $stmt = $this->bd->prepare("INSERT INTO utilisateur (nom, prenom, password, mail, token, photo_de_profil, date_de_creation, mail_verifier) VALUES (:nom, :prenom, :password, :mail, :token, :photo_de_profil, NOW(), FALSE)");
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':mail', $mail);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':photo_de_profil', $photo_de_profil);
            $stmt->execute();
    
            $userId = $this->bd->lastInsertId();
    

            if ($role === 'client') {
                $stmt = $this->bd->prepare("INSERT INTO client (id_utilisateur) VALUES (:userId)");
            } elseif ($role === 'formateur') {
                $stmt = $this->bd->prepare("INSERT INTO formateur (id_utilisateur) VALUES (:userId)");
            }
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
    
            return true;
        } catch (PDOException $e) {
            //echo 'Erreur : ' . $e->getMessage();
            return false;
        }
    }
    
    /**
     *  Verifier mail =>true
     * 
     * @param string $id_utilisateur id de l'utilisateur
     * @param string $token token de l'utilisateur
     * @return Bool
     */
    public function verifierMail($id_utilisateur, $token)
    {
        try {
            $this->bd->beginTransaction();
    
            // Prepare 
            $stmt = $this->bd->prepare("UPDATE utilisateur SET mail_verifier = TRUE WHERE token = :token AND id_utilisateur = :id_utilisateur");
    
            // Bind 
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    
            // Execute 
            $stmt->execute();

            $this->bd->commit();

            $stmt->closeCursor();
    
            return true; // Update 
        } catch (PDOException $e) {
            // Rollback
            $this->bd->rollBack();

            return false; 
        }
    }

    /**
     * Verifier si utilisateur existe
     * 
     * @param string $mail mail de l'utilisateur
     * @return Bool
    */
    public function utilisateurExiste($mail)
    {
        try {
            // Prepare 
            $stmt = $this->bd->prepare("SELECT COUNT(*) FROM utilisateur WHERE mail = :mail");

            // Bind 
            $stmt->bindParam(':mail', $mail);

            // Execute 
            $stmt->execute();

            $count = $stmt->fetchColumn();

            $stmt->closeCursor();

            return $count > 0; // Retourne True si il y a plus de 0 utilisateur avec l'email
        } catch (PDOException $e) {
            return false; 
        }
    }

    /**
     * Verifier si le mail est verifier ou pas
     * 
     * @param string $mail mail de l'utilisateur
     * @return Bool
     */
    public function isMailVerified($mail)
    {
        try {
            // Prepare 
            $stmt = $this->bd->prepare("SELECT mail_verifier FROM utilisateur WHERE mail = :mail");

            // Bind 
            $stmt->bindParam(':mail', $mail);

            // Execute 
            $stmt->execute();

            $mailVerified = $stmt->fetchColumn();
   
            $stmt->closeCursor();

            return $mailVerified === true; // Renvoie true si mail_verifier est true, false sinon
        } catch (PDOException $e) {
            return false; 
        }
    }

    /**
     * Connection utilisateur
     * 
     * @param string $mail mail de l'utilisateur
     * @param string $password mail de l'utilisateur
     * @return Bool
     */
    public function getUserByCredentials($mail, $password)
    {
        try {
            // Prepare
            $stmt = $this->bd->prepare("SELECT * FROM utilisateur WHERE mail = :mail");

            // Bind
            $stmt->bindParam(':mail', $mail);

            // Execute
            $stmt->execute();

            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            
            $stmt->closeCursor();

            // Vérifiez le mot de passe si l'utilisateur est trouvé
            if ($user && password_verify($password, $user['password'])) {

                //unset($user['password']); // Supprimez le mot de passe haché du résultat pour des raisons de sécurité => COMMENTé POUR LE MOMMENT
                return $user;
            } else {
                // Soit l'utilisateur est introuvable, soit le mot de passe est incorrect
                return null;
            }
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Verifier le token et renvoyer info
     * 
     * @param string $token token de l'utilisateur
     * @return Bool
     */
    public function verifierToken($token){
        try {
            // Prepare
            $stmt = $this->bd->prepare("SELECT * FROM utilisateur WHERE token = :token");

            // Bind
            $stmt->bindParam(':token', $token);

            // Execute
            $stmt->execute();

            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            
            $stmt->closeCursor();

            // Vérifiez le mot de passe si l'utilisateur est trouvé
            if ($user && $token == $user['token']) {

                //unset($user['password']); // Supprimez le mot de passe haché du résultat pour des raisons de sécurité => COMMENTé POUR LE MOMMENT
                return $user;
            } else {
                // Soit l'utilisateur est introuvable, soit le mot de passe est incorrect
                return null;
            }
        } catch (PDOException $e) {
            return null;
        }
    }

////// -discussion- //////

    /**
     * Récuperer les discussions l'utilisateur
     * 
     * @param string $id_utilisateur
     * @return array liste des discussions
     */
    public function recupererDiscussion($id_utilisateur){
        try{
            // Prepare 
            $stmt = $this->bd->prepare("SELECT * FROM discussion WHERE id_utilisateur = :id_utilisateur OR id_utilisateur_1 = :id_utilisateur");

            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();  
             
            $tab = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $tab;
        } catch  (PDOException $e) {
            return null;
        }
    }

    /**
     * Récuperer dernier message envoyé
     * 
     * @param string $discussion
     * @return array le dernier message
     */
    public function dernierMessageDiscussion($id_discussion){
        try{
            // Prepare 
            $stmt = $this->bd->prepare("SELECT * FROM message WHERE id_discussion = :id_discussion ORDER BY date_heure DESC LIMIT 1");

            $stmt->bindParam(':id_discussion', $id_discussion, PDO::PARAM_INT);
            $stmt->execute();  
             
            $tab = $stmt->fetch(PDO::FETCH_ASSOC);
            return $tab;
        } catch  (PDOException $e) {
            return null;
        }
    }

    /**
     * récuperer messages d'une discussion
     * 
     * @param string $discussion
     * @return array les messages
     */
    public function messagesDiscussion($id_discussion){
        try{
            // Prepare 
            $stmt = $this->bd->prepare("SELECT * FROM message WHERE id_discussion = :id_discussion ORDER BY date_heure ASC");

            $stmt->bindParam(':id_discussion', $id_discussion, PDO::PARAM_INT);
            $stmt->execute();  
             
            $tab = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $tab;
        } catch  (PDOException $e) {
            return null;
        }
    }
    
////// -verif cookie token/id- //////

    /**
     * verifier id et token
     * 
     * @param string $id_utilisateur id de l'utilisateur
     * @param string $token token de l'utilisateur
     * @return bool
     */
    public function verifCookie($id_utilisateur,$token){
        try{
            // Prepare 
            $stmt = $this->bd->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = :id_utilisateur");

            // Bind
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

            // Execute
            $stmt->execute();

            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);


            if ($token == $user['token']) {
                return true;
            } else {
                return false;
            }

        } catch  (PDOException $e) {
            return null;
        }
    }

    /**
     * Vérifier si l'utilisateur est un administrateur.
     *
     * @param string $id_utilisateur Identifiant de l'utilisateur
     * @return bool
     */
    public function verifAdmin($id_utilisateur){
        try{
            // Prepare 
            $stmt = $this->bd->prepare("SELECT * FROM admin WHERE id_utilisateur = :id_utilisateur");
    
            // Bind
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    
            // Execute
            $stmt->execute();
    
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($user !== false && $id_utilisateur == $user['id_utilisateur']) {
                return true;
            } else {
                return false;
            }
    
        } catch (PDOException $e) {
            return false;
        }
    }
    

    /**
     * Vérifier si l'utilisateur est un administrateur.
     *
     * @param string $id_utilisateur Identifiant de l'utilisateur
     * @return bool
     */
    public function verifModerateur($id_utilisateur){
        try{
            // Prepare 
            $stmt = $this->bd->prepare("SELECT * FROM moderateur WHERE id_utilisateur_1 = :id_utilisateur");
    
            // Bind
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    
            // Execute
            $stmt->execute();
    
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($user !== false && $id_utilisateur == $user['id_utilisateur_1']) {
                return true;
            } else {
                return false;
            }
    
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Vérifier si l'utilisateur est affranchi en tant que modérateur.
     *
     * @param string $id_utilisateur Identifiant de l'utilisateur
     * @return bool
     */
    public function verifAffranchiModerateur($id_utilisateur){
        try{
            // Préparation de la requête
            $stmt = $this->bd->prepare("SELECT * FROM Affranchis WHERE id_utilisateur = :id_utilisateur");

            // Liaison des paramètres
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

            // Exécution de la requête
            $stmt->execute();

            // Récupération des résultats
            $affranchi = $stmt->fetch(PDO::FETCH_ASSOC);

            // Vérification si l'utilisateur est affranchi en tant que modérateur
            return ($affranchi !== false && $id_utilisateur == $affranchi['id_utilisateur']);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Vérifiez si un formateur existe.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur
     * @return bool Si le formateur existe ou non
     */
    public function verifFormateur($id_utilisateur) {
        try {
            $stmt = $this->bd->prepare("SELECT COUNT(*) FROM formateur WHERE id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();

            $count = $stmt->fetchColumn();

            return ($count > 0);
        } catch (PDOException $e) {
            return false;
        }
    }

////// -GET NB- //////

    /**
     * Obtenir le nombre d'utilisateurs.
     *
     * @return int
     */
    public function nbUtilisateur(){
        try{
            // Prepare 
            $stmt = $this->bd->prepare("SELECT COUNT(*) AS nb FROM utilisateur;");

            // Execute
            $stmt->execute();

            $nb = $stmt->fetch(PDO::FETCH_ASSOC);

            return $nb['nb'];
             
        } catch  (PDOException $e) {
            return null;
        }
    }

    /**
     * Obtenir le nombre de clients.
     *
     * @return int
     */
    public function nbClient(){
        try{
            // Prepare 
            $stmt = $this->bd->prepare("SELECT COUNT(*) AS nb FROM client;");

            // Execute
            $stmt->execute();

            $nb = $stmt->fetch(PDO::FETCH_ASSOC);

            return $nb['nb'];
             
        } catch  (PDOException $e) {
            return null;
        }
    }

    /**
     * Obtenir le nombre de formateurs.
     *
     * @return int
     */
    public function nbFormateur(){
        try{
            // Prepare 
            $stmt = $this->bd->prepare("SELECT COUNT(*) AS nb FROM formateur;");

            // Execute
            $stmt->execute();

            $nb = $stmt->fetch(PDO::FETCH_ASSOC);

            return $nb['nb'];
             
        } catch  (PDOException $e) {
            return null;
        }
    }

    /**
     * Obtenir le nombre de discussions.
     *
     * @return int
     */
    public function nbDiscussion(){
        try{
            // Prepare 
            $stmt = $this->bd->prepare("SELECT COUNT(*) AS nb FROM discussion;");

            // Execute
            $stmt->execute();

            $nb = $stmt->fetch(PDO::FETCH_ASSOC);

            return $nb['nb'];
             
        } catch  (PDOException $e) {
            return null;
        }
    }

    /**
     * Obtenir le nombre de catégories.
     *
     * @return int
     */
    public function nbCategorie(){
        try{
            // Prepare 
            $stmt = $this->bd->prepare("SELECT COUNT(*) AS nb FROM categorie;");

            // Execute
            $stmt->execute();

            $nb = $stmt->fetch(PDO::FETCH_ASSOC);

            return $nb['nb'];
             
        } catch  (PDOException $e) {
            return null;
        }
    }

    /**
     * Obtenir le nombre d'activités.
     *
     * @return int
     */
    public function nbActivite(){
        try{
            // Prepare 
            $stmt = $this->bd->prepare("SELECT COUNT(*) AS nb FROM activite;");

            // Execute
            $stmt->execute();

            $nb = $stmt->fetch(PDO::FETCH_ASSOC);

            return $nb['nb'];
             
        } catch  (PDOException $e) {
            return null;
        }
    }

    /**
     * Obtenir le nombre de modérateurs.
     *
     * @return int
     */
    public function nbModerateur(){
        try{
            // Prepare 
            $stmt = $this->bd->prepare("SELECT COUNT(*) AS nb FROM moderateur;");

            // Execute
            $stmt->execute();

            $nb = $stmt->fetch(PDO::FETCH_ASSOC);

            return $nb['nb'];
             
        } catch  (PDOException $e) {
            return null;
        }
    }

    /**
     * Obtenir le nombre de thèmes.
     *
     * @return int
     */
    public function nbTheme(){
        try{
            // Prepare 
            $stmt = $this->bd->prepare("SELECT COUNT(*) AS nb FROM theme;");

            // Execute
            $stmt->execute();

            $nb = $stmt->fetch(PDO::FETCH_ASSOC);

            return $nb['nb'];
             
        } catch  (PDOException $e) {
            return null;
        }
    }

    /**
     * Obtenir le nombre d'affranchis.
     *
     * @return int
     */
    public function nbAffranchis(){
        try{
            // Prepare 
            $stmt = $this->bd->prepare("SELECT COUNT(*) AS nb FROM Affranchis;");

            // Execute
            $stmt->execute();

            $nb = $stmt->fetch(PDO::FETCH_ASSOC);

            return $nb['nb'];
             
        } catch  (PDOException $e) {
            return null;
        }
    }

////// -Administration- //////
//suprimer//

    /**
     * Supprimer un utilisateur.
     *
     * @param String $id_utilisateur Identifiant de l'utilisateur
     * @return int
     */
    public function deleteUtilisateur($id_utilisateur){
        try {
            $stmt = $this->bd->prepare("DELETE FROM utilisateur WHERE id_utilisateur = :id_utilisateur");
    
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    
            $success = $stmt->execute();

            if ($success) {
                return true;
            } else {
                return false;
            }
    
        } catch (PDOException $e) {

            return false;
        }
    }

    /**
     * Supprimez une activité.
     *
     * @param int $id_activite Identifiant de l'activité
     * @return bool Succès ou échec
     */
    public function deleteActivity($id_activite) {
        try {
            $stmt = $this->bd->prepare("DELETE FROM activite WHERE id_activite = :id_activite");
            $stmt->bindParam(':id_activite', $id_activite, PDO::PARAM_INT);
            $success = $stmt->execute();

            if ($success) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Supprimez une discussion et ses messages associés.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur
     * @param int $id_utilisateur_1 Autre identifiant d'utilisateur
     * @param int $id_discussion Identifiant de la discussion
     * @return bool Succès ou échec
     */
    public function deleteDiscussion($id_utilisateur, $id_utilisateur_1, $id_discussion) {
        try {
            $this->bd->beginTransaction();

            // Delete messages in the discussion
            $this->deleteMessagesInDiscussion($id_utilisateur, $id_utilisateur_1, $id_discussion);

            // Delete the discussion
            $stmt = $this->bd->prepare("DELETE FROM discussion WHERE id_utilisateur = :id_utilisateur 
                AND id_utilisateur_1 = :id_utilisateur_1 AND id_discussion = :id_discussion");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->bindParam(':id_utilisateur_1', $id_utilisateur_1, PDO::PARAM_INT);
            $stmt->bindParam(':id_discussion', $id_discussion, PDO::PARAM_INT);
            $success = $stmt->execute();

            if ($success) {
                $this->bd->commit();
                return true;
            } else {
                $this->bd->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            $this->bd->rollBack();
            return false;
        }
    }

    /**
     * Supprimez les messages dans une discussion.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur
     * @param int $id_utilisateur_1 Autre identifiant d'utilisateur
     * @param int $id_discussion Identifiant de la discussion
     * @return bool Succès ou échec
     */
    private function deleteMessagesInDiscussion($id_utilisateur, $id_utilisateur_1, $id_discussion) {
        try{
            $stmt = $this->bd->prepare("DELETE FROM message WHERE id_utilisateur = :id_utilisateur 
                AND id_utilisateur_1 = :id_utilisateur_1 AND id_discussion = :id_discussion");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->bindParam(':id_utilisateur_1', $id_utilisateur_1, PDO::PARAM_INT);
            $stmt->bindParam(':id_discussion', $id_discussion, PDO::PARAM_INT);
            $success = $stmt->execute();

            if ($success) {
                $this->bd->commit();
                return true;
            } else {
                $this->bd->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            $this->bd->rollBack();
            return false;
        }
    }

    /**
     * Supprimez une catégorie, ses thèmes et les activités associées.
     *
     * @param int $id_categorie Identifiant de la catégorie
     * @return bool Succès ou échec
     */
    public function deleteCategoryAndThemes($id_categorie) {
        try {
            $this->bd->beginTransaction();

            // Delete themes in the category
            $this->deleteThemesInCategory($id_categorie);

            // Delete the category
            $stmt = $this->bd->prepare("DELETE FROM categorie WHERE id_categorie = :id_categorie");
            $stmt->bindParam(':id_categorie', $id_categorie, PDO::PARAM_INT);
            $success = $stmt->execute();

            if ($success) {
                $this->bd->commit();
                return true;
            } else {
                $this->bd->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            $this->bd->rollBack();
            return false;
        }
    }

    /**
     * Supprimez les thèmes dans une catégorie et les activités associées.
     *
     * @param int $id_categorie Identifiant de la catégorie
     * @return bool Succès ou échec
     */
    private function deleteThemesInCategory($id_categorie) {
        try{
            $stmt = $this->bd->prepare("DELETE FROM theme WHERE id_categorie = :id_categorie");
            $stmt->bindParam(':id_categorie', $id_categorie, PDO::PARAM_INT);
            $stmt->execute();

            // Delete associated activities
            $this->deleteActivitiesWithThemeInCategory($id_categorie);

            if ($success) {
                $this->bd->commit();
                return true;
            } else {
                $this->bd->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            $this->bd->rollBack();
            return false;
        }
    }

    /**
     * Supprimez les activités avec un thème dans une catégorie.
     *
     * @param int $id_categorie Identifiant de la catégorie
     * @return bool Succès ou échec
     */
    private function deleteActivitiesWithThemeInCategory($id_categorie) {
        try{
            $stmt = $this->bd->prepare("DELETE FROM activite WHERE id_categorie = :id_categorie");
            $stmt->bindParam(':id_categorie', $id_categorie, PDO::PARAM_INT);
            $stmt->execute();
            if ($success) {
                $this->bd->commit();
                return true;
            } else {
                $this->bd->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            $this->bd->rollBack();
            return false;
        }
    }

    /**
     * Retirez un utilisateur du statut de modérateur.
     *
     * @param int $id_moderateur Identifiant du modérateur
     * @return bool Succès ou échec
     */
    public function removeModerator($id_moderateur) {
        try {
            $stmt = $this->bd->prepare("DELETE FROM moderateur WHERE id_utilisateur_1 = :id_moderateur");
            $stmt->bindParam(':id_moderateur', $id_moderateur, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }


//ajouter//

/**
 * Ajoutez une nouvelle expertise professionnelle pour un utilisateur.
 *
 * @param int $id_utilisateur Identifiant de l'utilisateur
 * @param int $id_theme Identifiant du thème (compétence)
 * @param int $id_niveau Identifiant du niveau
 * @param int $id_categorie Identifiant de la catégorie
 * @return bool Succès ou échec
 */
public function addProfessionalExpertise($id_utilisateur, $id_theme, $id_niveau, $id_categorie) {
    try {
        $stmt = $this->bd->prepare("
            SELECT * 
            FROM aExpertiseProfessionnelle 
            WHERE id_utilisateur = :id_utilisateur 
              AND id_theme = :id_theme 
              AND id_niveau = :id_niveau
              AND id_categorie = :id_categorie
        ");
        $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
        $stmt->bindParam(':id_theme', $id_theme, PDO::PARAM_INT);
        $stmt->bindParam(':id_niveau', $id_niveau, PDO::PARAM_INT);
        $stmt->bindParam(':id_categorie', $id_categorie, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) { // Aucune expertise similaire trouvée
            // Insére la nouvelle expertise
            $stmt = $this->bd->prepare("
                INSERT INTO aExpertiseProfessionnelle (id_utilisateur, id_theme, id_niveau, id_categorie) 
                VALUES (:id_utilisateur, :id_theme, :id_niveau, :id_categorie)
            ");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->bindParam(':id_theme', $id_theme, PDO::PARAM_INT);
            $stmt->bindParam(':id_niveau', $id_niveau, PDO::PARAM_INT);
            $stmt->bindParam(':id_categorie', $id_categorie, PDO::PARAM_INT);

            return $stmt->execute();
        } else {
          
            return true; 
        }
    } catch (PDOException $e) {
        // Gérer l'erreur PDO
        error_log("Erreur PDO : " . $e->getMessage()); 
        return false;
    }
}

public function getCategoryIdByName($nom_categorie) {
    $stmt = $this->bd->prepare("SELECT id_categorie FROM Categorie WHERE nom_categorie = :nom_categorie");
    $stmt->bindParam(':nom_categorie', $nom_categorie, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['id_categorie'];
    } else {
        return null; // La catégorie n'existe pas
    }
}


/**
 * Obtenir les competences d'un formateur avec la vue.
 *
 * @param int $id_utilisateur Identifiant de l'utilisateur
 * @return bool Succès ou échec
 */
function getCompetencesFormateurWithView($id_utilisateur) {
    $req = $this->bd->prepare("SELECT * FROM view_afficher_competence WHERE id_utilisateur = :id");
    $req->bindValue(':id', $id_utilisateur);
    $req->execute();

    $tab = $req->fetchAll(PDO::FETCH_ASSOC);

    return $tab;
}
/**
 * Obtenir les catégories d'un utilisateur.
 *
 * @param int $id_utilisateur Identifiant de l'utilisateur
 * @return bool Succès ou échec
 */
public function getCategoriesByUserId($id_utilisateur) {
    try {
        $stmt = $this->bd->prepare("
            SELECT DISTINCT c.nom_categorie 
            FROM categorie c
            JOIN theme t ON c.id_categorie = t.id_categorie
            JOIN aexpertiseprofessionnelle aep ON t.id_theme = aep.id_theme
            WHERE aep.id_utilisateur = :id_utilisateur
        ");
        $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
        $stmt->execute();
        
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $categories;
    } catch (PDOException $e) {
        return false;
    }
}

    /**
     * Ajoutez une nouvelle catégorie.
     *
     * @param string $nom_categorie Nom de la catégorie
     * @param bool $valide_categorie Si la catégorie est valide
     * @return bool Succès ou échec
     */
    public function addCategory($nom_categorie, $valide_categorie) {
        try {
            // Vérifier si la catégorie existe déjà dans la table
            $existingCategory = $this->bd->prepare("SELECT id_categorie FROM categorie WHERE nom_categorie = :nom_categorie");
            $existingCategory->bindParam(':nom_categorie', $nom_categorie, PDO::PARAM_STR);
            $existingCategory->execute();
    
            // Si la catégorie existe déjà, ne pas l'insérer à nouveau
            if ($existingCategory->rowCount() > 0) {
                return false; // Ou une autre indication que la catégorie existe déjà
            } else {
                // Insérer la nouvelle catégorie
                $stmt = $this->bd->prepare("INSERT INTO categorie (nom_categorie, valide_categorie) 
                                           VALUES (:nom_categorie, :valide_categorie)");
                $stmt->bindParam(':nom_categorie', $nom_categorie, PDO::PARAM_STR);
                $stmt->bindParam(':valide_categorie', $valide_categorie, PDO::PARAM_BOOL);
                return $stmt->execute();
            }
        } catch (PDOException $e) {
            return false; // Gérer les erreurs de la manière appropriée
        }
    }

    /**
 * Retourne le dernier Id de la table de la catégorie
 *
 * @return array
 */
    public function getLastCategoryId() {
        try {
            $stmt = $this->bd->query("SELECT MAX(id_categorie) AS last_id FROM categorie");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['last_id'];
        } catch (PDOException $e) {
            // Gérer l'exception ici
            return false;
        }
    }

    /**
     * Ajoutez un nouveau thème.
     *
     * @param string $nom_theme Nom du thème
     * @param bool $valide_theme Si le thème est valide
     * @param int $id_categorie Identifiant de la catégorie
     * @return bool Succès ou échec
     */
    public function addTheme($nom_theme, $valide_theme, $id_categorie) {
        try {
            $stmt = $this->bd->prepare("INSERT INTO theme (nom_theme, valide_theme, id_categorie) 
                                       VALUES (:nom_theme, :valide_theme, :id_categorie)");
            $stmt->bindParam(':nom_theme', $nom_theme, PDO::PARAM_STR);
            $stmt->bindParam(':valide_theme', $valide_theme, PDO::PARAM_BOOL);
            $stmt->bindParam(':id_categorie', $id_categorie, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Ajoutez une nouvelle activité.
     *
     * @param string $nom_activite Nom de l'activité
     * @param string $image Image de l'activité
     * @param string $description Description de l'activité
     * @param int $id_utilisateur Identifiant de l'utilisateur
     * @return bool Succès ou échec
     */
    public function addActivity($nom_activite, $image, $description, $id_utilisateur) {
        try {
            $stmt = $this->bd->prepare("INSERT INTO activite (nom_activite, image, description, id_utilisateur) 
                                       VALUES (:nom_activite, :image, :description, :id_utilisateur)");
            $stmt->bindParam(':nom_activite', $nom_activite, PDO::PARAM_STR);
            $stmt->bindParam(':image', $image, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
            /**
         * Ajoute un modérateur
         *
         * @param int $id_utilisateur Identifiant de l'utilisateur
         * @param int $id_utilisateur1 Identifiant du thème (compétence)
         * @return bool Succès ou échec
         */
    public function addModerator($id_utilisateur, $id_utilisateur_1) {
        try {
            $stmt = $this->bd->prepare("INSERT INTO moderateur (id_utilisateur, id_utilisateur_1) 
                                       VALUES (:id_utilisateur, :id_utilisateur_1)");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->bindParam(':id_utilisateur_1', $id_utilisateur_1, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

////// -Ajout d'information- //////

    /**
     * Mettez à jour les informations LinkedIn pour un formateur.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur (formateur)
     * @param string $linkedin Nouvelles informations LinkedIn
     * @return bool Succès ou échec
     */
    public function updateFormateurLinkedin($id_utilisateur, $linkedin) {
        try {
            $stmt = $this->bd->prepare("UPDATE formateur SET linkedin = :linkedin WHERE id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':linkedin', $linkedin, PDO::PARAM_STR);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Mettez à jour les informations du CV pour un formateur.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur (formateur)
     * @param string $cv Nouvelles informations du CV
     * @return bool Succès ou échec
     */
    public function updateFormateurCV($id_utilisateur, $cv) {
        try {
            $stmt = $this->bd->prepare("UPDATE formateur SET cv = :cv WHERE id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':cv', $cv, PDO::PARAM_STR);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Mettez à jour les informations de signature électronique pour un formateur.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur (formateur)
     * @param string $signature_electronique Nouvelles informations de signature électronique
     * @return bool Succès ou échec
     */
    public function updateFormateurSignature($id_utilisateur, $signature_electronique) {
        try {
            $stmt = $this->bd->prepare("UPDATE formateur SET signature_electronique = :signature_electronique WHERE id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':signature_electronique', $signature_electronique, PDO::PARAM_STR);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Mettez à jour le nombre total d'heures pour un formateur.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur (formateur)
     * @param int $total_heures Nouveau total d'heures
     * @return bool Succès ou échec
     */
    public function updateFormateurTotalHours($id_utilisateur, $total_heures) {
        try {
            $stmt = $this->bd->prepare("UPDATE formateur SET total_heures = :total_heures WHERE id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':total_heures', $total_heures, PDO::PARAM_INT);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Mettez à jour la photo de profil pour un utilisateur.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur
     * @param string $photo_de_profil Nouvelles informations de la photo de profil
     * @return bool Succès ou échec
     */
    public function updateUserProfilePicture($id_utilisateur, $photo_de_profil) {
        try {
            $stmt = $this->bd->prepare("UPDATE utilisateur SET photo_de_profil = :photo_de_profil WHERE id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':photo_de_profil', $photo_de_profil, PDO::PARAM_STR);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Mettez à jour les informations de l'entreprise pour un client.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur (client)
     * @param string $societe Nouvelles informations de l'entreprise
     * @return bool Succès ou échec
     */
    public function updateClientCompany($id_utilisateur, $societe) {
        try {
            $stmt = $this->bd->prepare("UPDATE client SET societe = :societe WHERE id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':societe', $societe, PDO::PARAM_STR);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

////// -Supprimer information- //////

////// -get information by id- //////

    /**
     * Obtenez les informations du formateur par l'identifiant.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur (formateur)
     * @return array|false Informations du formateur ou false en cas d'échec
     */
    public function getFormateurById($id_utilisateur) {
        try {
            $stmt = $this->bd->prepare("SELECT * FROM formateur WHERE id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtenez les informations du client par l'identifiant.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur (client)
     * @return array|false Informations du client ou false en cas d'échec
     */
    public function getClientById($id_utilisateur) {
        try {
            $stmt = $this->bd->prepare("SELECT * FROM client WHERE id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtenez les informations de l'utilisateur par l'identifiant.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur
     * @return array|false Informations de l'utilisateur ou false en cas d'échec
     */
    public function getUserById($id_utilisateur) {
        try {
            $stmt = $this->bd->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtenez les informations de l'administrateur par l'identifiant.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur (administrateur)
     * @return array|false Informations de l'administrateur ou false en cas d'échec
     */
    public function getAdminById($id_utilisateur) {
        try {
            $stmt = $this->bd->prepare("SELECT * FROM admin WHERE id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtenez les informations de la discussion par les identifiants d'utilisateurs et l'identifiant de discussion.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur
     * @param int $id_utilisateur_1 Autre identifiant d'utilisateur
     * @param int $id_discussion Identifiant de la discussion
     * @return array|false Informations sur la discussion ou false en cas d'échec
     */
    public function getDiscussionByIds($id_utilisateur, $id_utilisateur_1, $id_discussion) {
        try {
            $stmt = $this->bd->prepare("SELECT * FROM discussion WHERE id_utilisateur = :id_utilisateur 
                                       AND id_utilisateur_1 = :id_utilisateur_1 AND id_discussion = :id_discussion");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->bindParam(':id_utilisateur_1', $id_utilisateur_1, PDO::PARAM_INT);
            $stmt->bindParam(':id_discussion', $id_discussion, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

     /**
     * Obtenez les catégories pour un utilisateur
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur
     * @return array|false Informations sur la discussion ou false en cas d'échec
     */
    public function getCategoriessByUserId($id_utilisateur) {
        try {
            $stmt = $this->bd->prepare("
            SELECT c.nom_categorie
            FROM aexpertiseprofessionnelle ep
            JOIN categorie c ON ep.id_categorie = c.id_categorie
            WHERE ep.id_utilisateur = :id_utilisateur;   
            ");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();
            
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $categories ?: []; // Retourne le tableau des catégories ou un tableau vide s'il n'y en a pas
        } catch (PDOException $e) {
            return []; // En cas d'erreur, retourne un tableau vide
        }
    }

     /**
     * Obtenez les compétences de formateur pour un formmateur
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur
     * @return array|false Informations sur la discussion ou false en cas d'échec
     */
    public function getCompetencesFormateurById($id_utilisateur) {
        try {
            $stmt = $this->bd->prepare("
                SELECT c.nom_categorie, t.nom_theme, n.libelle_niveau
                FROM aExpertiseProfessionnelle aep
                JOIN theme t ON aep.id_theme = t.id_theme
                JOIN categorie c ON aep.id_categorie = c.id_categorie
                JOIN niveau n ON aep.id_niveau = n.id_niveau
                WHERE aep.id_utilisateur = :id_utilisateur
            ");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();
    
            $competences = array();
    
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $categorie = $row['nom_categorie'];
                $theme = $row['nom_theme'];
                $niveau = $row['libelle_niveau'];
    
                if (!isset($competences[$categorie])) {
                    $competences[$categorie] = array();
                }
    
                $competences[$categorie][] = array('theme' => $theme, 'niveau' => $niveau);
            }
    
            return $competences;
        } catch (PDOException $e) {
            return false;
        }
    }


    /**
     * Obtenez les informations du message par les identifiants d'utilisateurs, l'identifiant de discussion et l'identifiant de message.
     *
     * @param int $id_utilisateur_1 Identifiant de l'utilisateur 1
     * @param int $id_utilisateur_2 Identifiant de l'utilisateur 2
     * @param int $id_discussion Identifiant de la discussion
     * @param int $id_message Identifiant du message
     * @return array|false Informations sur le message ou false en cas d'échec
     */
    public function getMessageByIds($id_utilisateur_1, $id_utilisateur_2, $id_discussion, $id_message) {
        try {
            $stmt = $this->bd->prepare("SELECT * FROM message WHERE id_utilisateur_1 = :id_utilisateur_1 
                                       AND id_utilisateur_2 = :id_utilisateur_2 AND id_discussion = :id_discussion 
                                       AND id_message = :id_message");
            $stmt->bindParam(':id_utilisateur_1', $id_utilisateur_1, PDO::PARAM_INT);
            $stmt->bindParam(':id_utilisateur_2', $id_utilisateur_2, PDO::PARAM_INT);
            $stmt->bindParam(':id_discussion', $id_discussion, PDO::PARAM_INT);
            $stmt->bindParam(':id_message', $id_message, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

////// -pour controlleur formateurs- //////
 
    /**
     * Get the number of formateurs based on theme and/or categorie.
     *
     * @param int|null $themeId Theme ID (optional)
     * @param int|null $categorieId Categorie ID (optional)
     * @return int Number of formateurs
     */
    public function getNbFormateurByThemeOrCategorie($themeId = null, $categorieId = null) {
        try {
            $sql = "SELECT COUNT(DISTINCT f.id_utilisateur) AS formateur_count
                    FROM formateur f";

            if ($themeId !== null || $categorieId !== null) {
                $sql .= " INNER JOIN aExpertiseProfessionnelle aep ON f.id_utilisateur = aep.id_utilisateur";

                if ($themeId !== null) {
                    $sql .= " AND aep.id_theme = :themeId";
                }

                if ($categorieId !== null) {
                    $sql .= " INNER JOIN theme t ON aep.id_theme = t.id_theme
                             INNER JOIN categorie c ON t.id_categorie = c.id_categorie
                             WHERE c.id_categorie = :categorieId";
                }
            }

            $stmt = $this->bd->prepare($sql);

            if ($themeId !== null) {
                $stmt->bindParam(':themeId', $themeId, PDO::PARAM_INT);
            }

            if ($categorieId !== null) {
                $stmt->bindParam(':categorieId', $categorieId, PDO::PARAM_INT);
            }

            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    
    /**
     * Get the ID of a categorie by name.
     *
     * @param string $nom_categorie Categorie name
     * @return int|null Categorie ID or null if not found
     */
    public function getCategorieIdByName($nom_categorie) {
        try {
            $stmt = $this->bd->prepare("SELECT id_categorie FROM categorie WHERE nom_categorie = :nom_categorie");
            $stmt->bindParam(':nom_categorie', $nom_categorie, PDO::PARAM_STR);
            $stmt->execute();

            $categorieId = $stmt->fetchColumn();

            return ($categorieId !== false) ? (int) $categorieId : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Get the ID of a theme by name.
     *
     * @param string $nom_theme Theme name
     * @return int|null Theme ID or null if not found
     */
    public function getThemeIdByName($nom_theme) {
        try {
            $stmt = $this->bd->prepare("SELECT id_theme FROM theme WHERE nom_theme = :nom_theme");
            $stmt->bindParam(':nom_theme', $nom_theme, PDO::PARAM_STR);
            $stmt->execute();

            $themeId = $stmt->fetchColumn();

            return ($themeId !== false) ? (int) $themeId : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Get and display formateurs based on page, categorie, and theme.
     *
     * @param int $page Page number
     * @param int|null $categorieId Categorie ID (optional)
     * @param int|null $themeId Theme ID (optional)
     */
    public function getFormateursByPageAndCategoryOrTheme($page, $categorieId = null, $themeId = null) {
        try {
            $formateursPerPage = 5;
            $offset = ($page - 1) * $formateursPerPage;

            $sql = "SELECT DISTINCT f.id_utilisateur, f.linkedin, f.cv, f.date_signature, f.signature_electronique, f.total_heures
                    FROM formateur f
                    INNER JOIN aExpertiseProfessionnelle aep ON f.id_utilisateur = aep.id_utilisateur";

            if ($categorieId !== null) {
                $sql .= " INNER JOIN theme t ON aep.id_theme = t.id_theme
                         INNER JOIN categorie c ON t.id_categorie = c.id_categorie
                         WHERE c.id_categorie = :categorieId";
            }

            if ($themeId !== null) {
                $sql .= " WHERE aep.id_theme = :themeId";
            }

            $sql .= " LIMIT :formateursPerPage OFFSET :offset";

            $stmt = $this->bd->prepare($sql);

            if ($categorieId !== null) {
                $stmt->bindParam(':categorieId', $categorieId, PDO::PARAM_INT);
            }

            if ($themeId !== null) {
                $stmt->bindParam(':themeId', $themeId, PDO::PARAM_INT);
            }

            $stmt->bindParam(':formateursPerPage', $formateursPerPage, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Get the number of pages for formateurs based on categorie and theme.
     *
     * @param int|null $categorieId Categorie ID (optional)
     * @param int|null $themeId Theme ID (optional)
     * @return int Number of pages
     */
    public function getFormateurPagesByCategoryOrTheme($categorieId = null, $themeId = null) {
        try {
            $formateursPerPage = 5;

            $sql = "SELECT COUNT(DISTINCT f.id_utilisateur) AS formateur_count
                    FROM formateur f
                    INNER JOIN aExpertiseProfessionnelle aep ON f.id_utilisateur = aep.id_utilisateur";

            if ($categorieId !== null) {
                $sql .= " INNER JOIN theme t ON aep.id_theme = t.id_theme
                         INNER JOIN categorie c ON t.id_categorie = c.id_categorie
                         WHERE c.id_categorie = :categorieId";
            }

            if ($themeId !== null) {
                $sql .= " WHERE aep.id_theme = :themeId";
            }

            $stmt = $this->bd->prepare($sql);

            if ($categorieId !== null) {
                $stmt->bindParam(':categorieId', $categorieId, PDO::PARAM_INT);
            }

            if ($themeId !== null) {
                $stmt->bindParam(':themeId', $themeId, PDO::PARAM_INT);
            }

            $stmt->execute();

            $formateurCount = (int) $stmt->fetchColumn();

            $pages = ceil($formateurCount / $formateursPerPage);

            return $pages;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Get user information for a formateur by ID.
     *
     * @param int $id_utilisateur Formateur ID
     * @return array|false User information or false on failure
     */
    public function getFormateurUserInfoById($id_utilisateur) {
        try {
            $stmt = $this->bd->prepare("SELECT u.id_utilisateur, u.nom, u.prenom, u.password, u.mail, u.token,
                                              u.photo_de_profil, u.date_de_creation, u.mail_verifier,
                                              f.linkedin, f.cv, f.date_signature, f.signature_electronique, f.total_heures
                                       FROM utilisateur u
                                       JOIN formateur f ON u.id_utilisateur = f.id_utilisateur
                                       WHERE u.id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get formateurs' basic information by page and optional category or theme.
     *
     * @param int $page Page number
     * @param int|null $categoryId Category ID (optional)
     * @param int|null $themeId Theme ID (optional)
     * @return array|null Formateurs' basic information or null on failure
     */
    public function getFormateursBasicInfoByPageAndCategoryOrTheme($page, $categoryId = null, $themeId = null) {
        try {
            $formateursPerPage = 5;
            $offset = ($page - 1) * $formateursPerPage;

            $sql = "SELECT u.id_utilisateur, u.nom, u.prenom, u.mail, u.photo_de_profil, f.linkedin
                    FROM utilisateur u
                    INNER JOIN formateur f ON u.id_utilisateur = f.id_utilisateur
                    INNER JOIN aExpertiseProfessionnelle aep ON f.id_utilisateur = aep.id_utilisateur";

            if ($categoryId !== null) {
                $sql .= " INNER JOIN theme t ON aep.id_theme = t.id_theme
                        INNER JOIN categorie c ON t.id_categorie = c.id_categorie
                        WHERE c.id_categorie = :categoryId";
            }

            if ($themeId !== null) {
                $sql .= " WHERE aep.id_theme = :themeId";
            }

            $sql .= " LIMIT :formateursPerPage OFFSET :offset";

            $stmt = $this->bd->prepare($sql);

            if ($categoryId !== null) {
                $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            }

            if ($themeId !== null) {
                $stmt->bindParam(':themeId', $themeId, PDO::PARAM_INT);
            }

            $stmt->bindParam(':formateursPerPage', $formateursPerPage, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Get level data for a formateur by ID.
     *
     * @param int $id_utilisateur Formateur ID
     * @return array|false Level data or false on failure
     */
    public function getLevelDataById($id_utilisateur) {
        try {
            $stmt = $this->bd->prepare("SELECT aep.id_theme, aep.id_niveau, n.libelle_niveau
                                       FROM aExpertiseProfessionnelle aep
                                       JOIN niveau n ON aep.id_niveau = n.id_niveau
                                       WHERE aep.id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get pedagogical experience data for a formateur by ID.
     *
     * @param int $id_utilisateur Formateur ID
     * @return array|false Pedagogical experience data or false on failure
     */
    public function getPedagogicalExperienceDataById($id_utilisateur) {
        try {
            $stmt = $this->bd->prepare("SELECT aep.id_theme, t.nom_theme, aep.id_public, p.libelle_public,
                                              aep.volume_h_moyen_session, aep.nb_session_effectuee, aep.commentaire
                                       FROM aExpeirencePeda aep
                                       JOIN theme t ON aep.id_theme = t.id_theme
                                       JOIN public p ON aep.id_public = p.id_public
                                       WHERE aep.id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get categorie data for a formateur by ID.
     *
     * @param int $id_utilisateur Formateur ID
     * @return array|false Categorie data or false on failure
     */
    public function getCategorieDataById($id_utilisateur) {
        try {
            $stmt = $this->bd->prepare("SELECT DISTINCT c.id_categorie, c.nom_categorie, c.valide_categorie
                                       FROM categorie c
                                       JOIN aExpertiseProfessionnelle aep ON c.id_categorie = aep.id_categorie
                                       WHERE aep.id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get theme data for a formateur by ID.
     *
     * @param int $id_utilisateur Formateur ID
     * @return array|false Theme data or false on failure
     */
    public function getThemeDataById($id_utilisateur) {
        try {
            $stmt = $this->bd->prepare("SELECT DISTINCT t.id_theme, t.nom_theme, c.id_categorie, c.nom_categorie, t.valide_theme
                                       FROM theme t
                                       JOIN categorie c ON t.id_categorie = c.id_categorie
                                       JOIN aExpertiseProfessionnelle aep ON t.id_theme = aep.id_theme
                                       WHERE aep.id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get expertise data for a formateur by ID.
     *
     * @param int $id_utilisateur Formateur ID
     * @return array|false Expertise data or false on failure
     */
    public function getExpertiseDataById($id_utilisateur) {
        try {
            $stmt = $this->bd->prepare("SELECT aep.id_theme, t.nom_theme, aep.duree_experience, aep.commentaire, aep.id_niveau
                                       FROM aExpertiseProfessionnelle aep
                                       JOIN theme t ON aep.id_theme = t.id_theme
                                       WHERE aep.id_utilisateur = :id_utilisateur");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }


    /**
     * Get formateurs' basic information by page and optional category or theme.
     *
     * @param int $page Page number
     * @param int|null $categoryId Category ID (optional)
     * @param int|null $themeId Theme ID (optional)
     * @return array|null Formateurs' basic information with additional details or null on failure
     */
    public function getFormateursBasicInfoByPageAndCategoryOrTheme2($page, $categoryId = null, $themeId = null) {
        try {
            $formateursPerPage = 5;
            $offset = ($page - 1) * $formateursPerPage;

            $sql = "SELECT u.id_utilisateur, u.nom, u.prenom, u.mail, u.photo_de_profil, f.linkedin, n.libelle_niveau, aep.commentaire, c.nom_categorie, t.nom_theme
                    FROM utilisateur u
                    INNER JOIN formateur f ON u.id_utilisateur = f.id_utilisateur
                    INNER JOIN aExpertiseProfessionnelle aep ON f.id_utilisateur = aep.id_utilisateur
                    LEFT JOIN niveau n ON aep.id_niveau = n.id_niveau
                    LEFT JOIN theme t ON aep.id_theme = t.id_theme
                    LEFT JOIN categorie c ON t.id_categorie = c.id_categorie";

            if ($categoryId !== null) {
                $sql .= " WHERE c.id_categorie = :categoryId";
            } elseif ($themeId !== null) {
                $sql .= " WHERE aep.id_theme = :themeId";
            }

            $sql .= " LIMIT :formateursPerPage OFFSET :offset";

            $stmt = $this->bd->prepare($sql);

            if ($categoryId !== null) {
                $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            } elseif ($themeId !== null) {
                $stmt->bindParam(':themeId', $themeId, PDO::PARAM_INT);
            }

            $stmt->bindParam(':formateursPerPage', $formateursPerPage, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Add a message to a discussion.
     *
     * @param string $texte Message text
     * @param int $id_utilisateur_1 Sender's user ID
     * @param int $id_utilisateur_2 Receiver's user ID
     * @param int $id_discussion Discussion ID
     * @param bool $validation_moderation Moderation validation status
     * @return bool True on success, false on failure
     */
    public function addMessageToDiscussion($texte, $id_utilisateur_1, $id_utilisateur_2, $id_discussion, $validation_moderation, $senderId) {
        try {
            $stmt = $this->bd->prepare("INSERT INTO message (id_utilisateur_1, id_utilisateur_2, id_discussion, texte, date_heure, validation_moderation, lu, id_utilisateur)
                                       VALUES (:id_utilisateur_1, :id_utilisateur_2, :id_discussion, :texte, NOW(), :validation_moderation, :lu, :senderId)");
            $lu = false;
            $stmt->bindParam(':id_utilisateur_1', $id_utilisateur_1, PDO::PARAM_INT);
            $stmt->bindParam(':id_utilisateur_2', $id_utilisateur_2, PDO::PARAM_INT);
            $stmt->bindParam(':id_discussion', $id_discussion, PDO::PARAM_INT);
            $stmt->bindParam(':texte', $texte, PDO::PARAM_STR);
            $stmt->bindParam(':validation_moderation', $validation_moderation, PDO::PARAM_BOOL);
            $stmt->bindParam(':lu', $lu, PDO::PARAM_BOOL);
            $stmt->bindParam(':senderId', $senderId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            //echo 'Erreur : ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Récupérer toutes les informations d'une discussion par son identifiant.
     *
     * @param int $id_discussion Identifiant de la discussion
     * @return array|null Informations de la discussion ou null en cas d'erreur
     */
    public function getDiscussionById($id_discussion) {
        try {
            // Prépare la requête pour récupérer les informations de la discussion
            $stmt = $this->bd->prepare("SELECT * FROM discussion WHERE id_discussion = :id_discussion");

            $stmt->bindParam(':id_discussion', $id_discussion, PDO::PARAM_INT);
            $stmt->execute();

            $discussion = $stmt->fetch(PDO::FETCH_ASSOC);

            return $discussion;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function countUnreadMessages($userId, $discussionId) {
        try {
            $stmt = $this->bd->prepare("SELECT COUNT(*) FROM message WHERE id_utilisateur = :userId AND id_discussion = :discussionId AND lu = FALSE");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':discussionId', $discussionId, PDO::PARAM_INT);
            $stmt->execute();
    
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0; // En cas d'erreur, retourner 0
        }
    }

    /**
     * Récupérer l'heure du dernier message et son statut de lecture pour un utilisateur spécifique dans une discussion.
     *
     * @param int $userId Identifiant de l'utilisateur
     * @param int $discussionId Identifiant de la discussion
     * @return array|bool Tableau contenant l'heure du dernier message et son statut de lecture pour l'utilisateur spécifié, ou false en cas d'erreur
     */
    public function getLastMessageInfo($userId, $discussionId) {
        try {
            // Prépare la requête pour récupérer l'heure du dernier message et son statut de lecture pour un utilisateur spécifique
            $stmt = $this->bd->prepare("SELECT date_heure, lu FROM message 
                                        WHERE id_utilisateur = :userId
                                        AND id_discussion = :discussionId 
                                        ORDER BY date_heure DESC 
                                        LIMIT 1");

            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':discussionId', $discussionId, PDO::PARAM_INT);
            $stmt->execute();

            $lastMessage = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($lastMessage) {
                $lastMessageInfo = array(
                    'date_heure' => $lastMessage['date_heure'],
                    'lu' => (bool) $lastMessage['lu'],
                );

                return $lastMessageInfo;
            } else {
                // Aucun message trouvé dans la discussion pour l'utilisateur spécifié
                return false;
            }
        } catch (PDOException $e) {
            return false; // En cas d'erreur, retourner false
        }
    }


    
    /**
     * Récupérez toutes les catégories et thèmes.
     *
     * @return array|null Tableau contenant les catégories et thèmes ou null en cas d'échec
     */
    public function getAllCategories() {
        try {
            $stmt = $this->bd->prepare("SELECT * FROM categorie");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $categories;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Get all themes by category ID.
     *
     * @param int $categoryId Category ID
     * @return array|null Array of themes or null in case of failure
     */
    public function getThemesByCategoryId($categoryId) {
        try {
            $stmt = $this->bd->prepare("SELECT * FROM theme WHERE id_categorie = :id_categorie");
            $stmt->bindParam(':id_categorie', $categoryId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Get formateurs' basic information by page and optional category or theme.
     *
     * @param int $page Page number
     * @param int|null $categoryId Category ID (optional)
     * @param array|null $themeIds Array of Theme IDs (optional)
     * @return array|null Formateurs' basic information or null on failure
     */
    public function getFormateursBasicInfoByPageAndCategoryOrTheme3($page, $categoryId = null, $themeIds = null) {
        try {
        $formateursPerPage = 5;
        $offset = ($page - 1) * $formateursPerPage;

        $sql = " SELECT DISTINCT u.id_utilisateur,aep.commentaire, c.nom_categorie,t.nom_theme ,u.nom, u.prenom,u.photo_de_profil
        FROM aExpertiseProfessionnelle aep
        JOIN utilisateur u ON aep.id_utilisateur = u.id_utilisateur
        JOIN theme t ON aep.id_theme = t.id_theme
        ";

        if ($categoryId !== null) {
            $sql .= " JOIN categorie c ON aep.id_categorie = c.id_categorie
            WHERE c.id_categorie = :categoryId";
        }

        if ($themeIds !== null) {
            $themeIdsParam = implode(",", $themeIds);
            $sql .= " AND aep.id_theme IN ($themeIdsParam)";
        }

        $sql .= " LIMIT :formateursPerPage OFFSET :offset";

        $stmt = $this->bd->prepare($sql);

        if ($categoryId !== null) {
            $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        }

        $stmt->bindValue(':formateursPerPage', $formateursPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute(['categoryId' => $categoryId, 'formateursPerPage' => $formateursPerPage, 'offset' => $offset]);

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $e) {
        return null;
    }
    }

       /**
     * Récupérez tous les thèmes.
     *
     * @return array|null Tableau contenant thèmes ou null en cas d'échec
     */
    public function getAllNiveaux() {
        try {
            $stmt = $this->bd->prepare("SELECT * FROM niveau");
            $stmt->execute();
            $niveaux = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $niveaux;
        } catch (PDOException $e) {
            return null;
        }
    }
    /**
     * Récupérez tous les thèmes.
     *
     * @return array|null Tableau contenant thèmes ou null en cas d'échec
     */
    public function getAllThemes() {
        try {
            $stmt = $this->bd->prepare("SELECT * FROM theme");
            $stmt->execute();
            $themes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $themes;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Get the number of pages for formateurs based on categorie and theme.
     *
     * @param int|null $categorieId Categorie ID (optional)
     * @param array|null $themeIds Array of Theme IDs (optional)
     * @return int Number of pages
     */
    public function getFormateurPagesByCategoryOrTheme3($categorieId = null, $themeIds = null) {
        try {
            $formateursPerPage = 5;

            $sql = "SELECT COUNT(DISTINCT f.id_utilisateur) AS formateur_count
                    FROM formateur f
                    INNER JOIN aExpertiseProfessionnelle aep ON f.id_utilisateur = aep.id_utilisateur";

            if ($categorieId !== null) {
                $sql .= " INNER JOIN theme t ON aep.id_theme = t.id_theme
                        INNER JOIN categorie c ON t.id_categorie = c.id_categorie
                        WHERE c.id_categorie = :categorieId";
            }

            if ($themeIds !== null) {
                $themeIdsParam = implode(",", $themeIds);
                $sql .= " AND aep.id_theme IN ($themeIdsParam)";
            }

            $stmt = $this->bd->prepare($sql);

            if ($categorieId !== null) {
                $stmt->bindParam(':categorieId', $categorieId, PDO::PARAM_INT);
            }

            $stmt->execute();

            $formateurCount = (int) $stmt->fetchColumn();

            $pages = ceil($formateurCount / $formateursPerPage);

            return $pages;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Get the number of formateurs based on theme and/or categorie.
     *
     * @param array|null $themeIds Array of Theme IDs (optional)
     * @param int|null $categorieId Categorie ID (optional)
     * @return int Number of formateurs
     */
    public function getNbFormateurByThemeOrCategorie3($themeIds = null, $categorieId = null) {
        try {
            $sql = "SELECT COUNT(DISTINCT f.id_utilisateur) AS formateur_count
                    FROM formateur f";

            if ($themeIds !== null || $categorieId !== null) {
                $sql .= " INNER JOIN aExpertiseProfessionnelle aep ON f.id_utilisateur = aep.id_utilisateur";

                if ($themeIds !== null) {
                    $themeIdsParam = implode(",", $themeIds);
                    $sql .= " AND aep.id_theme IN ($themeIdsParam)";
                }

                if ($categorieId !== null) {
                    $sql .= " INNER JOIN theme t ON aep.id_theme = t.id_theme
                            INNER JOIN categorie c ON t.id_categorie = c.id_categorie
                            WHERE c.id_categorie = :categorieId";
                }
            }

            $stmt = $this->bd->prepare($sql);

            if ($themeIds !== null) {
                // No need to bind theme IDs individually; handled in the SQL query
            }

            if ($categorieId !== null) {
                $stmt->bindParam(':categorieId', $categorieId, PDO::PARAM_INT);
            }

            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Get the category name and names of all themes an user possesses.
     *
     * @param int $id_utilisateur User ID
     * @param int $categorie_id Category ID
     * @return array|null Array with category name and theme names or null on failure
     */
    public function getUserCategoryAndThemes($id_utilisateur, $categorie_id) {
        try {
            $sql = "SELECT c.nom_categorie, ARRAY_AGG(t.nom_theme) AS theme_names,
                    ARRAY_AGG(aep.commentaire) AS expertise_comment
                    FROM aExpertiseProfessionnelle aep
                    INNER JOIN theme t ON aep.id_theme = t.id_theme
                    INNER JOIN categorie c ON t.id_categorie = c.id_categorie
                    WHERE aep.id_utilisateur = :id_utilisateur AND c.id_categorie = :categorie_id
                    GROUP BY c.nom_categorie";

            $stmt = $this->bd->prepare($sql);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->bindParam(':categorie_id', $categorie_id, PDO::PARAM_INT);

            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Vérifie si une discussion existe déjà entre un client et un formateur.
     *
     * @param int $id_utilisateur Identifiant du client
     * @param int $id_utilisateur\_1 Identifiant du formateur
     * @return int|bool L'identifiant de la discussion si elle existe, ou false sinon
     */
    public function isDiscussionExist($id_utilisateur, $id_utilisateur_1) {
        try {
            $stmt = $this->bd->prepare("SELECT id_discussion FROM discussion 
                                    WHERE id_utilisateur = :id_utilisateur AND id_utilisateur_1 = :id_utilisateur_1");
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->bindParam(':id_utilisateur_1', $id_utilisateur_1, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return $result['id_discussion'];
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Commence une discussion entre un client et un formateur.
     *
     * @param int $id_utilisateur Identifiant du client
     * @param int $id_utilisateur\_1 Identifiant du formateur
     * @return int L'identifiant de la discussion
     */
    public function startDiscussion($id_utilisateur, $id_utilisateur_1) {
        if (!$this->isDiscussionExist($id_utilisateur, $id_utilisateur_1)) {
            try {
                $stmt = $this->bd->prepare("INSERT INTO discussion (id_utilisateur, id_utilisateur_1) 
                                        VALUES (:id_utilisateur, :id_utilisateur_1)");
                $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
                $stmt->bindParam(':id_utilisateur_1', $id_utilisateur_1, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    return $this->bd->lastInsertId();
                } else {
                    return false;
                }
            } catch (PDOException $e) {
                return false;
            }
        } else {
            return $this->isDiscussionExist($id_utilisateur, $id_utilisateur_1);
        }
    }

    /**
     * Obtenir la liste des formateurs avec leur statut de modérateur (excluant les administrateurs).
     *
     * @return array|bool Retourne un tableau contenant les informations des formateurs ou false en cas d'erreur.
     */
    public function listeFormateursAvecStatutModerateur()
    {
        try {
            $stmt = $this->bd->prepare("SELECT u.id_utilisateur, u.nom, u.prenom, 
                                            CASE WHEN m.id_moderateur IS NOT NULL THEN 1 ELSE 0 END AS est_moderateur
                                        FROM utilisateur u
                                        LEFT JOIN moderateur m ON u.id_utilisateur = m.id_utilisateur_1
                                        WHERE u.id_utilisateur IN (SELECT id_utilisateur FROM formateur)
                                          AND u.id_utilisateur NOT IN (SELECT id_utilisateur FROM admin)");
    
            $stmt->execute();
    
            $formateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            return $formateurs;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Valide un message en mettant à jour la colonne validation_moderation à TRUE.
     *
     * @param int $id_message Identifiant du message à valider.
     * @return mixed Retourne l'id_discussion si la validation réussit, sinon retourne false.
     */
    public function validateMessage($id_message) {
        try {
            // Récupérer l'id_discussion avant la mise à jour
            $stmtSelect = $this->bd->prepare("SELECT id_discussion FROM message WHERE id_message = :id_message");
            $stmtSelect->bindParam(':id_message', $id_message, PDO::PARAM_INT);
            $stmtSelect->execute();
            $id_discussion = $stmtSelect->fetchColumn();

            // Mettre à jour la validation_moderation
            $stmtUpdate = $this->bd->prepare("UPDATE message SET validation_moderation = TRUE WHERE id_message = :id_message");
            $stmtUpdate->bindParam(':id_message', $id_message, PDO::PARAM_INT);
            $stmtUpdate->execute();

            // Retourner l'id_discussion
            return $id_discussion;
        } catch (PDOException $e) {
            //echo 'Erreur : ' . $e->getMessage();
            return false;
        }
    }



    /**
     * Récupérer toutes les discussions avec les noms et prénoms du client et du formateur
     * 
     * @return array Liste des discussions avec les détails
     */
    public function recupererToutesDiscussions(){
        try{
            // Préparation de la requête
            $stmt = $this->bd->prepare("
                SELECT 
                    d.id_discussion,
                    u1.nom AS nom_client,
                    u1.prenom AS prenom_client,
                    u2.nom AS nom_formateur,
                    u2.prenom AS prenom_formateur
                FROM 
                    discussion d
                INNER JOIN 
                    utilisateur u1 ON d.id_utilisateur = u1.id_utilisateur
                INNER JOIN 
                    utilisateur u2 ON d.id_utilisateur_1 = u2.id_utilisateur
            ");

            // Exécution de la requête
            $stmt->execute();

            // Récupération des résultats
            $tab = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $tab;
        } catch  (PDOException $e) {
            return null;
        }
    }

    /**
     * Récupérer tous les utilisateurs qui ne sont pas dans la table Affranchis
     * 
     * @return array Liste des utilisateurs non affranchis
     */
    public function recupererUtilisateursNonAffranchis(){
        try {
            // Préparation de la requête
            $stmt = $this->bd->prepare("
                SELECT *
                FROM utilisateur u
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM Affranchis a
                    WHERE a.id_utilisateur = u.id_utilisateur
                )
            ");

            // Exécution de la requête
            $stmt->execute();

            // Récupération des résultats
            $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $utilisateurs;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Affranchir un utilisateur
     * 
     * @param int $id_moderateur L'ID du modérateur
     * @param int $id_utilisateur L'ID de l'utilisateur à affranchir
     * @return bool True si l'affranchissement est réussi, False sinon
     */
    public function affranchirUtilisateur($id_moderateur, $id_utilisateur){
        try {
            // Préparation de la requête
            $stmt = $this->bd->prepare("
                INSERT INTO Affranchis (id_moderateur, id_utilisateur)
                VALUES (:id_moderateur, :id_utilisateur)
            ");

            // Liaison des paramètres
            $stmt->bindParam(':id_moderateur', $id_moderateur, PDO::PARAM_INT);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

            // Exécution de la requête
            $resultat = $stmt->execute();

            return $resultat;
        } catch (PDOException $e) {
            //echo 'Erreur : ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Récupère la liste des activités.
     *
     * @return array|bool Tableau d'activités ou false en cas d'échec
     */
    public function getActivitiesList() {
        try {
            $stmt = $this->bd->query("SELECT * FROM activite");
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $activities;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Récupère une activité à partir de son ID.
     *
     * @param int $id_activite Identifiant de l'activité
     * @return array|bool Tableau contenant les informations de l'activité ou false en cas d'échec
     */
    public function getActivityById($id_activite) {
        try {
            $stmt = $this->bd->prepare("SELECT * FROM activite WHERE id_activite = :id_activite");
            $stmt->bindParam(':id_activite', $id_activite, PDO::PARAM_INT);
            $stmt->execute();

            $activity = $stmt->fetch(PDO::FETCH_ASSOC);

            return $activity !== false ? $activity : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Met à jour l'email de l'utilisateur.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur
     * @param string $nouvelle_email Nouvelle adresse email
     * @return bool True si la mise à jour réussit, sinon false
     */
    public function updateEmail($id_utilisateur, $nouvelle_email){
        try {
            // Prépare la requête
            $stmt = $this->bd->prepare("UPDATE utilisateur SET mail = :nouvelle_email WHERE id_utilisateur = :id_utilisateur");

            // Lie les paramètres
            $stmt->bindParam(':nouvelle_email', $nouvelle_email, PDO::PARAM_STR);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

            // Exécute la requête
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Met à jour le mot de passe de l'utilisateur.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur
     * @param string $nouveau_mot_de_passe Nouveau mot de passe (non haché)
     * @return bool True si la mise à jour réussit, sinon false
     */
    public function updatePassword($id_utilisateur, $nouveau_mot_de_passe){
        try {
            // Hash le nouveau mot de passe
            $hashedPassword = password_hash($nouveau_mot_de_passe, PASSWORD_DEFAULT);

            // Prépare la requête
            $stmt = $this->bd->prepare("UPDATE utilisateur SET password = :nouveau_mot_de_passe WHERE id_utilisateur = :id_utilisateur");

            // Lie les paramètres
            $stmt->bindParam(':nouveau_mot_de_passe', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

            // Exécute la requête
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }


    /**
     * Met à jour la société de l'utilisateur. Si aucune société n'existe, insère une nouvelle société.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur
     * @param string $nouvelle_societe Nouvelle société
     * @return bool True si la mise à jour réussit, sinon false
     */
    public function updateSociete($id_utilisateur, $nouvelle_societe){
        try {
            // Vérifie si une société existe déjà pour l'utilisateur
            $societeExistante = $this->getClientById($id_utilisateur);

            if ($societeExistante === FALSE) {
                // Aucune société existante, insère une nouvelle société
                $stmtInsert = $this->bd->prepare("INSERT INTO client (id_utilisateur, societe) VALUES (:id_utilisateur, :nouvelle_societe)");

                // Lie les paramètres pour l'insertion
                $stmtInsert->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
                $stmtInsert->bindParam(':nouvelle_societe', $nouvelle_societe, PDO::PARAM_STR);

                // Exécute l'insertion
                $stmtInsert->execute();
            } else {
                // Met à jour la société existante
                $stmtUpdate = $this->bd->prepare("UPDATE client SET societe = :nouvelle_societe WHERE id_utilisateur = :id_utilisateur");

                // Lie les paramètres pour la mise à jour
                $stmtUpdate->bindParam(':nouvelle_societe', $nouvelle_societe, PDO::PARAM_STR);
                $stmtUpdate->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

                // Exécute la mise à jour
                $stmtUpdate->execute();
            }

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Met à jour le profil LinkedIn de l'utilisateur.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur
     * @param string $nouveau_linkedin Nouveau profil LinkedIn
     * @return bool True si la mise à jour réussit, sinon false
     */
    public function updateLinkedIn($id_utilisateur, $nouveau_linkedin){
        try {
            // Met à jour LinkedIn
            $stmtUpdate = $this->bd->prepare("UPDATE formateur SET linkedin = :nouveau_linkedin WHERE id_utilisateur = :id_utilisateur");

            // Lie les paramètres pour la mise à jour
            $stmtUpdate->bindParam(':nouveau_linkedin', $nouveau_linkedin, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

            // Exécute la mise à jour
            $stmtUpdate->execute();

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Met à jour le CV de l'utilisateur.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur
     * @param string $nouveau_cv Nouveau CV
     * @return bool True si la mise à jour réussit, sinon false
     */
    public function updateCV($id_utilisateur, $nouveau_cv){
        try {
            // Met à jour le CV
            $stmtUpdate = $this->bd->prepare("UPDATE formateur SET cv = :nouveau_cv WHERE id_utilisateur = :id_utilisateur");

            // Lie les paramètres pour la mise à jour
            $stmtUpdate->bindParam(':nouveau_cv', $nouveau_cv, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

            // Exécute la mise à jour
            $stmtUpdate->execute();

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function change_mdp($email,$mdp){
        try{
        $mdp = $this->bd->prepare("UPDATE utilisateur SET password = :mdp WHERE email = :email");
        $mdp->bindParam(':email',$email,PDO::PARAM_STR);
        $mdp->bindParam(':mdp',$mdp,PDO::PARAM_STR);
        $mdp->execute();
        return true;
        }catch (PDOException $e) {
            return false;
        }
    }

    

    function generateRandomPassword($length = 12) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomPassword = '';
    for ($i = 0; $i < $length; $i++) {
        $randomPassword .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomPassword;
    }

    
}
