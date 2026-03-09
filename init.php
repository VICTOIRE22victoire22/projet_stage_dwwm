<?php
// Inclut la classe Navbar pour gérer la barre de navigation en POO
require_once 'classes/navbar.php';

// Crée la barre de navigation avec "LOGO" et ajoute les liens vers les différentes pages du site.
$navbar = new Navbar("LOGO");
$navbar->addLink("SITES", "site/index.php");

// Menu déroulant
$navbar->addLink("LIGNES", "#", [
    "LIGNE TELEPHONIQUE" => "phone_line/index.php",
    "NUMERO SDA" => "sda_number/index.php"
]);

$navbar->addLink("AGENTS", "agent/index.php");

// Menu déroulant
$navbar->addLink("FINANCES", "#", [
    "FACTURES" => "invoice/index.php",
    "OFFRES" => "offer/index.php"
]);

// Menu déroulant
$navbar->addLink("MATERIEL", "#", [
    "FIXE" => "phone/index.php",
    "MOBILE" => "mobile/index.php",
    "AUTRES" => [
        "EQUIPMENT" => "equipment/index.php",
        "PABX" => "pabx/index.php"
    ]
]);

$navbar->addLink("URGENCE", "emergency.php");

// Si l'utilisateur est connecté, afficher son nom et bouton déconnexion
if (isset($_SESSION['user_login'])) {
    $navbar->setLogout("logout.php", $_SESSION['user_login']);
}

$navbar->render();
