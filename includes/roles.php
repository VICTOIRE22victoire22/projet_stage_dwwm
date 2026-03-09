<?php 

    // Fichier centralisant les fonctions liées aux rôles utilisateur

    // ------ FONCTION DE VERIFICATION DE CONNEXION ET DE RENVOIE DU ROLE ------
    function getUserRole(): ?string 
    {
        return $_SESSION['user_role'] ?? null;
    }

    // ------ FONCTIONS DE VERIFICATION DU ROLE ACTUEL ------
    function isUser(): bool 
    {
        return getUserRole() === 'user';
    }

    function isAdmin(): bool 
    {
        return getUserRole() === 'admin';
    }

    function isSuperAdmin(): bool
    {
        return getUserRole() === 'super-admin';
    }

    // ------ FONCTIONS GENERIQUE DE RESTRICTION A L'ACCES A UNE PAGE OU UNE ACTION ------
    function authorize(array $roles_autorises): void
    {
        $role = getUserRole();

        if(!$role || !in_array($role, $roles_autorises, true))
        {
            $_SESSION['message'] = "Accès refusé.";
            header("Location: index.php");
            exit;
        }
    }
?>




