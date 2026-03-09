<?php

    // Démarre une session PHP afin de stocker des informations utilisateur
    // (id, login, rôle, etc.) après la connexion.
    session_start();

    // Inclusion de la connexion PDO à la base de données.
   $pdo = require 'config/db.php';

    // Inclusion de la classe CsrfToken qui gère la création et la vérification des tokens
    require_once 'classes/csrfToken.php';

    // Variable utilisée pour afficher un message d'erreur
    $error = '';

    // -------- GENERATION TOKEN CSRF --------
    // On génère un token unique stocké en session.
    // S'il existe déjà, on le garde, sinon on le crée.
    // Cela évite de générer un nouveau token à chaque rafraîchissement.
    // Un CSRF token est une clé secrète, unique et stocké en session. 
    // Il permet d'éviter les attaques visant à pousser un utilisateur à envoyer une requête à son insu.

    if (!isset($_SESSION['csrf_token'])) {

        // Appel de la fonction generateToken de la classe csrfToken
        $csrf_token = CsrfToken::generateToken();  
    }

    // Traitement du formulaire 
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // -------- VERIFICATION DU TOKEN CSRF --------
        // On compare le token envoyé dans le formulaire avec celui stocké dans la session.
        // Si les deux ne correspondent pas → tentative d'attaque CSRF !

        if (!isset($_POST['csrf_token']) || !CsrfToken::checkToken($_POST['csrf_token'])) {
            
            $error = "Erreur de sécurité : token CSRF invalide.";

        } else {

            // Token valide : on peut traiter le login
            $login = trim($_POST['login'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($login === '' || $password === '') {
                $error = 'Veuillez remplir tous les champs';
            } else {
                // Prépare une requête SQL sécurisée pour éviter les injections SQL
                // LIMIT 1 permet d'éviter de parcourir toute la table inutilement.
                $stmt = $pdo->prepare('SELECT * FROM users WHERE user_login = :login LIMIT 1');
                $stmt->execute([':login' => $login]);
                $user = $stmt->fetch();

                // Vérifie si un utilisateur a été trouvé ET si le mot de passe est correct
                // password_verify compare le mot de passe entrant avec le hash stocké en base
                if ($user && password_verify($password, $user['user_password'])) {

                    // En cas de succès, on stocke dans la session les informations utiles
                    $_SESSION['user_id']        = $user['user_id'];
                    $_SESSION['user_login']     = $user['user_login'];
                    $_SESSION['user_firstname'] = $user['user_firstname'];
                    $_SESSION['user_role']      = $user['user_role'];

                    // Très important : régénère l'ID de session immédiatement après connexion tout en conservant les valeurs de session.
                    // Permet d’éviter les attaques de "session fixation" (un attaquant qui force un ID avant login)
                    session_regenerate_id(true);

                    // Redirection vers la page principale après connexion
                    header('Location: index.php');
                    exit;
                } else {
                    $error = "Login ou mot de passe incorrect.";
                }
            }
        }
    }

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <h1 class="title">Connexion</h1>

    <!-- Affiche le message d'erreur s'il existe -->
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <!-- Affiche des messages spéciaux -->
     <?php 
        // Si l'utilisateur a été redirigé à cause d'une inactivité prolongée
        if (isset($_GET['timeout'])) {
            echo "<p class='alert'> Votre session a expirée après 10 minutes d'inactivité. Veuillez vous reconnecter.</p>";
        }

        // Si la session a été détruite à cause d'un changement d'adresse IP ou de navigateur
        if (isset($_GET['security'])) {
            echo "<p class='alert'> votre session a été réinitialisée. Veuillez vous reconnecter. </p>";
        }

        // Si un message de session existe (par exemple : "Veuillez vous connecter !")
        if (isset($_SESSION['message'])) {
            echo "<p class='alert alert-warning'>" . htmlspecialchars($_SESSION['message']) . "</p>";
            unset($_SESSION['message']); // On le supprime pour ne pas l'afficher à nouveau
        }
     ?>

    <!-- Formulaire pour saisir le login et le mot de passe et se connecter -->
    <form method="post" action="">
        <div class="connexion">

        <div class="login">
            <label for="login" class="label_login">Login</label>
            <!-- Ici htmlspecialchars évite l'injection HTML dans la valeur affichée -->
            <input type="text" id="login" name="login" class="input_login" required value="<?= htmlspecialchars($login ?? '') ?>">
        </div>

        <div class="password">
            <label for="password" class="label_password">Mot de passe</label>
            <input type="password" id="password" name="password" class="input_password" required>
        </div>

        <!-- Token CSRF pour sécuriser le formulaire -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    </div>    
        <button type="submit" class="btn-connection">Se connecter</button>
    </form>
</body>
</html>
