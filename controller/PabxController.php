<?php

    // Controller pour la table PABX. Sert d'intermédiaire entre la couche vue (html) et la couche "modèle" (PabxRepository)
    // Contient la logique métier et contrôle le flux des données.

    require_once __DIR__ . '/../model/pabx.php';              // Import du fichier gérant les requêtes SQL.
    require_once __DIR__ . '/../traitement_form.php';        // Import du fichier nettoyant les données saisies dans les dans les champs de formulaire.
    require_once __DIR__ . '/../classes/indexSort.php';     // Import du fichier gérant le tri et la pagination
    require_once __DIR__ . '/../classes/csrfToken.php';     // Import du fichier gérant la création et la vérification des tokens CSRF

    class PabxController 
    {
        private PabxRepository $pabxRepo;   // Déclaration d'une propriété privée contenant l'instance du repository (accès aux données).

        // Constructeur permettant la connexion à la BDD.
        public function __construct(PDO $pdo) 
        {
            // Instanciation du repository avec la connexion PDO passée en argument.
            $this->pabxRepo = new PabxRepository($pdo);
        }

        // -------------- FONCTION AFFICHANT LA LISTE DE TOUS LES PABX (page  pabx_index.php). --------------
        public function index() 
        {
            //-------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer' :
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_pabx'], $_POST['pabx_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer un PABX
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->pabxRepo->deletePabx((int)$_POST['pabx_id']);

                // Préparation des paramètres pour la redirection après ajout
                // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                $params = [
                    'page' => 'pabx/index',
                    'limit' => $_GET['limit'] ?? 10,
                    'search' => $_GET['search'] ?? '',
                    'sort' => $_GET['sort'] ?? 'pabx_brand',
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

            // On récupère le nombre total de pabx correspondant à la recherche actuelle.
            // Cela sert notamment pour la pagination afin de savoir combien de pages sont nécessaires.
            $indexSort->totalResults = $this->pabxRepo->countAll($indexSort->search);

            // Récupération des pabx à afficher avec tri et pagination
            // Paramètres passés à la fonction getAll :
            // - $indexSort->sort : colonne sur laquelle trier
            // - $indexSort->order : ordre de tri ('asc' ou 'desc')
            // - $indexSort->search : terme de recherche pour filtrer les résultats
            // - $indexSort->limit : nombre de résultats par page
            // - $indexSort->offset : décalage pour la pagination (ex : page 2 commence après les 10 premiers)
            $pabxs = $this->pabxRepo->getAll(
                $indexSort->sort,
                $indexSort->order,
                $indexSort->search,
                $indexSort->limit,
                $indexSort->offset
            );

            // Définition des colonnes dans le tableau HTML :
            // clé = nom de la colonne en BDD, valeur = label affiché dans l'entête.
            $colonnes = [
                'pabx_brand' => 'Marque',
                'pabx_model' => 'Modèle',
                'pabx_series_number' => 'Numéro de série',
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

            // Chargement de la vue correspondante (affichage de la page HTML)
            require_once __DIR__ . '/../views/pabx/pabx_index.php';
        }

        // -------------- FONCTION AFFICHANT LE DETAIL D'UN PABX (page pabx_detail.php). --------------
        public function detail(int $id) 
        {
            //-------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer' :
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_pabx'], $_POST['pabx_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer un PABX
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->pabxRepo->deletePabx((int)$_POST['pabx_id']);

                // Redirection vers la page index pour éviter le rechargement du formulaire.
                header("Location: /index.php?page=pabx/index");
                exit;       // Stop le script.
            }

            // Récupération des informations d'un pabx depuis la BDD
            $pabx = $this->pabxRepo->getById($id);

            // Si aucun pabx n'est trouvé, on renvoie une erreur 404.
            if(!$pabx) {
                http_response_code(404);
                echo "<p>PABX introuvable.</p>";
                exit;
            }

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/pabx/pabx_detail.php';
        }

        // -------------- FONCTION PERMETTANT L'AJOUT D'UN PABX (page pabx_form.php). --------------
        public function add() 
        {
            // Seuls admin et super-admin peuvent ajouter un PABX
            authorize(['admin', 'super-admin']);

            $buildings = $this->pabxRepo->getAllBuildings();

            // Si le formulaire a été soumis
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Vérifie et récupère les données du formulaire.
                $data = [
                    'pabx_brand' => valide_donnees($_POST['pabx_brand'] ?? ''),
                    'pabx_model' => valide_donnees($_POST['pabx_model'] ?? ''),
                    'pabx_series_number' => valide_donnees($_POST['pabx_series_number'] ?? ''),
                    'pabx_building_id' => isset($_POST['pabx_building_id']) && $_POST['pabx_building_id'] !== '' ? (int) $_POST['pabx_building_id'] : null 
                ];

                // Si les données sont valides selon la fonction validatePabx()
                if ($this->validatePabx($data)) {
                    // Ajout du pabx en base de données.
                    $this->pabxRepo->addPabx($data);

                    // Préparation des paramètres pour la redirection après ajout
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'pabx/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'pabx_brand',
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

            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/pabx/pabx_form.php';
        }

        // -------------- FONCTION PERMETTANT LA MODFICATION D'UN PABX (page pabx_edit_form.php). --------------
        public function edit(int $id) 
        {
            // Seuls admin et super-admin peuvent modifier un PABX
            authorize(['admin', 'super-admin']);

            // Récupère le pabx correspondant à l'ID fourni
            $pabx = $this->pabxRepo->getById($id);

            // Récupère tous les bâtiments pour les afficher dans un select
            $buildings = $this->pabxRepo->getAllBuildings();

            // Si aucun pabx n'est trouvé, renvoie une erreur 404
            if(!$pabx) {
                http_response_code(404);
                echo "<p>PABX introuvable.</p>";
                exit;
            }

            // Si le formulaire n'a pas été soumis, on préremplit les champs avec les valeurs de la BDD
            $pabx_brand = $pabx['pabx_brand'];
            $pabx_model = $pabx['pabx_model'];
            $pabx_series_number = $pabx['pabx_series_number'];
            $pabx_building_id = $pabx['pabx_building_id'];

            // Si le formulaire est soumis
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Nettoie et récupère les nouvelles valeurs saisies.
                $data = [
                    'pabx_brand' => valide_donnees($_POST['pabx_brand'] ?? ''),
                    'pabx_model' => valide_donnees($_POST['pabx_model'] ?? ''),
                    'pabx_series_number' => valide_donnees($_POST['pabx_series_number'] ?? ''),
                    'pabx_building_id' => isset($_POST['pabx_building_id']) && $_POST['pabx_building_id'] !== '' ? (int) $_POST['pabx_building_id'] : null 
                ];

                // Si les données sont valides selon la fonction validatePabx()
                if ($this->validatePabx($data)) {

                    // Mise à jour de l'agent en base de données selon l'ID avec les nouvelles valeurs
                    $this->pabxRepo->updatePabx($id, $data);

                    // Préparation des paramètres pour la redirection après mise à jour
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'pabx/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'pabx_brand',
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
                    $pabx_brand = $data['pabx_brand'];
                    $pabx_model = $data['pabx_model'];
                    $pabx_series_number = $data['pabx_series_number'];
                    $pabx_building_id = $data['pabx_building_id'];

                    // Message d'erreur si les données sont incorrectes.
                    echo "<p>Les données sont erronées ou incomplètes</p>";
                }
            } 

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/pabx/pabx_edit_form.php';
        }

        // -------------- FONCTION PRIVEE DE VALIDATION DES DONNEES. --------------
        private function validatePabx(array $data): bool 
        {

            return !empty($data['pabx_brand'])
                && !empty($data['pabx_model'])
                && !empty($data['pabx_series_number']);
        }
    }
?>