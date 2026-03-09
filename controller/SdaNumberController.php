<?php

    // Controller pour la table Phone. Sert d'intermédiaire entre la couche vue (html) et la couche "modèle" (SdaNumberRepository)
    // Contient la logique métier et contrôle le flux des données.

    require_once __DIR__ . '/../model/sda_number.php';       // Import du fichier gérant les requêtes SQL.
    require_once __DIR__ . '/../traitement_form.php';       // Import du fichier nettoyant les données saisies dans les dans les champs de formulaire.
    require_once __DIR__ . '/../classes/csrfToken.php';     // Import du fichier gérant la création et la vérification des tokens CSRF
    require_once __DIR__ . '/../includes/roles.php';        // Import du fichier gératn les roles, utile ici car les numéros SDA et leur CRUD n'est disponible que sur la page de détail d'une ligne
    
    class SdaNumberController 
    {
        private SdaNumberRepository $sdaRepo;       // Déclaration d'une propriété privée contenant l'instance du repository (accès aux données).

        // Constructeur permettan la connexion à la BDD
        public function __construct(PDO $pdo) 
        {

            // Instanciation du repository avec conenxion PDO passée en argument
            $this->sdaRepo = new SdaNumberRepository($pdo);
        }

        // -------------- FONCTION PERMETTANT L'AJOUT D'UN NUMERO SDA (page sda_number_form.php). --------------
        public function add() 
        {
            // Seuls admin et super-admin peuvent ajouter un numéro SDA
            authorize(['admin', 'super-admin']);

            // Récupération de la ligne téléphonique à partir de laquelle on a cliqué sur ajouter un numéro sda.
            $selected_phone_line_id = $_GET['sda_phone_line_id'] ?? null;

            // Récupération des lignes téléphoniques
            $phone_lines = $this->sdaRepo->getAllPhoneLines();

            // On ne fait la requête que si un ID est bien fourni
            if ($selected_phone_line_id) {

                    $selected_phone_line = $this->sdaRepo->getSelectedPhoneLine($selected_phone_line_id);
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkNamedToken('sda_number_add', $_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $data = [
                    'sda_number' => valide_donnees($_POST['sda_number'] ?? ''),
                    'sda_phone_line_id' => (int)$_POST['sda_phone_line_id']
                ];

                if ($this->validateSda($data)) {

                    $this->sdaRepo->addSdaNumber($data);
                    header("Location: /index.php?page=phone_line/detail&id=" . $data['sda_phone_line_id']);
                    exit;
                } else {
                    echo "<p>Les données sont erronnées ou incomplètes.</p>";
                }
            }

            $csrf_token = CsrfToken::generateNamedToken('sda_number_add');

            require __DIR__ . '/../views/sda_number/sda_number_form.php';
        }

        // -------------- FONCTION PERMETTANT LA MODFICATION D'UN NUMERO SDA (page sda_number_edit_form.php). --------------
        public function edit(int $id) 
        {
            // Seuls admin et super-admin peuvent modifier un numéro SDA
            authorize(['admin', 'super-admin']);

            $sda = $this->sdaRepo->getById($id);

            if (!$sda) {
                http_response_code(404);
                echo "<p>Numéro SDA introuvable.</p>";
                exit;
            }

            // Récupération des lignes téléphoniques pour les afficher dans un select
            $phone_lines = $this->sdaRepo->getAllPhoneLines();

            // Récupération de la ligne actuellement associée au numéro sda
            $sda_number_phone_line_id = $sda['sda_phone_line_id'];
            $selected_phone_line_id = $sda_number_phone_line_id;

            // Valeur du numéro sda actuel
            $sda_number_value = $sda['sda_number'];

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!CsrfToken::checkNamedToken('sda_number_edit', $_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $data = [
                    'sda_number' => valide_donnees($_POST['sda_number'] ?? ''),
                    'sda_phone_line_id' => (int)$_POST['sda_phone_line_id']
                ];

                if ($this->validateSda($data)) {

                    $this->sdaRepo->updateSdaNumber($id, $data);

                    header("Location: /index.php?page=phone_line/detail&id=" . $data['sda_phone_line_id']);
                    exit;
                } else {
                    echo "<p>Les données sont erronnées ou incomplètes.</p>";
                }
            }

            $csrf_token = CsrfToken::generateNamedToken('sda_number_edit');

            require __DIR__ . '/../views/sda_number/sda_number_edit_form.php';
        }

        // -------------- FONCTION PERMETTANT LA SUPPRESSION D'UN NUMERO SDA --------------
        public function delete() 
        {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sda_id'], $_POST['sda_phone_line_id'], $_POST['csrf_token'])) {

                // Seuls admin et super-admin peuvent supprimer un numéro SDA
                authorize(['admin', 'super-admin']);

                if (!CsrfToken::checkNamedToken('sda_number_delete', $_POST['csrf_token'])) {
                    http_response_code(403);
                    echo "<p>Erreur CSRF: action non autorisée.</p>";
                    exit;
                }

                $this->sdaRepo->deleteSdaNumber((int)$_POST['sda_id']);

                $params = [
                    'page' => 'phone_line/detail',
                    'id' => (int) $_POST['sda_phone_line_id'],
                    'search' => $_GET['search'] ?? '',
                    'sort' => $_GET['sort'] ?? '',
                    'order' => $_GET['order'] ?? 'asc',
                    'page_number' => $_GET['page_number'] ?? 1,
                    'limit' => $_GET['limit'] ?? 10
                ];
                
                // Redirection pour éviter le rechargement du formulaire
                header('Location: /index.php?' . http_build_query($params));
                exit;
            }
        }

        // -------------- FONCTION PRIVEE DE VALIDATION DES DONNEES. --------------
        public function validateSda(array $data): bool 
        {

            return !empty($data['sda_number'])
                && !empty($data['sda_phone_line_id']);
        }
    }
?>