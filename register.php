<?php

// Active l'affichage des erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarre une session utilisateur
session_start();

require_once 'config/db.php'; // Connexion à la base de données
$message = '';  // Commentaire simple
$errors = [];  // Crée un tableau vide dans la variable $errors pour stocker les erreurs ou les messages

// Traite les données du formulaire envoyées en méthode POST et initialise les variables avec les valeurs reçues
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère les valeurs (lastname, firstname, email et login) du formulaire, supprime les espaces avant et après, ou met une chaîne vide si non définie
    $lastname  = trim($_POST['lastname'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');  
    $email     = trim($_POST['email'] ?? '');
    $login     = trim($_POST['login'] ?? '');

    // Récupère le mot de passe et le mot de passe de confirmation envoyés via le formulaire, ou initialise à une chaîne vide si absent
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Vérifie que le prénom et le nom ne sont pas vides, ajoute un message d'erreur sinon
    if ($firstname === '') $errors[] = "Le prénom est requis.";
    if ($lastname === '')  $errors[] = "Le nom est requis.";

    // Vérifie que l'email est renseigné et valide, ajoute un message d'erreur sinon
    if ($email === '') {
        $errors[] = "L'email est requis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide.";
    }

    // Vérifie que le login est renseigné et qu'il contient au moins 3 caractères, ajoute un message d'erreur sinon
    if ($login === '') {
        $errors[] = "Le login est requis.";
    } elseif (strlen($login) < 3) {
        $errors[] = "Le login doit contenir au moins 3 caractères.";
    }

    // Vérifie que le mot de passe et le mot de passe de confirmation sont remplis, qu'ils correspondent, et que le mot de passe est assez long
    if ($password === '' || $password2 === '') {
        $errors[] = "Les deux champs de mot de passe sont obligatoires.";
    } elseif ($password !== $password2) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 3) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }

    // Si pas d'erreur, vérifier unicité login/email
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT user_login, user_email FROM users WHERE user_login = :login OR user_email = :email LIMIT 1');
        $stmt->execute([':login' => $login, ':email' => $email]);
        $existing = $stmt->fetch();

        // Si un utilisateur avec ce login ou cet email existe déjà, un message d'erreur s'affiche
        if ($existing) {
            if ($existing['user_login'] === $login) $errors[] = "Ce login est déjà utilisé.";
            if ($existing['user_email'] === $email) $errors[] = "Cet email est déjà utilisé.";
        } else {
            // Hachage mot de passe
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insère un nouvel utilisateur dans la base de données avec les champs firstname, lastname, email, login, mot de passe et rôle
            $insert = $pdo->prepare('INSERT INTO users (user_firstname, user_lastname, user_email, user_login, user_password, user_role) VALUES (:firstname, :lastname, :email, :login, :password, :role)');
            $insert->execute([
                ':firstname' => $firstname,
                ':lastname'  => $lastname,
                ':email'     => $email,
                ':login'     => $login,
                ':password'  => $password_hash,
                ':role'      => 'user'
            ]);

            $message = "Inscription réussie ! L'utilisateur a été créé.";
            // Réinitialiser les variables pour vider le formulaire
            $firstname = $lastname = $email = $login = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Inscription</title>
</head>
<body>
    <h1>Créer un utilisateur</h1>

    Affiche la liste des messages d'erreur s'il y en a
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul>
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Affiche un message de confirmation s'il existe -->
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Formulaire d'inscription avec pré-remplissage sécurisé des champs en cas d'erreur -->
    <form method="post" action="">
        <label for="firstname">Prénom</label>
        <input type="text" id="firstname" name="firstname" required value="<?= htmlspecialchars($firstname ?? '') ?>">

        <label for="lastname">Nom</label>
        <input type="text" id="lastname" name="lastname" required value="<?= htmlspecialchars($lastname ?? '') ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">

        <label for="login">Login</label>
        <input type="text" id="login" name="login" required value="<?= htmlspecialchars($login ?? '') ?>">

        <!-- Champs pour saisir et confirmer le mot de passe (non pré-remplis pour la sécurité) -->
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required>

        <label for="password2">Confirmer le mot de passe</label>
        <input type="password" id="password2" name="password2" required>

        <br><br>
        <!-- Bouton pour envoyer le formulaire et créer l'utilisateur -->
        <button type="submit">Créer l'utilisateur</button>
    </form>
</body>
</html>
