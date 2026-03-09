<?php

    // Controller pour la table Offer. Sert d'intermédiaire entre la couche vue (html) et la couche "modèle" (OfferRepository)
    // Contient la logique métier et contrôle le flux des données. 

    require_once __DIR__ . '/../model/offer.php';           // Import du fichier gérant les requêtes SQL.
    require_once __DIR__ . '/../traitement_form.php';       // Import du fichier nettoyant les données saisies dans les dans les champs de formulaire.
    require_once __DIR__ . '/../classes/indexSort.php';     // Import du fichier gérant le tri et la pagination
    require_once __DIR__ . '/../classes/csrfToken.php';     // Import du fichier gérant la création et la vérification des tokens CSRF

    class OfferController 
    {
        private OfferRepository $offerRepo;     // Déclaration d'une propriété privée contenant l'instance du repository (accès aux données).
    
        // Constructeur permettant la connexion à la BDD.
        public function __construct(PDO $pdo) 
        {
            // Instanciation du repository avec la connexion PDO passée en argument.
            $this->offerRepo = new OfferRepository($pdo);
        }

        // -------------- FONCTION AFFICHANT LA LISTE DE TOUTES LES OFFRES (page offer_index.php). --------------
        public function index() 
        {

            //-------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer':
            if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_offer'], $_POST['offer_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer une offre
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->offerRepo->deleteOffer((int)$_POST['offer_id']);

                // Préparation des paramètres pour la redirection après ajout
                // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                $params = [
                    'page' => 'offer/index',
                    'limit' => $_GET['limit'] ?? 10,
                    'search' => $_GET['search'] ?? '',
                    'sort' => $_GET['sort'] ?? 'offer_name',
                    'order' => $_GET['order'] ?? 'asc',
                    'page_number' => $_GET['page_number'] ?? 1,
                ];

                // Redirection vers la page index pour éviter le rechargement du formulaire.
                header("Location: /index.php?" . http_build_query($params));
                exit;   // Stop le script.
            }

            // -------------- GESTION DU TRI ET PAGINATION --------------
        
            // Initialisation de la classe IndexSort
            $indexSort = new Indexsort($_GET, '/index.php');

            // On récupère le nombre total d'offres correspondant à la recherche actuelle.
            // Cela sert notamment pour la pagination afin de savoir combien de pages sont nécessaires.
            $indexSort->totalResults = $this->offerRepo->countAll($indexSort->search);

            // Récupération des offres à afficher avec tri et pagination
            // Paramètres passés à la fonction getAll :
            // - $indexSort->sort : colonne sur laquelle trier
            // - $indexSort->order : ordre de tri ('asc' ou 'desc')
            // - $indexSort->search : terme de recherche pour filtrer les résultats
            // - $indexSort->limit : nombre de résultats par page
            // - $indexSort->offset : décalage pour la pagination (ex : page 2 commence après les 10 premiers)
            $offers = $this->offerRepo->getAll(
                $indexSort->sort,
                $indexSort->order,
                $indexSort->search,
                $indexSort->limit,
                $indexSort->offset
            );

            foreach ($offers as $offer) {
                $offer['offer_price'] = $this->formatPrice($offer['offer_price']);
            }
                        
            unset($offer);

            // Définition des colonnes dans le tableau HTML :
            // clé = nom de la colonne en BDD, valeur = label affiché dans l'entête.
            $colonnes = [
                'offer_name' => "Nom",
                'offer_price' => "Prix (€)",
                'offer_context' => "Contexte",
                'offer_type' => "Type d'offre",
                'provider_name' => "Opérateur"
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
            require __DIR__ . '/../views/offer/offer_index.php';
        }

        // -------------- FONCTION AFFICHANT LE DETAIL D'UNE OFFRE (page offer_detail.php). --------------
        public function detail(int $id) 
        {
            //-------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer':
            if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_offer'], $_POST['offer_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer une offre
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->offerRepo->deleteOffer((int)$_POST['offer_id']);

                // Redirection vers la page index pour éviter le rechargement du formulaire.
                header("Location: /index.php?page=offer/index");
                exit;   // Stop le script.
            }

            // Récupération des informations d'une offre depuis la BDD.
            $offer = $this->offerRepo->getById($id);

            // Si aucune offre n'est trouvée, on renvoie une erreur 404.
            if (!$offer) {
                http_response_code(404);
                echo "<p>Offre introuvable.</p>";
                exit;
            }

            // Récupération des opérateurs pour les afficher sur la page de détail.
            $providers = $this->offerRepo->getProvidersByOfferId($id);
            $phone_lines = $this->offerRepo->getPhoneLinesByOfferId($id);

            $csrf_token = CsrfToken::generateToken();
            
            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/offer/offer_detail.php';
        }

        // -------------- FONCTION PERMETTANT L'AJOUT D'UNE OFFRE (page offer_form.php). --------------
        public function add() 
        {

            // Seuls admin et super-admin peuvent ajouter une offre
            authorize(['admin', 'super-admin']);

            // Si le formulaire a été soumis
            if($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                //Nettoie et récupère les données du formulaire.
                $data = [
                    'offer_name' => valide_donnees($_POST['offer_name'] ?? ""),
                    'offer_price' => valide_donnees($_POST['offer_price'] ?? ""),
                    'offer_context' => $_POST['offer_context'] ?? "",
                    'offer_type' => $_POST['offer_type'] ?? "",
                    'offer_provider_id' => isset($_POST['offer_provider_id']) ? (int) $_POST['offer_provider_id'] : null
                ];

                // Si les données sont valides selon la fonction validateOffer()
                if($this->validateOffer($data)) {
                    // Ajoute l'offre en base de données
                    $this->offerRepo->addOffer($data);

                    // Préparation des paramètres pour la redirection après ajout
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'offer/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'offer_name',
                        'order' => $_GET['order'] ?? 'asc',
                        'page_number' => $_GET['page_number'] ?? 1,
                    ];

                    // Redirection vers la liste des offres après ajout
                    header("Location: /index.php?" . http_build_query($params));
                    exit;

                } else {
                    // Si les données sont incorrectes, on prépare un message d'erreur à afficher dans la vue.
                    echo  "<p>Les données sont erronées ou incomplètes.</p>";
                }
            }

            $providers = $this->offerRepo->getAllProviders();
            $offer_types = ['analogique', 'mobile', 'voip', 'adsl', 'fibre', 'numeris'];
            $offer_contexts = ['canut', 'sipperec', 'marché SFR', 'marché Orange', 'hors marché'];

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/offer/offer_form.php';
        }

        // -------------- FONCTION PERMETTANT LA MODFICATION D'UNE OFFRE (page offer_edit_form.php). --------------
        public function edit(int $id) 
        {

            // Seuls admin et super-admin peuvent modifier une offre
            authorize(['admin', 'super-admin']);

            // Récupère l'offre correspondante à l'ID fourni.
            $offer = $this->offerRepo->getById($id);

            // Si aucune offre n'est trouvée, on arrête le script
            if(!$offer) {
                http_response_code(404);
                echo "<p>Offre introuvable.</p>";
                exit;
            }

            // Si le formulaire n'a pas été soumis, on préremplit les champs avec les valeurs de la BDD
            $offer_name = $offer['offer_name'];
            $offer_price = $offer['offer_price'];
            $offer_context = $offer['offer_context'];
            $offer_type = $offer['offer_type'];
            $offer_provider_id = $offer['offer_provider_id'];

            // Si le formulaire est soumis
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Nettoie et récupère les nouvelles valeurs saisies.
                $data = [
                    'offer_name' => valide_donnees($_POST['offer_name'] ?? ""),
                    'offer_price' => valide_donnees($_POST['offer_price'] ?? ""),
                    'offer_context' => $_POST['offer_context'] ?? "",
                    'offer_type' => $_POST['offer_type'] ?? "",
                    'offer_provider_id' => isset($_POST['offer_provider_id']) ? (int) $_POST['offer_provider_id'] : null
                ];

                // Si les données sont valides selon la fonction validateOffer()
                if ($this->validateOffer($data)) {

                    // Mise à jour de l'offre en base de données selon l'ID avec les nouvelles valeurs
                    $this->offerRepo->updateOffer($id, $data);

                    // Préparation des paramètres pour la redirection après mise à jour
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'offer/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'offer_name',
                        'order' => $_GET['order'] ?? 'asc',
                        'page_number' => $_GET['page_number'] ?? 1,
                    ];

                    // Redirection vers la liste des offres avec les paramètres conservés
                    header('Location: /index.php?' . http_build_query($params));
                    exit;

                } else {
                    // Préparation des variables à afficher dans le formulaire après soumission
                    // On récupère les valeurs saisies pour les réafficher même si elles ont été validées
                    // Permet de conserver les valeurs saisies par l'utilisateur.
                    // en cas d'erreur cela évite à l'utilisateur d'avoir à tout resaisir
                    $offer_name = $data['offer_name'];
                    $offer_price = $data['offer_price'];
                    $offer_context = $data['offer_context'];
                    $offer_type = $data['offer_type'];
                    $offer_phone_line_id = $data['offer_phone_line_id'];
                    $offer_provider_id = $data['offer_provider_id'];

                    // Message d'erreur si les données sont incorrectes.
                    echo "<p>Les données sont erronées ou incomplètes.</p>";
                }
            } 

            $providers = $this->offerRepo->getAllProviders();
            $offer_types = ['analogique', 'mobile', 'voip', 'adsl', 'fibre', 'numéris'];
            $offer_contexts = ['Canut', 'Sipperec', 'Marché SFR', 'Marché Orange', 'Hors marché'];

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correspondante (affichage de la page HTML)
            require_once __DIR__ . '/../views/offer/offer_edit_form.php';
        }

        // -------------- FONCTION PRIVEE DE VALIDATION DES DONNEES. --------------
        private function validateOffer(array $data): bool 
        {
            // Valeurs autorisées
            $offer_types = ['analogique', 'mobile', 'voip', 'adsl', 'fibre', 'numeris'];
            $offer_contexts = ['canut', 'sipperec', 'marché SFR', 'marché Orange', 'hors marché'];

            // Vérifie les champs obligatoires
            if (empty($data['offer_name']) || empty($data['offer_price'])) {
                return false;
            }

            // Vérifie que le prix est un nombre positif
            if (!is_numeric($data['offer_price']) || $data['offer_price'] < 0) {
                return false;
            }

            // Vérifie que le type et le contexte sont valides
            if (!in_array($data['offer_type'], $offer_types, true)) {
                return false;
            }

            if (!in_array($data['offer_context'], $offer_contexts, true)) {
                return false;
            }

            return true;
        }

        private function formatPrice($price) 
        {
            // Assurer que le prix est traité comme un nombre décimal

            $safePrice = filter_var($price, FILTER_VALIDATE_FLOAT);

            if ($safePrice === false) {
                // Message d'erreur et retourne la valeur par défaut
                error_log("Erreur de formatage de prix: valeur non numérique");
                return '0.00';
            }

            // Formater avec précision à 2 décimales
            return number_format($safePrice, 2, ',', '');
        }
    }
?>