<?php

class IndexSort
{
    // Définition des propriétés publiques de la classe
    public string $sort;                // Nom de la colonne utilisée pour le tri
    public string $order;              // Ordre de tri: 'asc' ou 'desc (ascendant/descendant)
    public string $search;            // Terme de recherche
    public int $limit;               // Nombre de résultats par page
    public int $currentPage;        // Numéro de la page actuelle
    private array $queryParams;    // Copie locale des paramètres GET pour éviter l'utilisation directe de $_GET
    public int $offset;            // Décalage SQL calculé à partir de la page courante (OFFSET)
    public string $baseUrl;       // URL de base utilisée pour la pagination et les liens
    public int $totalResults = 0;    // Nombre total d'enregistrements (initialisé à 0)

    // ------------- CONSTRUCTEUR INITIALISANT L'OBJET -------------

    // Le constructeur initialise l'objet à partir des paramètres GET ou valeurs par défaut
    public function __construct(array $queryParams = [], string $baseUrl = '')
    {
        // Conservation les paramètres GET d'origine
        $this->queryParams = $queryParams;

        // Si 'sort' est défini dans l'URL, on l'utilise, sinon on trie par 'id' par défaut
        $this->sort = $queryParams['sort'] ?? 'id';

        // Récupération de l'ordre de tri ('asc' ou 'desc') et on converti en minuscule pour éviter les erreurs
        $this->order = strtolower($queryParams['order'] ?? 'asc');

        // Récupération de la chaîne de recherche, ici on supprime les éventuels espaces inutiles
        $this->search = trim($queryParams['search'] ?? '');

        // Si une recherche est effectuée → reset pagination
        if (!empty($this->search)) {
            $this->currentPage = 1;
            $this->queryParams['page_number'] = 1;
        } else {
            // Détermination de la page actuelle (uniquement si pas de recherche)
            if (isset($queryParams['page_number'])) {
                $this->currentPage = max((int)$queryParams['page_number'], 1);
            } else {
                $this->currentPage = $_SESSION['last_page'] ?? 1;
            }
        }
        $_SESSION['last_page'] = $this->currentPage;

        // Détermination du nombre de résultat par page
        if (isset($queryParams['limit'])) {
            $this->limit = max((int)$queryParams['limit'], 1);
        } else {
            $this->limit = isset($_SESSION['last_limit']) ? (int)$_SESSION['last_limit'] : 10;
        }
        $_SESSION['last_limit'] = $this->limit;

        // Détermination de la page actuelle 
        if (isset($queryParams['page_number'])) {
            $this->currentPage = max((int)$queryParams['page_number'], 1);
        } else {
            $this->currentPage = isset($_SESSION['last_page']) ? (int)$_SESSION['last_page'] : 1;
        }
        $_SESSION['last_page'] = $this->currentPage;

        // Calcule du décalage pour la requête SQL (OFFSET)
        $this->offset = ($this->currentPage - 1) * $this->limit;

        // Si l'URL de base n'est pas fournie, on la déduit automatiquement à partir du paramètre 'page'
        $this->baseUrl = $baseUrl ?: $this->detectBaseUrl($queryParams['page'] ?? '');
    }

    // ------------- METHODE PRIVEE DE DETECTION DE L'URL -------------
    // Cette méthode détecte automatiquement l'URL de base selon la page courant
    private function detectBaseUrl(string $pageParam): string
    {
        // Dictionnaire des routes connues pour chaque page index faisant références à une table.
        $routes = [
            'agent' => 'index.php?page=agent/index',
            'building' => 'index.php?page=building/index',
            'emergency' => 'index.php?page=emergency/index',
            'equipment' => 'index.php?page=equipment/index',
            'invoice' => 'index.php?page=invoice/index',
            'mobile' => 'index.php?page=mobile/index',
            'offer' => 'index.php?page=offer/index',
            'pabx' => 'index.php?page=pabx/index',
            'phone' =>'index.php?page=phone/index',
            'phone_line' => 'index.php?page=phone_line/index',
            'provider' => 'index.php?page=provider/index',
            'site' => 'index.php?page=site/index',
            'users' => 'index.php?page=users/index'
        ];

        // Si on est sur les équipements, on doit inclure le type (box, routeur, transmetteur)
        // stripos() recherche la position de la première occurence dans une chaîne, sans tenir compte de la casse
        if (stripos($pageParam, 'equipment') !== false) {
            $type = $_GET['type'] ?? '';
            return 'index.php?page=equipment/index' . ($type ? '&type=' . urlencode($type) : '');
        }

        // On parcourt les routes et cherches une correspondance partielle dans $pageParam
        foreach ($routes as $key => $url) {
            if (stripos($pageParam, $key) !== false) {
                return $url;    // Retourne l'URL correspondante si trouvée
            }
        }

        // Par défaut, retourne la page des agents si aucune correspondance
        return 'index.php';
    }

    // ------------- FONCTION GENERANT LES LIENS DE TRI -------------
    // Génère un lien de tri pour une colonne donnée
    public function sortUrl(string $colonne): string
    {
        // Si le tri est déjà effectuée sur cette colonne et que l'ordre est 'asc', on inverse vers 'desc'
        $newOrder = ($this->sort === $colonne && $this->order === 'asc') ? 'desc' : 'asc';

        // Fusionne les paramètres GET actuels avec les nouveaux paramètres de tri
        // array_merge permet de fusionner plusieurs tableau en un seul. 
        // Ici on fait fusion le tableau associatif contenu dans $_GET avec un tableau associatif contenant les correspondances pour les colonnes et
        // le nouvel ordre de tri.
        $params = array_merge($this->queryParams, [
            'sort' => $colonne,
            'order' => $newOrder,
        ]);

        // Construction et renvoie de l'URL encodée (ex: ?page=...&sort=nom&order=desc)
        return '?' . http_build_query($params);
    }

    // ------------- FONCTION RETOURNANT LA FLECHE (▲ ou ▼)  -------------
    // Retourne les flêches en fonction de l'état du tri ou affiche une flèche vide par défaut
    public function arrowFor(string $colonne): string
    {
        // Si la colonne actuelle est celle triée
        if ($this->sort === $colonne) {

            // On affiche flèche vers le haut pour 'asc' et vers le bas pour 'desc'
            return $this->order === 'asc' ? '▲' : '▼';
        }

        // Sinon, flèche neutre par défaut :
        return '△';
    }

    // ------------- FONCTION GENERANT LES LIENS DE RETOUR EN CONSERVANT LE TRI, RECHERCHE, PAGINATION -------------
    // Génère une url en conservant les paramètre de tri, recherche et pagination
    public function getReturnUrl(): string
    {
        // Récupération des paramètres GET actuels
        $params = $this->queryParams;

        // On retire l'id de la facture ou autre paramètre spécifique à la page de détail
        unset($params['id'], $params['page'], $params['method']);

        // On évite de mettre un double "?" dans l'URL
        $separator = (strpos($this->baseUrl, '?') === false) ? '?' : '&';

        // Construction de l'URL finale
        $query = http_build_query($params);

        // Si la baseUrl contient déjà "index.php?page=exemple/index&type=exemple"
        // on évite la duplication du paramètre suivant le '&'
        $finalUrl = $this->baseUrl;

        if ($query) {

            // Si 'type=' est déjà dans baseUrl, on le supprime de la query
            if (strpos($finalUrl, 'type=') !== false) {
                parse_str($query, $qArray);
                unset($qArray['type']);
                $query = http_build_query($qArray);
            }

            if (!empty($query)) {
                $finalUrl .= $separator . $query;
            }
        }

        // Si aucun paramètre n'est défini, on retourne juste la base
        return $finalUrl;
    }

    // ------------- FONCTION CALCULANT LE NOMBRE TOTAL DE PAGE  -------------
    // Compte le nombre total d epages nécessaires à l'affichage des enregistrements d'une table en fonction de la limite indiquée.
    public function totalPages(): int
    {
        // Division du total par la limite, arrondie à l'entier supérieur
        return (int)ceil($this->totalResults / $this->limit);
    }

    // ------------- FONCTION GENERANT UN URL SPECIFIQUE DE PAGINATION  -------------
    // Cette méthode génère une URL pour accèder à une page spécifique dans la pagination
    public function pageUrl(int $pageNumber): string 
    {
        // Fusionnes les paramètres GET actuels avec le numéro de page et la limite
        $params = array_merge($this->queryParams, [
            'page_number' => $pageNumber,
            'limit' => $this->limit
        ]);

        // On reoturne l'URL généré (ex: ?page=building/index&page_number=3&limit=20)
        return '?' . http_build_query($params);
    }
}