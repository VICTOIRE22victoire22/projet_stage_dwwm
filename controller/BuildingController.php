<?php

    // Controller pour la table Building. Sert d'intermédiaire entre la couche vue (html) et la couche "modèle" (BuildingRepository)
    // Contient la logique métier et contrôle le flux des données.

    require_once __DIR__ . '/../model/building.php';       //Import du fichier gérant les requêtes SQL.
    require_once __DIR__ . '/../traitement_form.php';     //Import du fichier nettoyant les données saisies dans les dans les champs de formulaire.
    require_once __DIR__ . '/../classes/indexSort.php';     // Import du fichier gérant le tri
    require_once __DIR__ . '/../classes/csrfToken.php';     // Import du fichier gérant la création et la vérification des tokens CSRF

    class BuildingController 
    {
        private BuildingRepository $buildingRepo;  // Déclaration d'une propriété privée contenant l'instance du repository (accès aux données).

        // Constructeur permettant la connexion à la BDD.
        public function __construct(PDO $pdo) 
        {

            // Instanciation du repository avec la connexion PDO passée en argument.
            $this->buildingRepo = new BuildingRepository($pdo);
        }

        // -------------- FONCTION AFFICHANT LA LISTE DE TOUS LES BATIMENTS (page building_index.php). --------------
        public function index() 
        {

            //-------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_building'], $_POST['building_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer un bâtiment
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }
                
                $this->buildingRepo->deleteBuilding((int)$_POST['building_id']);

                // Préparation des paramètres pour la redirection après ajout
                // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                $params = [
                    'page' => 'building/index',
                    'limit' => $_GET['limit'] ?? 10,
                    'search' => $_GET['search'] ?? '',
                    'sort' => $_GET['sort'] ?? 'building_name',
                    'order' => $_GET['order'] ?? 'asc',
                    'page_number' => $_GET['page_number'] ?? 1,
                ];

                // Redirection vers la page index pour éviter le rechargement du formulaire.
                header("Location: /index.php?" . http_build_query($params));
                exit;       // Stop l'exécution du script.
            }

            // -------------- GESTION DU TRI ET PAGINATION --------------

            // Initialisation de la classe IndexSort
            $indexSort = new IndexSort($_GET, '/index.php');

            // On récupère le nombre total de bâtiments correspondant à la recherche actuelle.
            // Cela sert notamment pour la pagination afin de savoir combien de pages sont nécessaires.
            $indexSort->totalResults = $this->buildingRepo->countAll($indexSort->search);

            // Récupération des bâtiments à afficher avec tri et pagiantion
            // Paramètres passés à la fonction getAll:
            // - $indexSort->sort : colonne sur laquelle trier
            // - $indexSort->order : ordre de tri ('asc' ou 'desc')
            // - $indexSort->search : terme de recherche pour filtrer les résultats
            // - $indexSort->limit : nombre de résultats par page
            // - $indexSort->offset : décalage pour la pagination (ex : page 2 commence après les 10 premiers)
            $buildings = $this->buildingRepo->getAll(
                $indexSort->sort,
                $indexSort->order,
                $indexSort->search,
                $indexSort->limit,
                $indexSort->offset
            );

            // Définition des colonnes dans le tableau HTML:
            // clé = nom de la colonne en BDD, valeur = label affiché dans l'entête.
            $colonnes = [
                'building_name' => 'Nom du bâtiment',
                'building_address' => 'Adresse',
                'building_erp_category' => 'Catégorie ERP',
                'site_name' => 'Sité associé'
            ];

            // Tableau qui contiendra les informations pour le tri (flèches et URLs)
            $triInfos = [];

            // Pour chaque colonne:
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

            // Chargement de la vue correpsondante (affichage de la page HTML)
            require __DIR__ . '/../views/building/building_index.php';
        }

        // -------------- FONCTION AFFICHANT LE DETAIL D'UN BATIMENT (page building_detail.php). --------------
        public function detail(int $id) 
        {

            //-------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_building'], $_POST['building_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer un bâtiment
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }
                
                $this->buildingRepo->deleteBuilding((int)$_POST['building_id']);

                // Redirection vers la page index pour éviter le rechargement du formulaire.
                header("Location: /index.php?page=building/index");
                exit;       // Stop l'exécution du script.
            }
            

            // Récupération des informations d'un bâtiment depuis la BDD.
            $building = $this->buildingRepo->getById($id);

            // Si aucun bâtiment n'est trouvé, on renvoie une erreur 404.
            if (!$building) {
                http_response_code(404);
                echo "<p>Bâtiment introuvable.</p>";
                exit;
            }

            // Récupération des urgences liées à ce bâtiment
            $emergencies_raw = $this->buildingRepo->getEmergenciesByBuildingId($id);

            $emergencies = [];

            foreach ($emergencies_raw as $emergency) {
                $emergencies[] = [
                    'number' => $emergency['phone_line_number'] ?? null,
                    'id' => $emergency['phone_line_id'] ?? null,
                    'emergency_type' => $emergency['emergency_type'] ?? null,
                ];
            }

            // Récupération des lignes téléphoniques liées à ce bâtiment
            $phone_lines_raw = $this->buildingRepo->getPhoneLinesByBuildingId($id);

            $phone_lines = [];

            foreach ($phone_lines_raw as $line) {
                $phone_lines[] = [
                    'designation' => $line['phone_line_designation'] ?? null,
                    'agent'       => $line['agent_fullname'] ?? null,
                    'number'      => $line['phone_line_number'] ?? null,
                    'id'          => $line['phone_line_id'] ?? null,
                ];
            }

            $csrf_token = CsrfToken::generateToken();
            
            // Chargement de la vue correpsondante (affichage de la page HTML)
            require __DIR__ . '/../views/building/building_detail.php';
        }

        // -------------- FONCTION PERMETTANT L'AJOUT D'UN BATIMENT (page building_form.php). --------------
        public function add() 
        {
            // Seuls admin et super_admin peuvent ajouter un bâtiment
            authorize(['admin', 'super-admin']);

            $sites = $this->buildingRepo->getAllSites();

            // Si le formulaire a été soumis
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Nettoie et récupère les données du formulaire.
                $data = [
                    'building_name' => valide_donnees($_POST['building_name'] ?? ""),
                    'building_address' => valide_donnees($_POST['building_address'] ?? ""),
                    'building_erp_category' => valide_donnees($_POST['building_erp_category'] ?? ""),
                    'building_site_id' => isset($_POST['building_site_id']) && $_POST['building_site_id'] !== '' ? (int) $_POST['building_site_id'] : null
                ];

                // Si les données sont valides selon la fonction validateBuilding()
                if($this->validateBuilding($data)) {
                    // Ajout du bâtiment en base de données.
                    $this->buildingRepo->addBuilding($data);

                    // Préparation des paramètres pour la redirection après ajout
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'building/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'building_name',
                        'order' => $_GET['order'] ?? 'asc',
                        'page_number' => $_GET['page_number'] ?? 1,
                    ];

                    header("Location: /index.php?" . http_build_query($params));
                    exit;

                } else {
                    // Si les données sont incorrectes, on prépare un message d'erreur à afficher dans la vue.
                    echo  "<p>Les données sont erronées ou incomplètes.</p>";
                }  
            } 

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correpondante (affichage de la page HTML)
            require __DIR__ . '/../views/building/building_form.php';
        }

        // -------------- FONCTION PERMETTANT LA MODIFICATION D'UN BATIMENT (page building_edit_form.php). --------------
        public function edit(int $id) 
        {
            // Seuls admin et super-admin peuvent modifier un bâtiment
            authorize(['admin', 'super-admin']);

            // Récupère le bâtiment correspondant à l'ID fourni.
            $building = $this->buildingRepo->getById($id);
            $sites = $this->buildingRepo->getAllSites();

            // si aucun bâtiment n'est trouvé, on arrête le script
            if (!$building) {
                http_response_code(404);
                echo "<p>Bâtiment introuvable.</p>";
                exit;
            }

             // Si le formulaire n'a pas été soumis, on préremplit les champs avec les valeurs de la BDD
            $name = $building['building_name'];
            $address = $building['building_address'];
            $erp_category = $building['building_erp_category'];
            $site_id = $building['building_site_id'];

            // Si le formulaire est soumis 
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Nettoie et récupère les nouvelles valeurs saisies.
                $data = [
                    'building_name' => valide_donnees($_POST['building_name'] ?? ""),
                    'building_address' => valide_donnees($_POST['building_address'] ?? ""),
                    'building_erp_category' => valide_donnees($_POST['building_erp_category'] ?? ""),
                    'building_site_id' => isset($_POST['building_site_id']) && $_POST['building_site_id'] !== '' ? (int) $_POST['building_site_id'] : null
                ];

                // Si les données sont valides selon la fonction validateBuilding()
                if ($this->validateBuilding($data)) {

                    // Mise à jour du bâtiment en base de données selon l'ID avec les nouvelles valeurs
                    $this->buildingRepo->updateBuilding($id, $data);

                    // Préparation des paramètres pour la redirection après mise à jour
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'building/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'building_name',
                        'order' => $_GET['order'] ?? 'asc',
                        'page_number' => $_GET['page_number'] ?? 1,
                    ]; 
                
                    header("Location: /index.php?" . http_build_query($params));
                    exit;
                    
                } else {
                    // Préparation des variables à afficher dans le formulaire après soumission
                    // On récupère les valeurs saisies pour les réafficher même si elles ont été validées
                    // Permet de conserver les valeurs saisies par l'utilisateur.
                    // en cas d'erreur cela évite à l'utilisateur d'avoir à tout resaisir
                    $name = $data['building_name'];
                    $address = $data['building_address'];
                    $erp_category = $data['building_erp_category'];
                    $site_id = $data['building_site_id'];

                    // Message d'erreur si les données sont incorrectes.
                    echo "<p>Les données sont erronées ou incomplètes</p>";
                }
            } 

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correspondante (affichage de la page HTML).
            require __DIR__ . '/../views/building/building_edit_form.php';
        }

        // -------------- FONCTION PRIVEE DE VALIDATION DES DONNEES. --------------
        // Vérifie que les champs ne sont pas vides.
        private function validateBuilding(array $data): bool 
        {

            return !empty($data['building_name'])                   // Le nom du bâtiment ne doit pas être vide
                   && !empty($data['building_address'])             // L'adresse du bâtiment ne doit pas être vide
                   && !empty($data['building_erp_category']);       // La catégorie ERP du bâtiment ne doit pas être vide
        }   
    }
?>