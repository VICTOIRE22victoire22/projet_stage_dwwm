<?php

    // Controller pour la table Users. Sert d'intermédiaire entre la couche vue (html) et la couche "modèle" (UsersRepository)
    // Contient la logique métier et contrôle le flux des données.

    require_once __DIR__ . '/../model/users.php';             // Import du fichier gérant les requêtes SQL.
    require_once __DIR__ . '/../traitement_form.php';        // Import du fichier nettoyant les données saisies dans les champs de formulaire.
    require_once __DIR__ . '/../classes/indexSort.php';     // Import du fichier gérant le tri et la pagination
    require_once __DIR__ . '/../classes/csrfToken.php';     // Import du fichier gérant la création et la vérification des tokens CSRF

    class UsersController
    {
        private UsersRepository $usersRepo;     // Déclaration d'une propriété privée contenant l'instance du repository (accès aux données).

        // Constructeur permettant la connexion à la BDD.
        public function __construct(PDO $pdo) 
        {

            // Instanciation du repository avec la connexion PDO passée en argument.
            $this->usersRepo = new UsersRepository($pdo);
        }

        // -------------- FONCTION AFFICHANT LA LISTE DE TOUS LES UTILISATEURS (page users_index.php). --------------
        public function index() 
        {
            // Seuls admin et super-admin peuvent consulter la liste des utilisateurs
            authorize(['admin', 'super-admin']);

            // -------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer':
            if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_users'], $_POST['user_id'], $_POST['csrf_token'])) {
                
                // Seul super-admin peut supprimer un utilisateur
                authorize(['super-admin']);
                
                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->usersRepo->deleteUser((int)$_POST['user_id']);

                // Préparation des paramètres pour la redirection après ajout
                // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                $params = [
                    'page' => 'users/index',
                    'limit' => $_GET['limit'] ?? 10,
                    'search' => $_GET['search'] ?? '',
                    'sort' => $_GET['sort'] ?? 'user_firstname',
                    'order' => $_GET['order'] ?? 'asc',
                    'page_number' => $_GET['page_number'] ?? 1,
                ];

                // Redirection vers la page index pour éviter le rechargement du formulaire
                header("Location: /index.php?" . http_build_query($params));
                exit;
            }

            // -------------- GESTION DU TRI ET PAGINATION --------------

            // Initialisation de la classe IndexSort
            $indexSort = new IndexSort($_GET, '/index.php');

            // On récupère le nombre total des utilisateurs correspondant à la recherche actuelle.
            // Cela sert notamment pour la pagination afin de savoir combien de pages sont nécessaires.
            $indexSort->totalResults = $this->usersRepo->countAll($indexSort->search);

            // Récupération des utilisateurs à afficher avec tri et pagination
            // Paramètres passés à la fonction getAll :
            // - $indexSort->sort : colonne sur laquelle trier
            // - $indexSort->order : ordre de tri ('asc' ou 'desc')
            // - $indexSort->search : terme de recherche pour filtrer les résultats
            // - $indexSort->limit : nombre de résultats par page
            // - $indexSort->offset : décalage pour la pagination (ex : page 2 commence après les 10 premiers)
            $users = $this->usersRepo->getAll(
                $indexSort->sort,
                $indexSort->order,
                $indexSort->search,
                $indexSort->limit,
                $indexSort->offset
            );

            // Définition des colonnes dans le tableau HTML :
            // clé = nom de la colonne en BDD, valeur = label affiché dans l'entête.
            $colonnes = [
                'user_firstname' => 'Prénom',
                'user_lastname' => 'Nom',
                'user_email' => 'Email',
                'user_login' => 'Identifiant',
                'user_role' => "Rôle"
            ];

            // Tableau qui contiendra les informations pour le tri (flèches et URLs)
            $triInfos = [];

            // Pour chaque colonne :
            // - 'arrow' : flèche indiquant si le tri est ascendant ou descendant sur cette colonne
            // - 'url'   : URL permettant de trier par cette colonne (avec les paramètres GET corrects)
            foreach ($colonnes as $colonne => $label) {
                $triInfos[$colonne] = [
                    'arrow' => $indexSort->arrowFor($colonne),
                    'url' => $indexSort->sortUrl($colonne),
                ];
            }

            // Calcul du nombre total de pages nécessaires en fonction du nombre total de résultats
            // et du nombre de résultats par page.
            $totalPages = $indexSort->totalPages();

            // Tableau contenant les informations de chaque page pour créer la pagination dans la vue.
            $pagination = [];

            // Pour chaque page, on stocke :
            // - 'page'    : numéro de la page
            // - 'url'     : URL permettant de naviguer vers cette page (avec les paramètres GET corrects)
            // - 'current' : booléen indiquant si cette page est la page courante (utile pour mettre en surbrillance)
            for ($i = 1; $i <= $totalPages; $i++) {
                $pagination[] = [
                    'page' => $i,
                    'url' => $indexSort->pageUrl($i),
                    'current' => $i === $indexSort->currentPage
                ];
            }

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/users/users_index.php';
        }

        // -------------- FONCTION AFFICHANT LE DETAIL D'UN UTILISATEUR (page users_detail.php). --------------
        public function detail(int $id) 
        {
            // -------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer':
            if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_users'], $_POST['user_id'], $_POST['csrf_token'])) {
                
                // Seul super-admin peut supprimer un utilisateur
                authorize(['super-admin']);
                
                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->usersRepo->deleteUser((int)$_POST['user_id']);

                // Redirection vers la page index pour éviter le rechargement du formulaire
                header("Location: /index.php?page=users/index");
                exit;
            }
            
            // Seuls admin et super-admin peuvent voir le détail d'un utilisateur  
            authorize(['admin', 'super-admin']);
            
            // Récupération des informations de l'utilisateur depuis la BDD
            $user = $this->usersRepo->getById($id);

            // Si aucun utilisateur n'est trouvé, on renvoie une erreur 404
            if(!$user) {
                http_response_code(404);
                echo "<p>Utilisateur introuvable.</p>";
                exit;
            }

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correspondante (affichage de la HTML)
            require __DIR__ . '/../views/users/users_detail.php';
        }

        // -------------- FONCTION PERMETTANT L'AJOUT D'UN UTILISATEUR (page users_form.php). --------------
        public function add() 
        {
            // Seul super-admin peut ajouter un utilisateur
            authorize(['super-admin']);

            // Tableau définissant les role possibles pour un utilisateur:
            $user_roles = ['super-admin', 'admin', 'user'];

            // Si le formulaire a été soumis
            if($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Tableau des données à envoyée en BDD
                    $data = [
                        'user_firstname' => valide_donnees($_POST['user_firstname'] ?? ''),
                        'user_lastname' => valide_donnees($_POST['user_lastname'] ?? ''),
                        'user_email' => valide_donnees(filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL) ?? ''),
                        'user_login' => valide_donnees($_POST['user_login'] ?? ''),
                        'user_password' => password_hash(valide_donnees($_POST['user_password']), PASSWORD_DEFAULT),
                        'user_role' => $_POST['user_role']
                    ];

                // Si les données sont valides selon la fonction validateUser()
                if($this->validateUser($data)) {
                    // Ajout de l'utilisateur en base de données.
                    $this->usersRepo->addUser($data);

                    // Préparation des paramètres pour la redirection après ajout
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'users/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'user_firstname',
                        'order' => $_GET['order'] ?? 'asc',
                        'page_number' => $_GET['page_number'] ?? 1,
                    ];

                    // Redirection vers la liste des utilisateurs après ajout
                    header("Location: /index.php?" . http_build_query($params));
                    exit;
                    
                } else {
                    // Si les données sont incorrectes, on prépare un message d'erreur à afficher dans la vue.
                    echo "<p>Les données sont erronées ou incomplètes.</p>";
                }
            }

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/users/users_form.php';    
        }

        // -------------- FONCTION PERMETTANT LA MODIFICATION D'UN UTILISATEUR (page users_edit_form.php). --------------
        public function edit(int $id) 
        {
            // Seul super-admin peut modifier un utilisateur
            authorize(['super-admin']);

            // Récupère l'utilisateur correspondant à l'ID fourni.
            $user = $this->usersRepo->getById($id);

            // Si aucun utilisateur n'est trouvé, on arrête le script 
            if(!$user) {
                http_response_code(404);
                echo "<p>Utilisateur introuvable.</p>";
                exit;
            }

            // Tableau définissant les role possibles pour un utilisateur:
            $user_roles = ['super-admin', 'admin', 'user'];

            // Si le formulaire n'a pas été soumis, on préremplit les champs avec les valeurs de la BDD
            $firstname = $user['user_firstname'];
            $lastname = $user['user_lastname'];
            $email = $user['user_email'];
            $login =$user['user_login'];
            $role = $user['user_role'];

            // Si le formulaire est soumis
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Nettoie et récupère les nouvelles valeurs saisies.
                $data = [
                    'user_firstname' => valide_donnees($_POST['user_firstname'] ?? ''),
                    'user_lastname' => valide_donnees($_POST['user_lastname'] ?? ''),
                    'user_email' => valide_donnees($_POST['user_email'] ?? ''),
                    'user_login' => valide_donnees($_POST['user_login'] ?? ''),
                    'user_role' => $_POST['user_role'] ?? '',
                ];

                // Gestion du mot de passe si changement de celui-ci
                if (!empty($_POST['user_password'])) {

                    $data['user_password'] = password_hash(valide_donnees($_POST['user_password']), PASSWORD_DEFAULT);

                } else {
        
                    $data['user_password'] = $user['user_password'];
                }

                // Si les données sont valides selon la fonction validateUser()
                if ($this->validateUser($data)) {

                    // Mise à jour de l'utilisateur en base de données selon l'ID avec les nouvelles valeurs
                    $this->usersRepo->updateUser($id, $data);

                    // Préparation des paramètres pour la redirection après ajout
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'users/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'user_firstname',
                        'order' => $_GET['order'] ?? 'asc',
                        'page_number' => $_GET['page_number'] ?? 1,
                    ];


                    // Redirection vers la liste des agents avec les paramètres conservés
                    header("Location: /index.php?" . http_build_query($params));
                    exit;

                } else {
                    // Préparation des variables à afficher dans le formulaire après soumission
                    // On récupère les valeurs saisies pour les réafficher même si elles ont été validées
                    // Permet de conserver les valeurs saisies par l'utilisateur.
                    // en cas d'erreur cela évite à l'utilisateur d'avoir à tout resaisir
                    $firstname = $data['user_firstname'];
                    $lastname = $data['user_lastname'];
                    $email = $data['user_email'];
                    $login =$data['user_login'];
                    $role = $data['user_role'];

                    // Si les données sont incorrectes, on prépare un message d'erreur à afficher dans la vue.
                    echo "<p>Les données sont erronées ou incomplètes.</p>";
                } 
            } 
            
            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/users/users_edit_form.php';   
        }

        // -------------- FONCTION PRIVEE DE VALIDATION DES DONNEES. --------------
        // Vérifie que les champs ne sont pas vides et ne contiennent pas de caractères interdits
        private function validateUser(array $data) 
        {
            return !empty($data['user_firstname'])
                && !empty($data['user_lastname'])
                && !empty($data['user_email'])
                && !empty($data['user_login'])

            // Les champs prénom et nom ne doivent contenir uniquement que des lettres, espaces, apostrophes ou tirets
            && preg_match("/^[A-Za-zÀ-ÿ '-]+$/", $data['user_firstname'])
            && preg_match("/^[A-Za-zÀ-ÿ '-]+$/", $data['user_lastname']);
        }
    }
