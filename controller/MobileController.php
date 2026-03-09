<?php

    // Controller pour la table Mobile. Sert d'intermédiaire entre la couche vue (html) et la couche "modèle" (MobileRepository)
    // Contient la logique métier et contrôle le flux des données.

    require_once __DIR__ . '/../model/mobile.php';          // Import du fichier gérant les requêtes SQL.
    require_once __DIR__ . '/../traitement_form.php';      // Import du fichier nettoyant les données saisies dans les dans les champs de formulaire.
    require_once __DIR__ . '/../classes/indexSort.php';   // Import du fichier gérant le tri et la pagination
    require_once __DIR__ . '/../classes/csrfToken.php';     // Import du fichier gérant la création et la vérification des tokens CSRF

    class MobileController 
    {
        private MobileRepository $mobileRepo;     // Déclaration d'une propriété privée contenant l'instance du repository (accès aux données).

        // Constructeur permettant la connexion à la BDD.
        public function __construct(PDO $pdo) 
        {

            // Instaciation du repository avec connexion PDO passée en argument.
            $this->mobileRepo = new MobileRepository($pdo);
        }

        // -------------- FONCTION AFFICHANT LA LISTE DE TOUS LES MOBILES (page mobile_index.php). --------------
        public function index() 
        {
            
            //-------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_mobile'], $_POST['mobile_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer un mobile
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->mobileRepo->deleteMobile((int)$_POST['mobile_id']);

                $params = [
                    'page' => 'mobile/index',
                    'search' => $_GET['search'] ?? '',
                    'sort' => $_GET['sort'] ?? '',
                    'order' => $_GET['order'] ?? 'asc',
                    'page_number' => $_GET['page_number'] ?? 1,
                    'limit' => $_GET['limit'] ?? 10
                ];

                // Redirection vers la page index pour éviter le rechargement du formulaire.
                // $indexSort = new IndexSort($_GET, "index.php?page=mobile/index");
                // header("Location: " . $indexSort->getReturnUrl());
                header('Location: /index.php?' . http_build_query($params));
                exit;       // Stop l'exécution du script.
            }

            // -------------- GESTION DU TRI, RECHERCHE ET PAGINATION --------------

            // Instanciation de la classe IndexSort (elle récupère tous les paramètres GET)
            $indexSort = new IndexSort($_GET, '/index.php');

            // On récupère le nombre total de mobiles correspondant à la recherche actuelle.
            // Cela sert notamment pour la pagination afin de savoir combien de pages sont nécessaires.
            $indexSort->totalResults = $this->mobileRepo->countAll($indexSort->search);

            // Récupération des mobiles selon les paramètres de tri et pagination
            $mobiles = $this->mobileRepo->getAll(
                $indexSort->sort,
                $indexSort->order,
                $indexSort->search,
                $indexSort->limit,
                $indexSort->offset
            );

            // Définition des colonnes dans le tableau HTML :
            // clé = nom de la colonne en BDD, valeur = label affiché dans l'entête.
            $colonnes = [
                'mobile_brand' => 'Marque',
                'mobile_model' => 'Modèle',
                'mobile_imei' => 'IMEI',
                // 'mobile_purchase_date' => "Date d'achat",
                // 'mobile_exit_date' => "Date de sortie du parc",
                'mobile_reconditioned' => 'Reconditionné',
                'mobile_status' => 'Statut',
                'phone_line_number' => 'Ligne téléphonique',
                'agent_fullname' => 'Agent'
            ];

            // Tableau qui contiendra les informations pour le tri (flèches et URLs)
            $triInfos = [];

            // Pour chaque colonne :
            // - 'arrow' : flèche indiquant si le tri est ascendant ou descendant sur cette colonne
            // - 'url'   : URL permettant de trier par cette colonne (avec les paramètres GET corrects) 
            foreach ($colonnes as $colonne => $label) {
                $triInfos[$colonne] = [
                    'arrow' => $indexSort->arrowFor($colonne),
                    'url' => $indexSort->sortUrl($colonne)
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

            // Chargement de la vue
            require __DIR__ . '/../views/mobile/mobile_index.php';
        }

        // -------------- FONCTION AFFICHANT LE DETAIL D'UN MOBILE (page mobile_detail.php). --------------
        public function detail(int $id)
        {
            // Récupération des informations du mobile depuis la BDD
            $mobile = $this->mobileRepo->getById($id);

            // Si aucun mobile n'est trouvé, on renvoie une erreur 404
            if(!$mobile) {
                http_response_code(404);
                echo "<p>Mobile introuvable.</p>";
                exit;
            }

            //-------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_mobile'], $_POST['mobile_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer un mobile
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->mobileRepo->deleteMobile((int)$_POST['mobile_id']);

                // Redirection vers la page index pour éviter le rechargement du formulaire.
                header("Location: /index.php?page=mobile/index");
                exit;       // Stop l'exécution du script.
            }

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/mobile/mobile_detail.php';
        }

        // -------------- FONCTION PERMETTANT L'AJOUT D'UN MOBILE (page mobile_form.php). --------------
        public function add() 
        {

            // Seuls admin et super-admin peuvent ajouter un mobile
            authorize(['admin', 'super-admin']);

            $phone_lines = $this->mobileRepo->getAllPhoneLines();
            $agents = $this->mobileRepo->getAllAgents();

            // Si le formualire a été soumis
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Nettoie et récupère les données du formulaire
                $data = [
                    'mobile_brand' => valide_donnees($_POST['mobile_brand'] ?? ""),
                    'mobile_model' => valide_donnees($_POST['mobile_model'] ?? ""),
                    'mobile_imei' => valide_donnees($_POST['mobile_imei'] ?? ""),
                    'mobile_purchase_date' => $_POST['mobile_purchase_date'] ?? "",
                    'mobile_exit_date' => !empty($_POST['mobile_exit_date']) ? $_POST['mobile_exit_date'] : null,
                    'mobile_reconditioned' => $_POST['mobile_reconditioned'] ?? "",
                    'mobile_status' => $_POST['mobile_status'],
                    'mobile_phone_line_id' => isset($_POST['mobile_phone_line_id']) && $_POST['mobile_phone_line_id'] !== '' ? (int) $_POST['mobile_phone_line_id'] : null,
                    'mobile_agent_id' => isset($_POST['mobile_agent_id']) && $_POST['mobile_agent_id'] !== '' ? (int) $_POST['mobile_agent_id'] : null,
                ];

                // Si les données sont valides selon la fonction validateMobile()
                if ($this->validateMobile($data)) {

                    // Ajout du mobile en base de données.
                    $this->mobileRepo->addMobile($data);

                    // Préparation des paramètres pour la redirection après ajout
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'mobile/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'mobile_brand',
                        'order' => $_GET['order'] ?? 'asc',
                        'page_number' => $_GET['page_number'] ?? 1,
                    ];

                    // Redirection vers la liste des mobiles après ajout
                    header("Location: /index.php?" . http_build_query($params));
                    exit;

                } else {
                    // Si les données sont incorrectes, on prépare un message d'erreur à afficher dans la vue.
                    echo  "<p>Les données sont erronées ou incomplètes.</p>";
                }
            }

            $csrf_token = CsrfToken::generateToken();

            // Chargment de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/mobile/mobile_form.php';
        }

        // -------------- FONCTION PERMETTANT LA MODFICATION D'UN MOBILE (page mobile_edit_form.php). --------------
        public function edit(int $id) 
        {
            // Seuls admin et super-admin peuvent modifier un mobile
            authorize(['admin', 'super-admin']);

            // Récupère le mobile correspondant à l'id fourni
            $mobile = $this->mobileRepo->getById($id);
            $agents = $this->mobileRepo->getAllAgents();
            $phone_lines = $this->mobileRepo->getAllPhoneLines();

            // Si aucun mobile n'est trouvé, on arrête le script
            if (!$mobile) {
                http_response_code(404);
                echo "<p>Agent introuvable.</p>";
                exit;
            }

            // Si le formulaire n'a pas été soumis, on préremplit les champs avec les valeurs de la BDD
            $mobile_brand = $mobile['mobile_brand'];
            $mobile_model = $mobile['mobile_model'];
            $mobile_imei = $mobile['mobile_imei'];
            $mobile_purchase_date = $mobile['mobile_purchase_date'];
            $mobile_exit_date = $mobile['mobile_exit_date'];
            $mobile_reconditioned = $mobile['mobile_reconditioned'];
            $mobile_status = $mobile['mobile_status'];
            $mobile_phone_line_id = $mobile['mobile_phone_line_id'];
            $mobile_agent_id = $mobile['mobile_agent_id'];

            // Si le formulaire est soumis
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Nettoie et récupère les nouvelles valeurs saisies.
                $data = [
                    'mobile_brand' => valide_donnees($_POST['mobile_brand'] ?? ""),
                    'mobile_model' => valide_donnees($_POST['mobile_model'] ?? ""),
                    'mobile_imei' => valide_donnees($_POST['mobile_imei'] ?? ""),
                    'mobile_purchase_date' => $_POST['mobile_purchase_date'] ?? "",
                    'mobile_exit_date' => !empty($_POST['mobile_exit_date']) ? $_POST['mobile_exit_date'] : null,
                    'mobile_reconditioned' => $_POST['mobile_reconditioned'] ?? "",
                    'mobile_status' => $_POST['mobile_status'],
                    'mobile_phone_line_id' => isset($_POST['mobile_phone_line_id']) && $_POST['mobile_phone_line_id'] !== '' ? (int) $_POST['mobile_phone_line_id'] : null,
                    'mobile_agent_id' => isset($_POST['mobile_agent_id']) && $_POST['mobile_agent_id'] !== '' ? (int) $_POST['mobile_agent_id'] : null,
                ];

                // Si les données sont valides selon la fonction validateMobile()
                if($this->validateMobile($data)) {

                    // Mise à jour du mobile en base de données selon l'ID avec les nouvelles valeurs
                    $this->mobileRepo->updateMobile($id, $data);

                    // Préparation des paramètres pour la redirection après mise à jour
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'mobile/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'mobile_brand',
                        'order' => $_GET['order'] ?? 'asc',
                        'page_number' => $_GET['page_number'] ?? 1,
                    ];

                    // Redirection vers la liste des mobiles avec les paramètres conservés
                    header("Location: /index.php?page=mobile/index");
                    exit;

                } else {
                    // Préparation des variables à afficher dans le formulaire après soumission
                    // On récupère les valeurs saisies pour les réafficher même si elles ont été validées
                    // Permet de conserver les valeurs saisies par l'utilisateur.
                    // en cas d'erreur cela évite à l'utilisateur d'avoir à tout resaisir
                    $mobile_brand = $data['mobile_brand'];
                    $mobile_model = $data['mobile_model'];
                    $mobile_imei = $data['mobile_imei'];
                    $mobile_purchase_date = $data['mobile_purchase_date'];
                    $mobile_exit_date = $data['mobile_exit_date'];
                    $mobile_reconditioned = $data['mobile_reconditioned'];
                    $mobile_status = $data['mobile_status'];
                    $mobile_phone_line_id = $data['mobile_phone_line_id'];
                    $mobile_agent_id = $data['mobile_agent_id'];

                    // Message d'erreur si le données sont incorrectes.
                    echo "<p>Les données sont erronnées ou incomplètes.</p>";
                }
            } 

            $statuses = ['en stock', 'activé', 'volé', 'perdu', 'cassé', 'vendu', 'sortie de parc'];

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/mobile/mobile_edit_form.php';
        }

        // -------------- FONCTION PRIVEE DE VALIDATION DES DONNEES. --------------
        // Vérifie que les champs ne sont pas vides.
        private function validateMobile(array $data): bool {

            return !empty($data['mobile_brand'])
                   && !empty($data['mobile_model'])
                   && !empty($data['mobile_imei'])
                   && !empty($data['mobile_purchase_date'])
                   && isset($data['mobile_reconditioned'])
                   && !empty($data['mobile_status']);
        }
    }
?>