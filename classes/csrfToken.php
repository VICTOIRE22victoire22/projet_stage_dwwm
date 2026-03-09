<?php

class CsrfToken
{
    // -------- FONCTION DE GENERATION D'UN TOKEN CSRF --------
    // Génère et retourne un token CSRF unique
    public static function generateToken():string
    {
        // Si pas de session on en démarre une
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si pas de token CSRF dans la session, on le génère.
        if (empty($_SESSION['csrf_token'])) {

            // bin2hex retourne une chaîne dont tous les caractères sont représentés par leur équivalent hexadécimal.
            // random_bytes() génère une chaîne contenant des octets aléatoires uniformément sélectionnés avec la valeur de length.
            // Ici la longeur (length) est de 32. 
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Retourne le token
        return $_SESSION['csrf_token'];
    }

    // -------- FONCTION DE VERIFICATION D'UN TOKEN CSRF --------
    // Vérifie si un token fourni correspond à celui stocké en session
    public static function checkToken(string $token): bool
    {
        // Si pas de session on en démarre une
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // hash_equals vérifie si deux chaînes de caractères sont égales sans divulguer d'informations sur le contenu
        // via le temps d'éxécution
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    // -------- FONCTIONS DE GENERATION ET VERIFICATION DE TOKENS POUR LES PAGES AVEC CRUD DE PLUSIEURS TABLES --------
    // Ces fonctions sont utilisés pour gérer les tokens de phone_line et de sda_number
    public static function generateNamedToken(string $name): string 
    {
        // Si pas de session on en démarre une
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si pas de token CSRF dans la session, on le génère.
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf'][$name] = $token;

        // Retourne le token
        return $token;
    }

    public static function checkNamedToken(string $name, string $token): bool 
    {
        // Si pas de session on en démarre une
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf'][$name])) {
            return false;
        }

        // Vérifie que les chaînes correspondent
        $isValid = hash_equals($_SESSION['csrf'][$name], $token);

        // Retourne le token
        return $isValid;
    }
}