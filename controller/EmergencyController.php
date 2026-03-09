<?php

    // Controller pour la table Emergency. Sert d'intermédiaire entre la couche vue (html) et la couche "modèle" (EmergencyRepository)
    // Contient la logique métier et contrôle le flux des données.

    require_once __DIR__ . '/../model/emergency.php';       // Import du fichier gérant les requêtes SQL.
    require_once __DIR__ . '/../traitement_form.php';      // Import du fichier nettoyant les données saisies dans les dans les champs de formulaire.
    require_once __DIR__ . '/../classes/indexSort.php';     // Import du fichier gérant le tri et la pagination
    require_once __DIR__ . '/../classes/csrfToken.php';     // Import du fichier gérant la création et la vérification des tokens CSRF
    
    class EmergencyController 
    {
        private EmergencyRepository $emergencyRepo;  // Déclaration d'une propriété privée contenant l'instance du repository (accès aux données).

        // Constructeur permettant la connexion à la BDD.
        public function __construct(PDO $pdo) 
        {

            // Instanciation du repository avec la connexion PDO passée en argument.
            $this->emergencyRepo = new EmergencyRepository($pdo);
        }

        // -------------- FONCTION AFFICHANT LA LISTE DE TOUTES LES URGENCES (page emergency_index.php). --------------
        public function index() 
        {

            //-------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer :
            if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_emergency'], $_POST['emergency_id'], $_POST['csrf_token'])) {
                
                // Seuls admin et super_admin peuvent supprimer une urgence
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->emergencyRepo->deleteEmergency((int)$_POST['emergency_id']);

                // Préparation des paramètres pour la redirection après ajout
                // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                $params = [
                    'page' => 'emergency/index',
                    'limit' => $_GET['limit'] ?? 10,
                    'search' => $_GET['search'] ?? '',
                    'sort' => $_GET['sort'] ?? 'phone_line_number',
                    'order' => $_GET['order'] ?? 'asc',
                    'page_number' => $_GET['page_number'] ?? 1,
                ];

                // Redirection vers la page index pour éviter le rechargement du formulaire.
                header("Location: /index.php?" . http_build_query($params));
                exit;       // Stop le script.
            }

            // -------------- GESTION DU TRI ET PAGINATION --------------

            // Initialisation de la classe IndexSort
            $indexSort = new IndexSort($_GET, '/index.php');
            
            // On récupère le nombre total d'urgences correspondant à la recherche actuelle.
            //Cela sert notamment pour la pagination afin de savoir combien de pages sont nécessaires.
            $indexSort->totalResults = $this->emergencyRepo->countAll($indexSort->search);

            // Récupération des bâtiments à afficher avec tri et pagination
            // Paramètres passés à la fonction getAll :
            // - $indexSort->sort : colonne sur laquelle trier
            // - $indexSort->order : ordre de tri ('asc' ou 'desc')
            // - $indexSort->search : terme de recherche pour filtrer les résultats
            // - $indexSort->limit : nombre de résultats par page
            // - $indexSort->offset : décalage pour la pagination (ex : page 2 commence après les 10 premiers)
            $emergencies = $this->emergencyRepo->getAll(
                $indexSort->sort,
                $indexSort->order,
                $indexSort->search,
                $indexSort->limit,
                $indexSort->offset
            );

            // Définition des colonnes dans le tableau HTML :
            // clé = nom de la colonne en BDD, valeur = label affiché dans l'entête.
            $colonnes = [
                'phone_line_number' => 'Ligne téléphonique',
                'building_name' => 'Nom du bâtiment',
                'equipment_model' => 'Modèle d\'équipement',
                'emergency_type' => 'Type d\'urgence',
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
            require __DIR__ . '/../views/emergency/emergency_index.php';
        }

        // -------------- FONCTION AFFICHANT LE DETAIL D'UNE URGENCE (page emergency_detail.php). --------------
        public function detail(int $id) 
        {
            //-------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer :
            if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_emergency'], $_POST['emergency_id'], $_POST['csrf_token'])) {
                
                // Seuls admin et super_admin peuvent supprimer une urgence
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->emergencyRepo->deleteEmergency((int)$_POST['emergency_id']);

                $csrf_token = CsrfToken::generateToken();

                // Redirection vers la page index pour éviter le rechargement du formulaire.
                header("Location: /index.php?" . http_build_query($params));
                exit;       // Stop le script.
            }

            
            // Récupération des informations d'une urgence depuis la BDD.
            $emergency = $this->emergencyRepo->getById($id);

            $equipments_raw = $this->emergencyRepo->getAllEquipments();

            $equipments = [];

            foreach ($equipments_raw as $equipment) {
                $equipments[] = [
                    'id' => $equipment['equipment_id'],
                    'type' => $equipment['equipment_type'],
                    'model' => $equipment['equipment_model']
                ];
            }

            // Si aucune urgence n'est trouvée, on renvoie une erreur 404.
            if(!$emergency) {
                http_response_code(404);
                echo "<p>Urgence introuvable.</p>";
                exit;
            }

            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/emergency/emergency_detail.php';
        }

        // -------------- FONCTION PERMETTANT L'AJOUT D'UNE URGENCE (page emergency_form.php). --------------
        public function add() 
        {
            authorize(['admin', 'super-admin']);

            // Récupération des bâtiments et équipements pour les afficher dans un select
            $buildings = $this->emergencyRepo->getAllBuildings();
            $equipments = $this->emergencyRepo->getAllEquipments();

            // Tableau définissant les statuts possibles pour les urgences: 
            $types = ['alarme intrusion', 'alarme incendie', 'ascenseur', "bouton urgence"];

            $error = null; // message d’erreur envoyé à la vue

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $number = trim($_POST['emergency_phone_line_number'] ?? '');
                $phone_line_id = $this->emergencyRepo->getPhoneLineIdByNumber($number);

                $data = [
                    'emergency_phone_line_id' => $phone_line_id,
                    'emergency_building_id'   => !empty($_POST['emergency_building_id']) ? (int)$_POST['emergency_building_id'] : null,
                    'emergency_equipment_id'  => !empty($_POST['emergency_equipment_id']) ? (int)$_POST['emergency_equipment_id'] : null,
                    'emergency_type'          => $_POST['emergency_type'] ?? null
                ];

                if ($phone_line_id === null) {
                    $error = "Le numéro saisi est introuvable.";

                } elseif (!$this->validateEmergency($data)) {

                    $error = "Les données du formulaire sont invalides.";

                } else {
                    // Ajout de l'urgence en base de données
                    $this->emergencyRepo->addEmergency($data);

                    header("Location: /index.php?page=emergency/index");
                    exit;
                }
            }

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correpsondante
            require __DIR__ . '/../views/emergency/emergency_form.php';
        }

        // -------------- FONCTION PERMETTANT LA MODFICATION D'UNE URGENCE (page emergency_edit_form.php). --------------
        public function edit(int $id) 
        {
            // Seuls admin et super_admin peuvent modifier une urgence
            authorize(['admin', 'super-admin']);

            // Récupère l'urgence correspondante à l'ID fourni.
            $emergency = $this->emergencyRepo->getByID($id);

            // Si aucune urgence n'est trouvée, on arrête le script
            if (!$emergency) {
                http_response_code(404);
                echo "<p>Urgence introuvable.</p>";
                exit;
            }

            // Récupération des bâtiments et équipements pour les afficher dans un select
            $buildings = $this->emergencyRepo->getAllBuildings();
            $equipments = $this->emergencyRepo->getAllEquipments();

            // Tableau définissant les statuts possibles pour les urgences: 
            $types = ['alarme intrusion', 'alarme incendie', 'ascenseur', "bouton urgence"];

            $error = null; // message d’erreur envoyé à la vue

            // Si le formulaire n'a pas été soumis, on préremplit les champs avec les valeurs de la BDD
            $emergency_phone_line_id = $emergency['emergency_phone_line_id'];
            $emergency_phone_line_number = $this->emergencyRepo->getPhoneLineNumberById($emergency_phone_line_id);

            $emergency_building_id = $emergency['emergency_building_id'];
            $emergency_equipment_id = $emergency['emergency_equipment_id'];
            $emergency_type = $emergency['emergency_type'];

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $number = trim($_POST['emergency_phone_line_number'] ?? '');
                $phone_line_id = $this->emergencyRepo->getPhoneLineIdByNumber($number);

                $data = [
                    'emergency_phone_line_id' => $phone_line_id,
                    'emergency_building_id'   => !empty($_POST['emergency_building_id']) ? (int)$_POST['emergency_building_id'] : null,
                    'emergency_equipment_id'  => !empty($_POST['emergency_equipment_id']) ? (int)$_POST['emergency_equipment_id'] : null,
                    'emergency_type'          => $_POST['emergency_type'] ?? null
                ];

                if ($phone_line_id === null) {

                    $error = "Le numéro saisi est introuvable.";

                } elseif (!$this->validateEmergency($data)) {

                    $error = "Les données du formulaire sont invalides.";

                } else {

                    // Mise à jour de l'urgence en base de données selon l'ID avec les nouvelles valeurs
                    $this->emergencyRepo->updateEmergency($id, $data);

                    header("Location: /index.php?page=emergency/index");
                    exit;
                }
            } 

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correpsondante (affichage de la page HTML).
            require __DIR__ . '/../views/emergency/emergency_edit_form.php';
        }

        // -------------- FONCTION PRIVEE DE VALIDATION DES DONNEES. --------------
        private function validateEmergency(array $data): bool 
        {

            return !empty($data['emergency_phone_line_id'])         // La ligne téléphonique ne doit pas être vide
                   && !empty($data['emergency_building_id'])       // Le bâtiment ne doit pas être vide
                   && !empty($data['emergency_equipment_id'])     // L'équipement ne doit pas être vide
                   && !empty($data['emergency_type']);           // Le type d'urgence ne doit pas être vide
        }
    }   
?>