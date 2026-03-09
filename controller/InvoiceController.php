<?php

    // Controller pour la table Invoice. Sert d'intermédiaire entre la couche vue (html) et la couche "modèle" (InvoiceRepository)
    // Contient la logique métier et contrôle le flux des données.

    require_once __DIR__ . '/../model/invoice.php';     // Import du fichier gérant les requêtes SQL.
    require_once __DIR__ . '/../traitement_form.php';       // Import du fichier nettoyant les données saisies dans les dans les champs de formulaire.
    require_once __DIR__ . '/../classes/indexSort.php';     // Import du fichier gérant le tri et la pagination
    require_once __DIR__ . '/../classes/csrfToken.php';     // Import du fichier gérant la création et la vérification des tokens CSRF

    class InvoiceController 
    {
        private InvoiceRepository $invoiceRepo;     // Déclaration d'une propriété privée contenant l'instance du repository (accès aux données).
    
        // Constructeur permettant la connexion à la BDD.
        public function __construct(PDO $pdo) 
        {
            // Instanciation du repository avec la connexion PDO passée en argument.
            $this->invoiceRepo = new InvoiceRepository($pdo);
        }

        // -------------- FONCTION AFFICHANT LA LISTE DE TOUTES LES FACTURES (page invoice_index.php). --------------
        public function index() 
        {
            //-------------- GESTION DE LA SUPPRESSION --------------

            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer':
            if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_invoice'], $_POST['invoice_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer une facture
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->invoiceRepo->deleteInvoice((int)$_POST['invoice_id']);

                // Préparation des paramètres pour la redirection après ajout
                // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                $params = [
                    'page' => 'invoice/index',
                    'limit' => $_GET['limit'] ?? 10,
                    'search' => $_GET['search'] ?? '',
                    'sort' => $_GET['sort'] ?? 'invoice_number',
                    'order' => $_GET['order'] ?? 'asc',
                    'page_number' => $_GET['page_number'] ?? 1,
                ];

                // Redirection vers la page index pour éviter le rechargement du formulaire.
                header("Location: /index.php? " . http_build_query($params));
                exit;   // Stop le script.
            }

            // -------------- GESTION DU TRI ET PAGINATION --------------

            // Initialisation de la classe IndexSort
            $indexSort = new IndexSort($_GET, '/index.php');

            // On récupère le nombre total de factures correspondant à la recherche actuelle.
            // Cela sert notamment pour la pagination afin de savoir combien de pages sont nécessaires.
            $indexSort->totalResults = $this->invoiceRepo->countAll($indexSort->search);

            // Récupération des factures à afficher avec tri et pagination
            // Paramètres passés à la fonction getAll :
            // - $indexSort->sort : colonne sur laquelle trier
            // - $indexSort->order : ordre de tri ('asc' ou 'desc')
            // - $indexSort->search : terme de recherche pour filtrer les résultats
            // - $indexSort->limit : nombre de résultats par page
            // - $indexSort->offset : décalage pour la pagination (ex : page 2 commence après les 10 premiers)
            $invoices = $this->invoiceRepo->getAll(
                $indexSort->sort,
                $indexSort->order,
                $indexSort->search,
                $indexSort->limit,
                $indexSort->offset
            );

            // Définition des colonnes dans le tableau HTML :
            // clé = nom de la colonne en BDD, valeur = label affiché dans l'entête.
            $colonnes = [
                'invoice_number' => 'N° de facture',
                'invoice_date' => 'Date',
                'invoice_amount' => 'Montant(€)',
                'invoice_status' => 'Statut',
                'invoice_account_number' => 'N° de compte',
                'invoice_sub_account_number' => 'N° de sous-compte',
                'provider_name' => 'Opérateur'
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
            require __DIR__ . '/../views/invoice/invoice_index.php';
        }

        // -------------- FONCTION AFFICHANT LE DETAIL D'UNE FACTURE (page invoice_detail.php). --------------
        public function detail (int $id) 
        {
            // Si la requête HTTP est un POST et si l'on a cliqué sur 'supprimer':
            if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_invoice'], $_POST['invoice_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer une facture
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->invoiceRepo->deleteInvoice((int)$_POST['invoice_id']);

                // Redirection vers la page index pour éviter le rechargement du formulaire.
                header("Location: /index.php?page=invoice/index");
                exit;   // Stop le script.
            }

            // Récupération des informations d'un bâtiment depuis la BDD.
            $invoice = $this->invoiceRepo->getById($id);

            // Si aucune facture n'est trouvée, on renvoie une erreur 404.
            if(!$invoice) {
                http_response_code(404);
                echo "<p>Facture introuvable.</p>";
                exit;
            }

            $csrf_token = CsrfToken::generateToken();
            
            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/invoice/invoice_detail.php';
        }

        // -------------- FONCTION PERMETTANT L'AJOUT D'UNE FACTURE (page invoice_form.php). --------------
        public function add() 
        {
            // Seuls admin et super-admin peuvent ajouter une facture
            authorize(['admin', 'super-admin']);

            // Récupération des opérateurs
            $providers = $this->invoiceRepo->getAllProviders();

            // Tableau définissant les statuts possible pour les factures
            $statuses = ['Acquittée', 'En attente', 'Refusée'];

            // Si le formulaire a été soumis
            if($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Nettoie et récupère les données du formulaire.
                $data = [
                    'invoice_number' => valide_donnees($_POST['invoice_number'] ?? ""),
                    'invoice_date' => valide_donnees($_POST['invoice_date'] ?? ""),
                    'invoice_amount' => valide_donnees($_POST['invoice_amount'] ?? ""),
                    'invoice_account_number' => valide_donnees($_POST['invoice_account_number'] ?? ""),
                    'invoice_sub_account_number' => valide_donnees($_POST['invoice_sub_account_number'] ?? ""),
                    'invoice_status' => $_POST['invoice_status'] ?? "",
                    'invoice_provider_id' => $_POST['invoice_provider_id']
                ];

                // Vérifie la validité des données 
                if($this->validateInvoice($data)) {
                
                    // Ajoute en base de données
                    $this->invoiceRepo->addInvoice($data);

                    // Préparation des paramètres pour la redirection après ajout
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'invoice/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'invoice_number',
                        'order' => $_GET['order'] ?? 'asc',
                        'page_number' => $_GET['page_number'] ?? 1,
                    ];

                    // Redirection vers la liste des factures après ajout.
                    header("Location: /index.php?" . http_build_query($params));
                    exit;

                } else {
                    // Si les données sont incorrectes, on affiche un message d'erreur
                    echo "<p>Les données sont erronées ou incomplètes.</p>";
                }
            }

            $csrf_token = CsrfToken::generateToken();

            // Chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/invoice/invoice_form.php';
        }

        // -------------- FONCTION PERMETTANT LA MODFICATION D'UNE FACTURE (page invoice_edit_form.php). --------------
        public function edit(int $id) 
        {
            // Seuls admin et super-admin peuvent modifier une facture
            authorize(['admin', 'super-admin']);

            // Récupère la facture correspondante à l'ID fourni.
            $invoice = $this->invoiceRepo->getById($id);
            $providers = $this->invoiceRepo->getAllProviders();

            // Tableau définissant les statuts possible pour les factures
            $statuses = ['Acquittée', 'En attente', 'Refusée'];

            // Si aucune facture n'est trouvée, on arrête le script
            if(!$invoice) {
                http_response_code(404);
                echo "<p>Facture introuvable.</p>";
                exit;
            }

            // Si le formulaire n'a pas été soumis, on préremplit les champs avec les valeurs de la BDD
            $number = $invoice['invoice_number'];
            $date = $invoice['invoice_date'];
            $amount = $invoice['invoice_amount'];
            $account_number = $invoice['invoice_account_number'];
            $sub_account_number = $invoice['invoice_sub_account_number'];
            $status = $invoice['invoice_status'];
            $provider_id = $invoice['invoice_provider_id'];

            // Si le formulaire est soumis
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkToken($_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                // Vérifie et récupère les nouvelles valeurs saisies.
                $data = [
                    'invoice_number' => valide_donnees($_POST['invoice_number'] ?? ""),
                    'invoice_date' => valide_donnees($_POST['invoice_date'] ?? ""),
                    'invoice_amount' => valide_donnees($_POST['invoice_amount'] ?? ""),
                    'invoice_account_number' => valide_donnees($_POST['invoice_account_number'] ?? ""),
                    'invoice_sub_account_number' => valide_donnees($_POST['invoice_sub_account_number'] ?? ""),
                    'invoice_status' => $_POST['invoice_status'] ?? "",
                    'invoice_provider_id' => $_POST['invoice_provider_id'] 
                ];

                // Vérifie que les données sont valides avant mise à jour
                if($this->validateInvoice($data)) {

                    // Mis à jour
                    $this->invoiceRepo->updateInvoice($id, $data);

                    // Préparation des paramètres pour la redirection après mise à jour
                    // On conserve les paramètres actuels de la liste (tri, recherche, pagination)
                    $params = [
                        'page' => 'invoice/index',
                        'limit' => $_GET['limit'] ?? 10,
                        'search' => $_GET['search'] ?? '',
                        'sort' => $_GET['sort'] ?? 'invoice_number',
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
                    $number = $data['invocie_number'];
                    $date = $data['invoice_date'];
                    $amount = $data['invocie_amount'];
                    $account_number = $data['invoice_account_number'];
                    $sub_account_number = $data['invoice_sub_account_number'];
                    $status = $data['invoice_status'];
                    $provider_id = $data['invoice_provider_id'];

                    // Message d'erreur si les données sont incorrectes.
                    echo "<p>Les données sont erronées ou incomplètes.</p>";
                }
            } 

             $csrf_token = CsrfToken::generateToken();
             
            // chargement de la vue correspondante (affichage de la page HTML)
            require __DIR__ . '/../views/invoice/invoice_edit_form.php';
        }

        // -------------- FONCTION PRIVEE DE VALIDATION DES DONNEES. --------------
        private function validateInvoice(array $data): bool 
        {
            return !empty($data['invoice_number'])
                && !empty($data['invoice_date'])
                && !empty($data['invoice_amount'])
                && !empty($data['invoice_account_number'])
                && !empty($data['invoice_status'])
                && !empty($data['invoice_provider_id']);
        }
    }
