<?php 

    /** Ce fichier reste le point d'entrée principal de l'application. 
    * Il sert de routeur et décide quel controller et quelle méthode appeler 
    * selon la valeur du paramètre '?page' passé dans l'URL.
    */ 

    //-------------- INCLUSION DES ELEMENTS COMMUNS --------------
    require_once 'authentification.php';                             // Gestion de la session + authentification
    require_once 'includes/roles.php';                              // Gestion des roles et permission d'accès
    require_once 'includes/navbar.html.php';                          // Barre de navigation.                                                    

    //-------------- CONNEXION A LA BASE DE DONNEES --------------
    require_once __DIR__ . '/config/db.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestionnaire de parc téléphonique</title>
    <!-- CSS global -->
    <link rel="stylesheet" href="/css/navbar.css"/>
    <link rel="stylesheet" href="/css/style.css"/>
   

    <?php 

        // Récupère la valeur de ?page si elle existe
        $page = $_GET['page'] ?? null;

        // CSS à charger dynamiquement selon la page
        $page_css = null;

        // On vérifie que $page est bien une chaîne de caractères
        if (is_string($page)) {
            
            // Charge le CSS des pages détail
            if (str_contains($page, '/detail')) {
                $page_css = '/css/detail.css';

            } elseif (str_contains($page, '/add') || str_contains($page, '/edit')) {
                // Charge le CSS des formulaires (add et edit)
                $page_css = '/css/form.css';
            } 
        }

        // On vérifie que le fichier CSS existe avant de l'injecter pour éviter une erreur 404
        if ($page_css && file_exists(__DIR__ . $page_css)) {
            echo '<link rel="stylesheet" href="' . $page_css . '">';
        }     
    ?>
</head>

<body>

<div class='accueil-container'>
 
    <?php if (!isset($_GET['page']) || $_GET['page'] === null) : ?>
        <h1 id="main-title">GESTION DU PARC TELEPHONIQUE</h1>
    <?php endif; ?>

    <?php

        //-------------- ROUTEUR PRINCIPAL --------------

		// Message d'accueil et image par défaut si aucune page n'est spécifiée
    	if(!isset($_GET['page']) || $_GET['page'] === null){
        	echo "<p>Bonjour " . htmlspecialchars($_SESSION['user_firstname']) . " et bienvenue sur l'application de gestion du parc téléphonique.</p>";

            // IMAGE ACCUEIL
            echo "<img src='/includes/wolverine-comic-book-still-image-4049206977.jpg' class='accueil-image' />";
    	}

        // Récupération du paramètre '?page' dans l'URL
        $page = $_GET['page'] ?? null;

        // Si aucun paramètre '?page' n'est défini, on affiche la page d'accueil par défaut.
        if($page === null){
        } else {             

        //-------------- ROUTAGE VERS LES CONTROLEURS SELON LA PAGE DEMANDÉE --------------
           
        // Découpe du paramètre ?page=controller/method
        list($controller_name, $method) = explode('/', $page) + [null, null];

        // Vérifie que les 2 valeurs existent
        if($controller_name === null || $method === null) {
            http_response_code(400);
            echo "Paramètre 'page' invalide.";
            exit;
        }

        // Génère le nom de la classe controller (ex: user -> UserController)
        $controller_class = str_replace(' ', '', ucwords(str_replace('_', ' ', $controller_name))) . 'Controller';

        // Chemin vers le fichier du controller
        $controller_file = __DIR__ . '/controller/' . $controller_class . '.php';

        // Vérification de l'existance du fichier
        if(!file_exists($controller_file)) {
            http_response_code(404);
            echo "Contrôleur '$controller_class' introuvable.";
            exit;
        }

        // Inclusion du controller
        require_once $controller_file;

        // Vérifie que la classe existe réellement
        if(!class_exists($controller_class)) {
            http_response_code(500);
            echo "Classe '$controller_class' non définie.";
            exit;
        }

        // Instanciation du controller en passant la connexion PDO
        $controller = new $controller_class($pdo);

        // Vérifie que la méthode demandée existe
        if(!method_exists($controller, $method)) {
            http_response_code(404);
            echo "Méthode '$method' introuvable dans $controller_class. <br>";
            echo "Méthodes disponibles : " . implode(', ', get_class_methods($controller));
            exit;
        }

        // Récupération d'un eventuel ID dans l'URL 
        $id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

        // Récupère un éventuel type, cela est utile pour gérer les pages équipements
        $type = $_GET['type'] ?? null; 

        try {

            // Création d'un objet ReflectionMethod pour analyser la méthode du contrôleur
            // La classe ReflectionMethod rapporte des informations sur une méthode et notamment combien d'arguments celle-ci attend.
            $reflection = new ReflectionMethod($controller_class, $method);

            // Récupération du nombre d'arguments obligatoires attendus par la méthode
            $requiredParams = $reflection->getNumberOfRequiredParameters();

            //--------------------- CAS D’UNE MÉTHODE A 2 PARAMÈTRES ---------------------

            // Si la méthode attend exactement 2 arguments (ex: detail($type, $id)) et que $type et $id sont définis
            if ($requiredParams === 2) {

                // Si $id est fourni mais pas $type, on récupère le type depuis la base
                if($id !== null && $type === null) {

                    // Récupération du type de l'équipement via la table "equipment
                    $stmt = $pdo->prepare("SELECT equipment_type FROM equipment WHERE id = :id");
                    $stmt->execute(['id' => $id]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($result) {
                        $type = $result['equipment_type'];
                    } else {
                        throw new Exception("Aucun équipement trouvé avec l'ID $id.");
                    }
                }

                // Si type et ID sont présents, on appel la méthode
                if ($type !== null && $id !== null) {
                    $controller->$method($type, $id);
                } else {
                    throw new Exception("Les paramètres 'type' et 'id' sont requis pour {$method}.");
                }

                  //--------------------- CAS D’UNE MÉTHODE A 1 PARAMÈTRE ---------------------

            } elseif ($requiredParams === 1) {
                // Sinon, si la méthode attend exactement 1 argument

                // Si $type est défini, on le passe comme argument
                if ($type !== null) {
                    $controller->$method($type);

                } elseif ($id !== null) {
                    $controller->$method($id);

                } else {
                    // Si aucun des deux n'est défini on déclenche une exception pour signaler le problème
                    throw new Exception("Paramètre manquant pour {$method}");
                }

                 //--------------------- CAS D’UNE MÉTHODE SANS PARAMÈTRE ---------------------

            } else {
                // Si la méthode n'attend aucun argument on appelle simplement la méthode
                $controller->$method();
            }
            
            // ArgumentCountError est émis lorsque trop peu d'arguments sont passés à la méthode. 
            // Elle se lance également quand il y a trop d'arguments qui sont fournis.
        } catch (ArgumentCountError $e) {

            // Capture des erreurs liées au nombre d'arguments passés à la méthode
            http_response_code(500);    // Code HTTP 500 pour erreur serveur
            echo "<p>Erreur: nombre d'arguments incorrect pour la méthode <b>{$method}</b> du contrôleur <b>{$controller_class}</b>.</p>";
            // Affiche le message d'erreur détaillé de manière sécurisée
            echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";

        } catch (Exception $e) {
            // Capture toutes les autres exceptions (ex: ID manquant, type introuvable, etc);
            http_response_code(400); // Code HTTP 400 pour requête incorrecte
            echo "<p>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
?>
</div>
</body>
</html>