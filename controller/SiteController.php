<?php

    // Controller pour la table Site. Sert d'intermédiaire entre la couche vue (html) et la couche "modèle" (SiteRepository)
    // Contient la logique métier et contrôle le flux des données.

    require_once __DIR__ . '/../model/site.php';            // Import du fichier gérant les requêtes SQL.
    require_once __DIR__ . '/../traitement_form.php';       // Import du fichier nettoyant les données saisies dans les champs de formulaire.
    require_once __DIR__ . '/../classes/indexSort.php';     // Import du fichier gérant le tri et la pagination
    require_once __DIR__ . '/../classes/csrfToken.php';     // Import du fichier gérant la création et la vérification des tokens CSRF

    class SiteController
    {
        private SiteRepository $siteRepo;       // Déclaration d'une propriété privée contenant l'instance du repository (accès aux données).

        // Constructeur permettant la connexion à la BDD.
        public function __construct(PDO $pdo) 
        {

            // Instanciation du repository avec la connexion PDO passée en argument.
            $this->siteRepo = new SiteRepository($pdo);
        }

        //-------------- FONCTION AFFICHANT LA LISTE DE TOUS LES SITES (page site_index.php). --------------
        public function index() 
        {

            // -------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur "supprimer":
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_site'], $_POST['site_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer un site
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }
                
                $this->siteRepo->deleteSite((int)$_POST['site_id']);

                // Préparation des paramètres pour la redirection après ajout
                // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                $params = [
                    'page' => 'site/index',
                    'limit' => $_GET['limit'] ?? 10,
                    'search' => $_GET['search'] ?? '',
                    'sort' => $_GET['sort'] ?? 'site_name',
                    'order' => $_GET['order'] ?? 'asc',
                    'page_number' => $_GET['page_number'] ?? 1,
                ];

                // Redirection vers la page index pour éviter le rechargement du formulaire
                header("Location: /index.php?" . http_build_query($params));
                exit;       // stop l'exécution du script.
            }

            // -------------- GESTION DU TRI ET PAGINATION --------------

            // Initialisation de la classe IndexSort
            $indexSort = new IndexSort($_GET, '/index.php');

            // On récupère le nombre total de sites correspondant à la recherche actuelle.
            // Cela sert notamment pour la pagination afin de savoir combien de pages sont nécessaires.
            $indexSort->totalResults = $this->siteRepo->countAll($indexSort->search);

            // Récupération des sites à afficher avec tri et pagination
            // Paramètres passés à la fonction getAll :
            // - $indexSort->sort : colonne sur laquelle trier
            // - $indexSort->order : ordre de tri ('asc' ou 'desc')
            // - $indexSort->search : terme de recherche pour filtrer les résultats
            // - $indexSort->limit : nombre de résultats par page
            // - $indexSort->offset : décalage pour la pagination (ex : page 2 commence après les 10 premiers)
            $sites = $this->siteRepo->getAll(
                $indexSort->sort,
                $indexSort->order,
                $indexSort->search,
                $indexSort->limit,
                $indexSort->offset
            );

            // Définition des colonnes dans le tableau HTML :
            // clé = nom de la colonne en BDD, valeur = label affiché dans l'entête.
            $colonnes = [
                'site_name' => 'Nom du site'
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
            require __DIR__ . '/../views/site/site_index.php';
        }

        // -------------- FONCTION AFFICHANT LE DETAIL D'UN SITE (page site_detail.php). --------------
        public function detail(int $id) 
        {

            // -------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur "supprimer":
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_site'], $_POST['site_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer un site
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }
                
                $this->siteRepo->deleteSite((int)$_POST['site_id']);

                // Redirection vers la page index pour éviter le rechargement du formulaire
                header("Location: /index.php?page=site/index");
                exit;       // stop l'exécution du script.
            }

            // Récupération des informations du site depuis la BDD.
            $site = $this->siteRepo->getById($id);

            // Si aucun site n'est trouvé, on renvoie une erreur 404.
            if (!$site) {
                http_response_code(404);
                echo "<p>Site introuvable.</p>";
                exit;
            }

            // Récupération des bâtiments associé au site
            $buildings = $this->siteRepo->getBuildingsBySiteId($id);

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correpsondante (affichage de la page HTML)
            require __DIR__ . '/../views/site/site_detail.php';
        }

        // -------------- FONCTION PERMETTANT L'AJOUT D'UN SITE (page site_form.php). --------------
        public function add() 
        {

            // Seuls admin et super-admin peuvent ajouter un site
                authorize(['admin', 'super-admin']);

            // Si le formulaire a été soumis
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Nettoie et récupère les données du formulaire.
                $data = [
                    'site_name' => valide_donnees($_POST['site_name'] ?? "")
                ];

                 // Si les données sont valides selon la fonction validateSite()
                if ($this->validateSite($data)) {
                    // Ajout du site en base de données.
                    $this->siteRepo->addSite($data);

                    // Préparation des paramètres pour la redirection après ajout
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'site/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'site_name',
                        'order' => $_GET['order'] ?? 'asc',
                        'page_number' => $_GET['page_number'] ?? 1,
                    ];

                    // Redirection vers la liste des sites après ajout
                    header("Location: /index.php?" . http_build_query($params));
                    exit;

                } else {
                    // Si les données sont incorrectes, on affiche message d'erreur
                    echo "<p>Les données sont erronées ou incomplètes";
                }
            }

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/site/site_form.php';
        }

        // -------------- FONCTION PERMETTANT LA MODFICATION D'UN SITE (page site_edit_form.php). --------------
        public function edit(int $id) 
        {

            // Seuls admin et super-admin peuvent modifier un site
            authorize(['admin', 'super-admin']);

            // Récupère le site correpsondant à l'ID fourni.
            $site = $this->siteRepo->getById($id);

            // Si aucun site n'est trouvé on arrête le script
            if (!$site) {
                http_response_code(404);
                echo "<p>Site introuvable.</p>";
                exit;
            }

            // Si le formulaire n'a pas été soumis, on préremplit les champs avec les valeurs de la BDD
            $name = $site['site_name'];

            // Si le formulaire est soumis 
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Nettoie et récupère les nouvelles valeurs saisies.
                $data = [
                    'site_name' => valide_donnees($_POST['site_name'])
                ];

                // Si les données sont valides selon la fonction validateSite()
                if($this->validateSite($data)) {

                    // Mise à jour du site en base de données selon l'ID avec les nouvelles valeurs
                    $this->siteRepo->updateSite($id, $data);

                    // Préparation des paramètres pour la redirection après ajout
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'site/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'site_name',
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
                    $name = $data['site_name'];

                    // Message d'erreur si les données sont incorrectes.
                    echo "<p>Les données sont erronées ou incomplètes</p>";
                }

            } 

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/site/site_edit_form.php';
        }

        // -------------- FONCTION PRIVEE DE VALIDATION DES DONNEES. --------------
        // Vérifie que les champs ne sont pas vides et ne contiennent pas de caractères interdits
        private function validateSite(array $data): bool 
        {
            return !empty($data['site_name']);
        }
    }