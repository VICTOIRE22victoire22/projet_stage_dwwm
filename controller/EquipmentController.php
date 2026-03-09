<?php

    // Controller pour la table Equipment. Sert d'intermédiaire entre la couche vue (html) et la couche "modèle" (EquipmentRepository)
    // Contient la logique métier et contrôle le flux des données.

    require_once __DIR__ . '/../model/equipment.php';         // Import du fichier gérant les requêtes SQL.
    require_once __DIR__ . '/../traitement_form.php';        // Import du fichier nettoyant les données saisies dans les champs de formulaire.
    require_once __DIR__ . '/../classes/indexSort.php';     // Import du fichier gérant le tri et la pagination
    require_once __DIR__ . '/../classes/csrfToken.php';     // Import du fichier gérant la création et la vérification des tokens CSRF

    class EquipmentController
    {
        private EquipmentRepository $equipmentRepo;

        public function __construct(PDO $pdo)
        {
            $this->equipmentRepo = new EquipmentRepository($pdo);
        }

       // -------------- FONCTION AFFICHANT LA LISTE DES EQUIPEMENT SELON LE TYPE -------------- 
       public function index(string $type) 
       {
            // Validation du type d'équipement 
            $validTypes = ['box', 'routeur', 'transmetteur'];
            if(!in_array($type, $validTypes, true)) {
                http_response_code(404);
                echo "<p>Type d'équipement invalide.</p>";
                exit;
            }

            //-------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_equipment'], $_POST['equipment_id'], $_POST['csrf_token'])) {
                
                // Seuls admin et super-admin peuvent supprimer un équipement
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->equipmentRepo->deleteEquipment((int)$_POST['equipment_id'], $type);

                // Préparation des paramètres pour la redirection après ajout
                // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                $params = [
                    'page' => 'equipment/index',
                    'type' => $type,
                    'limit' => $_GET['limit'] ?? 10,
                    'search' => $_GET['search'] ?? '',
                    'sort' => $_GET['sort'] ?? 'equipment_number',
                    'order' => $_GET['order'] ?? 'asc',
                    'page_number' => $_GET['page_number'] ?? 1, 
                ];

                // Redirection vers la page index pour éviter le rechargement du formulaire.
                header("Location: /index.php?" . http_build_query($params));
                exit;       // Stop l'exécution du script.
            }

            // -------------- GESTION DU TRI ET PAGINATION --------------

            // Initialisation de la classe IndexSort
            $indexSort = new IndexSort($_GET, 'index.php?page=equipment/index&type=' . urlencode($type));

            // On récupère le nombre total d'agents correspondant à la recherche actuelle.
            // Cela sert notamment pour la pagination afin de savoir combien de pages sont nécessaires.
            $indexSort->totalResults = $this->equipmentRepo->countAll($type, $indexSort->search);

            // Récupération des agents à afficher avec tri et pagination
            // Paramètres passés à la fonction getAll :
            // - $indexSort->sort : colonne sur laquelle trier
            // - $indexSort->order : ordre de tri ('asc' ou 'desc')
            // - $indexSort->search : terme de recherche pour filtrer les résultats
            // - $indexSort->limit : nombre de résultats par page
            // - $indexSort->offset : décalage pour la pagination (ex : page 2 commence après les 10 premiers)
            $equipments = $this->equipmentRepo->getAllEquipments(
                $type,
                $indexSort->sort,
                $indexSort->order,
                $indexSort->search,
                $indexSort->limit,
                $indexSort->offset
            );

            // Définition des colonnes dans le tableau HTML :
            // clé = nom de la colonne en BDD, valeur = label affiché dans l'entête.
            $colonnes = [
                'equipment_series_number' => 'N° de série',
                'equipment_model' => 'Modèle',
                'equipment_type' => 'Type'
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

            // Labels pour le titre dynamique
            $type_labels = [
                'transmetteur' => 'TRANSMETTEURS',
                'routeur' => 'ROUTEURS',
                'box' => 'BOX',
            ];

            $csrf_token = CsrfToken::generateToken();
             
            // Chargement de la vue correspondante 
            require __DIR__ . "/../views/equipment/equipment_index.php";
       }

       //-------------- FONCTION AFFICHANT LE DETAIL D'UN EQUIPEMENT --------------
       public function detail(string $type, int $id) 
       {
            // Validation du type d'équipement 
            $validTypes = ['box', 'routeur', 'transmetteur'];
            if(!in_array($type, $validTypes, true)) {
                http_response_code(404);
                echo "<p>Type d'équipement invalide.</p>";
                exit;
            }

            //-------------- GESTION DE LA SUPPRESSION --------------
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_equipment'], $_POST['equipment_id'], $_POST['csrf_token'])) {

                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }
                $this->equipmentRepo->deleteEquipment((int)$_POST['equipment_id'], $type);

                // Recréer les paramètres pour la redirection vers l'index
                $params = [
                    'page' => 'equipment/index',
                    'type' => $type,
                    'limit' => $_GET['limit'] ?? 10,
                    'search' => $_GET['search'] ?? '',
                    'sort' => $_GET['sort'] ?? 'equipment_number',
                    'order' => $_GET['order'] ?? 'asc',
                    'page_number' => $_GET['page_number'] ?? 1,
                ];

                header("Location: /index.php?" . http_build_query($params));
                exit;
            }

            // Récupération de l'équipement via la nouvelle fonction générique
            $equipment = $this->equipmentRepo->getEquipmentById($id, $type);

            if (!$equipment) {
                http_response_code(404);
                echo "<p>Equipement introuvable.</p>";
                exit;
            }

            // --- AJOUT DE LA VARIABLE $colonnes pour rendre la vue dynamique ---
            $colonnes = [
                'equipment_series_number' => 'N° de série',
                'equipment_model' => 'Modèle',
                'equipment_type' => 'Type'
            ];

            // Labels pour le titre dynamique
            $type_labels = [
                'transmetteur' => 'TRANSMETTEURS',
                'routeur' => 'ROUTEURS',
                'box' => 'BOX',
            ];

            $csrf_token = CsrfToken::generateToken();

            $indexSort = new IndexSort($_GET, "index.php?page=equipment/index&type={$type}");
            $returnLink = $indexSort->getReturnUrl();

            // Chargement de la vue
            require __DIR__ . "/../views/equipment/equipment_detail.php";
       }

       // -------------- FONCTION PERMETTANT L'AJOUT D'UN EQUIPEMENT --------------
       public function add() 
       {
            // Seuls admin et super-admin peuvent ajouter un équipement
            authorize(['admin', 'super-admin']);

            // Récupération du type via 'post' ou 'get'
            $type = $_POST['equipment_type'] ?? $_GET['type'] ?? null;
            
            // Si le formulaire a été soumis
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Nettoie et récupère les données du formulaire.
                $data = [
                    'equipment_series_number' => valide_donnees($_POST['equipment_series_number'] ?? ''),
                    'equipment_model' => valide_donnees($_POST['equipment_model'] ?? ''),
                    'equipment_type' => $_POST['equipment_type']
                ];
            
                // Si les données sont valides selon la fonction validateEquipment()
                if ($this->validateEquipment($data)) {
                    // Ajout de l'équipement en base de données.
                    $this->equipmentRepo->addEquipment($data);

                    // Conservation des paramètres de tri, recherche, pagination
                    $indexSort = new IndexSort($_GET, "index.php?page=equipment/index&type={$data['equipment_type']}");
                    header("Location: " . $indexSort->getReturnUrl());
                    exit;
                } else {
                    // Si les données sont incorrectes, on prépare un message d'erreur à afficher dans la vue.
                    echo  "<p>Les données sont erronées ou incomplètes.</p>";
                }
            }

            $indexSort = new IndexSort($_GET, "index.php?page=equipment/index&type={$type}");
            $returnLink = $indexSort->getReturnUrl();

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . "/../views/equipment/equipment_form.php";
        }

       // -------------- FONCTION PERMETTANT LA MODFICATION D'UN EQUIPMENT --------------
       public function edit(string $type, int $id ) 
       {

            // Seuls admin et super-admin peuvent modifier un équipement
            authorize(['admin', 'super-admin']);

            // Récupération de l'équipement via la nouvelle fonction générique
            $equipment = $this->equipmentRepo->getEquipmentById($id, $type);

            // Si aucun equipement n'est trouvé on arrête le script
            if (!$equipment) {
                http_response_code(404);
                echo "<p>Equipement introuvable.</p>";
                exit;
            }

            // Si le formulaire n'a pas été soumis, on préremplit les champs avec les valeurs de la BDD
            $equipment_series_number = $equipment['equipment_series_number'] ?? '';
            $equipment_model = $equipment['equipment_model'] ?? '';
            $equipment_type = $equipment['equipment_type'] ?? $type;

            // Si le formulaire est soumis 
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Nettoie et récupère les nouvelles valeurs saisies.
                $data = [
                    'equipment_series_number' => valide_donnees($_POST['equipment_series_number'] ?? ''),
                    'equipment_model' => valide_donnees($_POST['equipment_model']),
                    'equipment_type' => $_POST['equipment_type'] ?? $type
                ];

                // Si les données sont valides selon la fonction validateEquipment()
                if ($this->validateEquipment($data)) {
                    match ($type) {
                        'box' => $this->equipmentRepo->updateBox($id, $data),
                        'routeur' => $this->equipmentRepo->updateRouteur($id, $data),
                        'transmetteur' => $this->equipmentRepo->updateTransmetteur($id, $data)
                    };

                    $indexSort = new IndexSort($_GET, "index.php?page=equipment/index&type={$data['equipment_type']}");
                    header("Location: " . $indexSort->getReturnUrl());
                    exit;

                } else {
                    // Préparation des variables à afficher dans le formulaire après soumission
                    // On récupère les valeurs saisies pour les réafficher même si elles ont été validées
                    // Permet de conserver les valeurs saisies par l'utilisateur.
                    // en cas d'erreur cela évite à l'utilisateur d'avoir à tout resaisir
                    $equipment_series_number = $data['equipment_series_number'] ?? '';
                    $equipment_model = $data['equipment_model'] ?? '';
                    $equipment_type = $data['equipment_type'] ?? $type;

                    // Si les données sont incorrectes, on prépare un message d'erreur à afficher dans la vue.
                    echo  "<p>Les données sont erronées ou incomplètes.</p>";
                }
            }

            $types = ['box', 'routeur', 'transmetteur'];

            $indexSort = new IndexSort($_GET, "index.php?page=equipment/index&type={$type}");
            $returnLink = $indexSort->getReturnUrl();

            $csrf_token = CsrfToken::generateToken();

            require __DIR__ . "/../views/equipment/equipment_{$type}_edit_form.php";
       }

       // -------------- FONCTION PRIVEE DE VALIDATION DES DONNEES. --------------
       private function validateEquipment(array $data): bool
       {
            return !empty($data['equipment_series_number'])
                && !empty($data['equipment_model'])
                && !empty($data['equipment_type']);
       }
    }
?>