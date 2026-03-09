<?php

    // Controller pour la table Phone. Sert d'intermédiaire entre la couche vue (html) et la couche "modèle" (PhoneLineRepository)
    // Contient la logique métier et contrôle le flux des données.

    require_once __DIR__ . '/../model/phone_line.php';      // Import du fichier gérant les requêtes SQL.
    require_once __DIR__ . '/../traitement_form.php';      // Import du fichier nettoyant les données saisies dans les dans les champs de formulaire.
    require_once __DIR__ . '/../classes/indexSort.php';     // Import du fichier gérant le tri et la pagination
    require_once __DIR__ . '/../classes/csrfToken.php';     // Import du fichier gérant la création et la vérification des tokens CSRF

    class PhoneLineController
    {
        private PhoneLineRepository $phoneLineRepo;     // Déclaration d'une propriété privée contenant l'instance du repository (accès aux données).

        // Constructeur permettan la connexion à la BDD
        public function __construct(PDO $pdo) {

            // Instanciation du repository avec conenxion PDO passée en argument
            $this->phoneLineRepo = new PhoneLineRepository($pdo);
        }

        // -------------- FONCTION AFFICHANT LA LISTE DE TOUS LES LIGNES TELEPHONIQUES (page phone_line_index.php). --------------
        public function index() 
        {

            //-------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_phone_line'], $_POST['phone_line_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer une ligne téléphonique
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkNamedToken('phone_line_index', $_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->phoneLineRepo->deletePhoneLine((int)$_POST['phone_line_id']);

                // Préparation des paramètres pour la redirection après mise à jour
                // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                $params = [
                    'page' => 'phone_line/index',
                    'limit' => $_GET['limit'] ?? 10,
                    'search' => $_GET['search'] ?? '',
                    'sort' => $_GET['sort'] ?? 'phone_line_number',
                    'order' => $_GET['order'] ?? 'asc',
                    'page_number' => $_GET['page_number'] ?? 1,
                ];

                // Redirection vers la page index pour éviter le rechargement du formulaire.
                header("Location: /index.php?" . http_build_query($params));
                exit;       // Stop l'exécution du script.
            }

            // -------------- GESTION DU TRI ET PAGINATION --------------

            // Initialisation de la classe IndexSort
            $indexSort = new IndexSort($_GET, ('/index.php'));

            // On récupère le nombre total de lignes téléphoniques correspondantes à la recherche actuelle.
            // Cela sert notamment pour la pagination afin de savoir combien de pages sont nécessaires.
            $indexSort->totalResults = $this->phoneLineRepo->countAll($indexSort->search);

            // Récupération des lignes téléphoniques à afficher avec tri et pagination
            // Paramètres passés à la fonction getAll :
            // - $indexSort->sort : colonne sur laquelle trier
            // - $indexSort->order : ordre de tri ('asc' ou 'desc')
            // - $indexSort->search : terme de recherche pour filtrer les résultats
            // - $indexSort->limit : nombre de résultats par page
            // - $indexSort->offset : décalage pour la pagination (ex : page 2 commence après les 10 premiers)
            $phone_lines = $this->phoneLineRepo->getAll(
                $indexSort->sort,
                $indexSort->order,
                $indexSort->search,
                $indexSort->limit,
                $indexSort->offset
            );

            // Définition des colonnes dans le tableau HTML :
            // clé = nom de la colonne en BDD, valeur = label affiché dans l'entête.
            $colonnes = [
                    'phone_line_number' => 'Numéro de ligne',
                    'phone_line_status' => 'Statut',
                    // AFFICHAGE A REACTIVER SI BESOIN (PAGE LISTE)
                    // 'phone_line_termination_number' => 'Numéro de résiliation',
                    // 'phone_line_termination_date' => 'Date de résiliation',
                    // 'phone_line_box_return_date' => 'Date de retour de la box',
                    'phone_line_designation' => 'Désignation',
                    'agent_fullname' => 'Agent',
                    'building_name' => 'Bâtiment',
                    'offer_name' => 'Offre'
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

            $csrf_token = CsrfToken::generateNamedToken('phone_line_index');

            // chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/phone_line/phone_line_index.php';
        }

        //-------------- FONCTION AFFICHANT LE DETAIL D'UN TELEPHONE (page phone_line_detail.php). --------------
        public function detail(int $id) 
        {
            //-------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_phone_line'], $_POST['phone_line_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer une ligne téléphonique
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkNamedToken('phone_line_detail', $_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->phoneLineRepo->deletePhoneLine((int)$_POST['phone_line_id']);

                // Redirection vers la page index pour éviter le rechargement du formulaire.
                header("Location: /index.php?page=phone_line/index");
                exit;       // Stop l'exécution du script.
            }

            // Récupération des informations de la ligne téléphonique depuis la BDD.
            $phone_line = $this->phoneLineRepo->getById($id);

            // Si aucune ligne téléphonique n'est trouvée, on renvoie une erreur 404.
            if(!$phone_line) {
                http_response_code(404);
                echo "<p>Ligne téléphonique introuvable.</p>";
                exit;
            }

            // Récupération de l'offre liée à la ligne
            $offer = $this->phoneLineRepo->getOfferByPhoneLineId($id);

            // Permet l'affichage des numéros sda 
            $sda_numbers = $phone_line['sda_numbers'] ?? [];

            $csrf_token = CsrfToken::generateNamedToken('phone_line_detail');
            $csrf_token_delete_sda = CsrfToken::generateNamedToken('sda_number_delete');
            
            // Chargement de la vue correspondante (affichage HTML)
            require __DIR__ . '/../views/phone_line/phone_line_detail.php';
        }

        // -------------- FONCTION PERMETTANT L'AJOUT D'UN MOBILE (page phone_line_form.php). --------------
        public function add() 
        {

            // Seuls admin et super-admin peuvent ajouter une ligne téléphonique
            authorize(['admin', 'super-admin']);

            $agents = $this->phoneLineRepo->getAllAgents();
            $buildings = $this->phoneLineRepo->getAllBuildings();
            $offers = $this->phoneLineRepo->getAllOffers();

            // Si le formulaire a été soumis 
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkNamedToken('phone_line_add', $_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Nettoie et récupère les données du formulaire
                $data = [
                    'phone_line_number' => valide_donnees($_POST['phone_line_number'] ?? ""),
                    'phone_line_status' => $_POST['phone_line_status'],
                    'phone_line_termination_number' => !empty($_POST['phone_line_termination_number']) ? valide_donnees($_POST['phone_line_termination_number']) : null,
                    'phone_line_termination_date' => !empty($_POST['phone_line_termination_date']) ? $_POST['phone_line_termination_date'] : null,
                    'phone_line_box_return_date' => !empty($_POST['phone_line_box_return_date']) ? $_POST['phone_line_box_return_date'] : null,
                    'phone_line_designation' => valide_donnees($_POST['phone_line_designation'] ?? ""),
                    'phone_line_agent_id' => isset($_POST['phone_line_agent_id']) && $_POST['phone_line_agent_id'] !== '' ? (int) $_POST['phone_line_agent_id'] : null,
                    'phone_line_building_id' => isset($_POST['phone_line_building_id']) && $_POST['phone_line_building_id'] !== '' ? (int) $_POST['phone_line_building_id'] : null,
                    'phone_line_offer_id' => isset($_POST['phone_line_offer_id']) && $_POST['phone_line_offer_id'] !== '' ? (int) $_POST['phone_line_offer_id'] : null,
                ];

                // Si les données sont valides selon la fonction validatePhoneLine()
                if ($this->validatePhoneLine($data)) {
                    // Ajout de la ligne en base de données.
                    $this->phoneLineRepo->addPhoneLine($data);

                    // Préparation des paramètres pour la redirection après ajout
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'phone_line/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'phone_line_number',
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

            $csrf_token = CsrfToken::generateNamedToken('phone_line_add');

            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/phone_line/phone_line_form.php';
        }

        // -------------- FONCTION PERMETTANT LA MODFICATION D'UNE LIGNE TELEPHONIQUE (page phone_line_edit_form.php). --------------
        public function edit(int $id) 
        {
            // Seuls admin et super-admin peuvent modifier une ligne téléphonique
            authorize(['admin', 'super-admin']);

            // Récupère la ligne téléphonique correspondant à l'ID fourni
            $phone_line = $this->phoneLineRepo->getById($id);
            $agents = $this->phoneLineRepo->getAllAgents();
            $buildings = $this->phoneLineRepo->getAllBuildings();
            $offers = $this->phoneLineRepo->getAllOffers();

            // Si aucun téléphone n'est trouvé, on arrête le script
            if (!$phone_line) {
                http_response_code(404);
                echo "<p>Ligne téléphonique introuvable.</p>";
                exit;
            }

            // Si le formulaire n'a pas été soumis, on préremplit les champs avec les valeurs de la BDD
            $phone_line_number = $phone_line['phone_line_number'];
            $phone_line_status = $phone_line['phone_line_status'];
            $phone_line_termination_number = $phone_line['phone_line_termination_number'];
            $phone_line_termination_date = $phone_line['phone_line_termination_date'];
            $phone_line_box_return_date = $phone_line['phone_line_box_return_date'];
            $phone_line_designation = $phone_line['phone_line_designation'];
            $phone_line_agent_id = $phone_line['phone_line_agent_id'];
            $phone_line_building_id = $phone_line['phone_line_building_id'];
            $phone_line_offer_id = $phone_line['phone_line_offer_id'];

            // Si le formulaire est soumis
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkNamedToken('phone_line_edit', $_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Nettoie et récupère les nouvelles valeurs saisies.
                $data = [
                    'phone_line_number' => valide_donnees($_POST['phone_line_number'] ?? ""),
                    'phone_line_status' => $_POST['phone_line_status'],
                    'phone_line_termination_number' => !empty($_POST['phone_line_termination_number']) ? valide_donnees($_POST['phone_line_termination_number']) : null,
                    'phone_line_termination_date' => !empty($_POST['phone_line_termination_date']) ? $_POST['phone_line_termination_date'] : null,
                    'phone_line_box_return_date' => !empty($_POST['phone_line_box_return_date']) ? $_POST['phone_line_box_return_date'] : null,
                    'phone_line_designation' => valide_donnees($_POST['phone_line_designation'] ?? ""),
                    'phone_line_agent_id' => isset($_POST['phone_line_agent_id']) && $_POST['phone_line_agent_id'] !== '' ? (int) $_POST['phone_line_agent_id'] : null,
                    'phone_line_building_id' => isset($_POST['phone_line_building_id']) && $_POST['phone_line_building_id'] !== '' ? (int) $_POST['phone_line_building_id'] : null,
                    'phone_line_offer_id' => isset($_POST['phone_line_offer_id']) && $_POST['phone_line_offer_id'] !== '' ? (int) $_POST['phone_line_offer_id'] : null,
                ];

                // Si les données sont valides selon la fonction validatePhoneLine()
                if ($this->validatePhoneLine($data)) {

                    // Mise à jour de la ligne téléphonique en base de données selon l'ID avec les nouvelles valeurs
                    $this->phoneLineRepo->updatePhoneLine($id, $data);

                    // Préparation des paramètres pour la redirection après mise à jour
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'phone_line/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'phone_line_number',
                        'order' => $_GET['order'] ?? 'asc',
                        'page_number' => $_GET['page_number'] ?? 1,
                    ];

                    // Redirection vers la liste des lignes téléphoniques avec les paramètres conservés
                    header("Location: /index.php?" . http_build_query($params));
                    exit;

                } else {
                    // Préparation des variables à afficher dans le formulaire après soumission
                    // On récupère les valeurs saisies pour les réafficher même si elles ont été validées
                    // Permet de conserver les valeurs saisies par l'utilisateur.
                    // en cas d'erreur cela évite à l'utilisateur d'avoir à tout resaisir
                    $phone_line_number = $data['phone_line_number'];
                    $phone_line_status = $data['phone_line_status'];
                    $phone_line_termination_number = $data['phone_line_termination_number'];
                    $phone_line_termination_date = $data['phone_line_termination_date'];
                    $phone_line_box_return_date = $data['phone_line_box_return_date'];
                    $phone_line_designation = $data['phone_line_designation'];
                    $phone_line_agent_id = $data['phone_line_agent_id'];
                    $phone_line_building_id = $data['phone_line_building_id'];
                    $phone_line_offer_id = $data['phone_line_offer_id'];
                    
                    // Message d'erreur si les données sont incorrectes.
                    echo "<p>Les données sont erronées ou incomplètes.</p>";
                }
            }

            // Tableau définissant les types possible pour les offres: 
            $statues = ['en service', 'résiliée'];

            $csrf_token = CsrfToken::generateNamedToken('phone_line_edit');

            // Chargement de la vue correpsondante (affichage de la page HTML)
            require __DIR__ . '/../views/phone_line/phone_line_edit_form.php';
        }

        // -------------- FONCTION PRIVEE DE VALIDATION DES DONNEES. --------------
        // Vérifie que les champs ne sont pas vides.
        private function validatePhoneLine(array $data): bool 
        {
            return !empty($data['phone_line_number']) 
                && !empty($data['phone_line_status']);
        }
    }
?>