<?php 

    /**
     * ---------------------------------------------------------
     * FICHIER : authentification.php
     * ---------------------------------------------------------
     * Vérifie que l’utilisateur est bien connecté,
     * que sa session est encore valide (non expirée par inactivité),
     * et qu’elle n’a pas été détournée (même IP + navigateur).
     * 
     * À inclure en haut de toutes les pages nécessitant une authentification.
     * ---------------------------------------------------------
     */

    // -------------- DÉMARRAGE DE LA SESSION EN MODE SÉCURISÉ --------------

    if (session_status() == PHP_SESSION_NONE) {
        // Protection renforcée contre le vol de session
        session_start([
            'cookie_httponly' => true,  // Empêche l'accès aux cookies depuis JS
            'cookie_secure' => isset($_SERVER['HTTPS']),    // Seulement en HTTPS
            'use_strict_mode' => true,      // Empêche l'utilisation d'un ancien ID de session
            'cookie_samesite' => 'Strict'       // Evite le vol de session via requêtes externes
        ]);
    }

    // ------- MISE EN PLACE DE LA DECONNEXION POUR INNACTIVITE -------

    // Durée d'inactivité maximale en secondes, ici 10 minutes.
    $timeout_duration = 600;

    // Vérifie si la variable de temps existe, sinon cela la créer
    if(isset($_SESSION['LAST_ACTIVITY']) &&
    (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
        // La session est expirée: on la détruit et on redirige

        session_unset();        // Suppression de toutes les variables de session
        session_destroy();      // Destruction de la session elle-même
        header("Location: login.php?timeout=1");    // Redirection vers la page de connexion
        exit;
    }

     // Mise à jour du timestamp de la dernière activité
    $_SESSION['LAST_ACTIVITY'] = time();

    // -------------- VÉRIFICATION DE L’INTÉGRITÉ DE LA SESSION (IP + NAVIGATEUR) --------------
    
    // Si la session ne contient pas encore les infos IP et navigateur :
    if (!isset($_SESSION['user_ip'])) {

        // Enregistrement des informations lors de la première activité 

        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';       // Adresse IP actuelle
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';     // Chaîne identifiant le navigateur
    } else {

        // Si les informations existent déjà, on vérifie qu'elles correspondent toujours à la session actuelle
        if ( $_SESSION['user_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '') || $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? ''))
        {
            // Si les informations ne concordent pas il est possible que quelqu'un tente d'utiliser un cookie volé depuis un autre appareil
            session_unset();    // Suppressin de toutes les données de session
            session_destroy();      // On détruit complètement la session
            header("Location: login.php?security=1");   // Redirection vers la page de connexion avec un paramètre de sécurité
            exit;
        }
    }

    // -------------- CONTRÔLE DE LA CONNEXION UTILISATEUR --------------

    // Si l'utilisateur n'est pas connecté (aucune variable de session user_login définie)
    if (!isset($_SESSION['user_login'])) {
        $_SESSION['message'] = 'Veuillez vous connecter !'; // Message d'erreur à afficher sur la page de login
        header("Location: login.php");      // Redirection vers la page de connexion
        exit();
    }

    




   

    
?>