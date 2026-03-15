<?php

    // Controller pour la table Phone. Sert d'intermédiaire entre la couche vue (html) et la couche "modèle" (PhoneRepository)
    // Contient la logique métier et contrôle le flux des données.

    require_once __DIR__ . '/../model/phone.php';         // Import du fichier gérant les requêtes SQL.
    require_once __DIR__ . '/../traitement_form.php';     // Import du fichier nettoyant les données saisies dans les dans les champs de formulaire.
    require_once __DIR__ . '/../classes/indexSort.php';     // Import du fichier gérant le tri et la pagination
    require_once __DIR__ . '/../classes/csrfToken.php';     // Import du fichier gérant la création et la vérification des tokens CSRF

    class PhoneController
    {
        private PhoneRepository $phoneRepo;     // Déclaration d'une propriété privée contenant l'instance du repository (accès aux données).
        
        // Constructeur permettant la connexion à la BDD.
        public function __construct(PDO $pdo) 
        {

            // Instanciation du repository avec connexion PDO passée en argument
            $this->phoneRepo = new PhoneRepository($pdo);
        }

        // -------------- FONCTION AFFICHANT LA LISTE DE TOUS LES TELEPHONES (page phone_index.php). --------------
        public function index() 
        {
            //-------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_phone'], $_POST['phone_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer un téléphone fixe
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->phoneRepo->deletePhone((int)$_POST['phone_id']);

                // Préparation des paramètres pour la redirection après ajout
                // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                $params = [
                    'page' => 'phone/index',
                    'limit' => $_GET['limit'] ?? 10,
                    'search' => $_GET['search'] ?? '',
                    'sort' => $_GET['sort'] ?? 'phone_brand',
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

            // On récupère le nombre total de téléphones fixes correspondant à la recherche actuelle.
            // Cela sert notamment pour la pagination afin de savoir combien de pages sont nécessaires.
            $indexSort->totalResults = $this->phoneRepo->countAll($indexSort->search);

            // Récupération des agents à afficher avec tri et pagination
            // Paramètres passés à la fonction getAll :
            // - $indexSort->sort : colonne sur laquelle trier
            // - $indexSort->order : ordre de tri ('asc' ou 'desc')
            // - $indexSort->search : terme de recherche pour filtrer les résultats
            // - $indexSort->limit : nombre de résultats par page
            // - $indexSort->offset : décalage pour la pagination (ex : page 2 commence après les 10 premiers)
            $phones = $this->phoneRepo->getAll(
                $indexSort->sort,
                $indexSort->order,
                $indexSort->search,
                $indexSort->limit,
                $indexSort->offset
            );

            // Définition des colonnes dans le tableau HTML :
            // clé = nom de la colonne en BDD, valeur = label affiché dans l'entête.
            $colonnes = [
                    'phone_brand' => 'Marque',
                    'phone_model' => 'Modèle',
                    'phone_status' => 'Statut',
                    'phone_line_number' => 'Ligne téléphonique',
                    'agent_fullname' => 'Agent',
                    'building_name' => 'Bâtiment'
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
            
            // chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/phone/phone_index.php';
        }
        
        // -------------- FONCTION AFFICHANT LE DETAIL D'UN TELEPHONE (page phone_detail.php). --------------
        public function detail(int $id) 
        {

            //-------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_phone'], $_POST['phone_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer un téléphone fixe
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->phoneRepo->deletePhone((int)$_POST['phone_id']);

                // Redirection vers la page index pour éviter le rechargement du formulaire.
                header("Location: /index.php?page=phone/index");
                exit;       // Stop l'exécution du script.
            }

            // Récupération des informations d'un phone depuis la BDD.
            $phone = $this->phoneRepo->getById($id);

            // Si aucun mobile n'est trouvé, on renvoie une erreur 404.
            if (!$phone) {
                http_response_code(404);
                echo "<p>Téléphone introuvable.</p>";
                exit;
            }

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correspondante (affichage html)
            require __DIR__ . '/../views/phone/phone_detail.php';
        }

        // -------------- FONCTION PERMETTANT L'AJOUT D'UN TELEPHONE (page phone_form.php). --------------
        public function add() 
        {

            // Seuls admin et super-admin peuvent ajouter un téléphone fixe
            authorize(['admin', 'super-admin']);

            $phone_lines = $this->phoneRepo->getAllPhoneLines();
            $agents = $this->phoneRepo->getAllAgents();
            $buildings = $this->phoneRepo->getAllBuildings();

            // Si le formulaire a été soumis
            if($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Nettoie et récupère les données du formulaire
                $data = [
                    'phone_brand' => valide_donnees($_POST['phone_brand'] ?? ""),
                    'phone_model' => valide_donnees($_POST['phone_model'] ?? ""),
                    'phone_status' => $_POST['phone_status'] ?? "",
                    'phone_line_id' => isset($_POST['phone_line_id']) && $_POST['phone_line_id'] !== '' ? (int) $_POST['phone_line_id'] : null,
                    'phone_agent_id' => isset($_POST['phone_agent_id']) && $_POST['phone_agent_id'] !== '' ? (int) $_POST['phone_agent_id'] : null,
                    'phone_building_id' => isset($_POST['phone_building_id']) && $_POST['phone_building_id'] !== '' ? (int) $_POST['phone_building_id'] : null
                ];

                // Si les données sont valides selon la fonction validatePhone()
                if ($this->validatePhone($data)) {
                    // Ajout du téléphone en base de données.
                    $this->phoneRepo->addPhone($data);

                    // Préparation des paramètres pour la redirection après ajout
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'phone/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'phone_brand',
                        'order' => $_GET['order'] ?? 'asc',
                        'page_number' => $_GET['page_number'] ?? 1,
                    ];

                    // Redirection vers la liste des agents après ajout
                    header("Location: /index.php?" . http_build_query($params));
                    exit;

                } else {
                    // Si les données sont incorrectes, on prépare un message d'erreur à afficher dans la vue.
                    echo  "<p>Les données sont erronées ou incomplètes.</p>"; 
                }
            }

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correpsondante (affichage de la page HTML)
            require __DIR__ . '/../views/phone/phone_form.php';
        }

        // -------------- FONCTION PERMETTANT LA MODFICATION D'UN TELEPHONE (page phone_edit_form.php). --------------
        public function edit(int $id) 
        {

            // Seuls admin et super-admin peuvent modifier un téléphone fixe
            authorize(['admin', 'super-admin']);

            // Récupère le téléphone correpsondant à l'id fourni
            $phone = $this->phoneRepo->getById($id);
            $phone_lines = $this->phoneRepo->getAllPhoneLines();
            $agents = $this->phoneRepo->getAllAgents();
            $buildings = $this->phoneRepo->getAllBuildings();

            // Si aucun téléphone n'est trouvé, on arrête le script
            if (!$phone) {
                http_response_code(404);
                echo "<p>Téléphone introuvable.</p>";
                exit;
            }

            // Si le formulaire n'a pas été soumis, on préremplit les champs avec les valeurs de la BDD
            $phone_brand = $phone['phone_brand'];
            $phone_model = $phone['phone_model'];
            $phone_status = $phone['phone_status'];
            $phone_line_id = $phone['phone_line_id'];
            $phone_agent_id = $phone['phone_agent_id'];
            $phone_building_id = $phone['phone_building_id'];

            // Si le formulaire est soumis 
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Nettoie et récupère les nouvelles valeurs saisies.
                $data = [
                    'phone_brand' => valide_donnees($_POST['phone_brand'] ?? ""),
                    'phone_model' => valide_donnees($_POST['phone_model'] ?? ""),
                    'phone_status' => $_POST['phone_status'] ?? "",
                    'phone_line_id' => isset($_POST['phone_line_id']) && $_POST['phone_line_id'] !== '' ? (int) $_POST['phone_line_id'] : null,
                    'phone_agent_id' => isset($_POST['phone_agent_id']) && $_POST['phone_agent_id'] !== '' ? (int) $_POST['phone_agent_id'] : null,
                    'phone_building_id' => isset($_POST['phone_building_id']) && $_POST['phone_building_id'] !== '' ? (int) $_POST['phone_building_id'] : null
                ];

                // Si les données sont valides selon la fonction validatePhone() 
                if ($this->validatePhone($data)) {
                    // Mise à jour de l'agent en base de données selon l'ID avec les nouvelles valeurs
                    $this->phoneRepo->updatePhone($id, $data);

                    // Préparation des paramètres pour la redirection après mise à jour
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'phone/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'phone_brand',
                        'order' => $_GET['order'] ?? 'asc',
                        'page_number' => $_GET['page_number'] ?? 1,
                    ];

                    // Redirection vers la liste des téléphones avec les paramètres conservés
                    header("Location: /index.php?page=phone/index");
                    exit;

                } else {
                    // Préparation des variables à afficher dans le formulaire après soumission
                    // On récupère les valeurs saisies pour les réafficher même si elles ont été validées
                    // Permet de conserver les valeurs saisies par l'utilisateur.
                    // en cas d'erreur cela évite à l'utilisateur d'avoir à tout resaisir
                    $phone_brand = $data['phone_brand'];
                    $phone_model = $data['phone_model'];
                    $phone_status = $data['phone_status'];
                    $phone_line_id = $data['phone_line_id'];
                    $phone_agent_id = $data['phone_agent_id'];
                    $phone_building_id = $data['phone_building_id'];

                    // Message d'erreur si les données sont incorrectes.
                    echo "<p>Les données sont erronées ou incomplètes</p>";
                }
            } 

            $statuses = ['en service', 'cassé', 'en stock'];

            $csrf_token = CsrfToken::generateToken();
            
            // Chargement de la vue correspodante (affichage de la page HTML)
            require __DIR__ . '/../views/phone/phone_edit_form.php';
        }

        // -------------- FONCTION PRIVEE DE VALIDATION DES DONNEES. --------------
        // Vérifie que les champs ne sont pas vides.
        private function validatePhone(array $data): bool 
        {

            return !empty($data['phone_brand'])
                   && !empty($data['phone_model']);
        }
    }
