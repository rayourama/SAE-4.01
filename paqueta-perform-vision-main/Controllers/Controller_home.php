<?php

/**
 * Controller_home - Classe permettant de gérer l'accueil du site web et plus précisémment les activités.
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

class Controller_home extends Controller
{
    public function action_default()
    {
        $this->action_home();
    }

    /**
     * Action pour afficher la vue home.
     *
     * Cette fonction récupère le modèle pour interagir avec les données et récupère la liste des activités depuis le tableau data.
     *
     * @return void
     */
    public function action_home()
    {
        $model = Model::getModel();

        $data = [
            'activites' => $model->getActivitiesList()
        ];

        $this->render('home', $data);
    }

    /**
     * Action pour afficher les détails d'une activité.
     *
     * Cette fonction récupère le modèle pour interagir avec les données et récupère l'ID de l'activité depuis les paramètres GET.
     *
     * @return void
     */
    public function action_activite()
    {
        // Obtient le modèle pour interagir avec les données
        $model = Model::getModel();

        // Récupère l'ID de l'activité depuis les paramètres GET
        $id_activite = isset($_GET['id']) ? e($_GET['id']) : null;

        if (!$id_activite) {
            // Si aucun ID d'activité n'est fourni, retourne à la page d'accueil
            $data = [
                'activites' => $model->getActivitiesList()
            ];
            $this->render('home', $data);
            return;
        }

        // Récupère les détails de l'activité par ID
        $data = [
            'activite' => $model->getActivityById($id_activite)
        ];

        // Rend la vue 'activite' avec les données
        $this->render('activite', $data);
    }
}
