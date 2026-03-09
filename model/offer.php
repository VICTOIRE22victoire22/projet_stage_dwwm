<?php

// Modèle de classe pour la table Offer

class OfferRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION DE TOUS LES ENREGISTREMENTS DE LA TABLE --------------
    public function getAll($sort = 'offer_name', $order = 'asc', $search = '', $limit = null, $offset = null) 
    {

        // Colonnes autorisées pour le tri
        $sortableColumns = ['offer_name', 'offer_price', 'offer_date', 'offer_context', 'offer_type', 'provider_name'];

        // Validation du tri
        $sort = in_array($sort, $sortableColumns) ? $sort : 'offer_name';
        $order = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'asc';

        $params = [];

        // Préparation de la requête SQL
        $sql = "SELECT 
                offer.offer_id, 
                offer.offer_name, 
                offer.offer_price, 
                offer.offer_context, 
                offer.offer_type, 
                provider.provider_name AS provider_name
            FROM offer
            LEFT JOIN provider ON offer.offer_provider_id = provider.provider_id";

        //Si recherche, ajouter un WHERE
        if (!empty($search)) {
            $sql .= " WHERE 
                    LOWER(offer.offer_name) LIKE LOWER(:search) OR
                    LOWER(CAST(offer.offer_price AS CHAR)) LIKE LOWER(:search) OR 
                    LOWER(offer.offer_context) LIKE LOWER(:search) OR 
                    LOWER(offer.offer_type) LIKE LOWER(:search) OR 
                    LOWER(provider.provider_name) LIKE LOWER(:search)";
            $params[':search'] = '%' . $search . '%';
        }

        // Ajouter l'ordre
        $sql .= " ORDER BY $sort $order";

        // Pagination SQL (LIMIT + OFFSET)
        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT :offset, :limit";
        }

        $stmt = $this->pdo->prepare($sql);

        // Bind dynamique
        foreach($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        if ($limit !== null && $offset !== null) {
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------- FONCTION DE COMPTAGE UTILE A LA PAGINATION --------------
    public function countAll($search = '') 
    {
        // Requête de base : on compte toutes les offres,
        // avec une jointure pour autoriser la recherche sur le nom du provider.

        $sql = "SELECT COUNT(*) 
                FROM offer
                LEFT JOIN provider ON offer.offer_provider_id = provider.provider_id";

        // Tableau contenant les paramètres pour la requête préparée
        $params = [];

        if (!empty($search)) {
            // Colonnes sur lesquelles on souhaite effectuer la recherche
            $columns = [
                'offer.offer_name',
                'offer.offer_context',
                'offer.offer_type',
                'provider.provider_name',
                // Colonnes à caster en texte pour utiliser LIKE correctement
                'CAST(offer.offer_price AS CHAR)'
            ];

            // Tableau qui contiendra chaque condition du WHERE générée dynamiquement
            $whereParts = [];

            /**
             * Construction dynamique des conditions du WHERE
             * 
             * Pour chaque colonne, on crée un paramètre unique (ex: :search0, :search1, etc...)
             * ensuite on construit une condition du type :
             * colonne LIKE :param
             * 
             * Ces conditions sont ensuite combinées avec OR dans le WHERE final.
             */

            foreach ($columns as $index => $column) {
                $param = ":search$index";       // nom du paramètre unique
                $whereParts[] = "$column LIKE $param";      // ajout de la condition au tableau
                $params[$param] = '%' . $search . '%';      // valeur liée au paramètre. On entoure la recherche de '%' pour utiliser l'opérateur LIKE
            }

            // la méthode implode() permet de rassembler les éléments d'un tableau en une chaîne de caractères.
            // assemble toutes les conditions avec OR pour former le WHERE
            $sql .= " WHERE " . implode(" OR ", $whereParts);
        }

        // Préparation de la requête SQL
        $stmt = $this->pdo->prepare($sql);

        // Liaison de chaque paramètre créé dynamiquement à sa valeur
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        // Exécution de la requête
        $stmt->execute();

        // Retourne le nombre d'enregistrements trouvés correspondant à la recherche
        return $stmt->fetchColumn();
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION D'UN ENREGISTREMENT EN FONCTION DE L'ID --------------
    public function getById(int $id): ?array 
    {
        $sql = "SELECT 
                offer.offer_id, 
                offer.offer_name, 
                offer.offer_price, 
                offer.offer_context, 
                offer.offer_type,
                offer.offer_provider_id,
                phone_line.phone_line_id AS phone_line_id,
                phone_line.phone_line_number AS phone_line_number,
                provider.provider_name AS provider_name
            FROM offer
            LEFT JOIN phone_line ON phone_line.phone_line_offer_id = offer.offer_id
            LEFT JOIN provider ON offer.offer_provider_id = provider.provider_id
            WHERE offer.offer_id = :id"; 
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);  
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$offer) {
            return null;
        }

        return $offer;
    }

    // -------------- FONCTION REQUETE SQL D'AJOUT D'UN ENREGISTREMENT--------------
    public function addOffer($data) 
    {
        $sql = "INSERT INTO offer (
                    offer_name, 
                    offer_price, 
                    offer_context, 
                    offer_type, 
                    offer_provider_id) 
                VALUES (
                    :offer_name, 
                    :offer_price, 
                    :offer_context, 
                    :offer_type,
                    :offer_provider_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':offer_name' => $data['offer_name'],
            ':offer_price' => $data['offer_price'],
            ':offer_context' => $data['offer_context'],
            ':offer_type' => $data['offer_type'],
            ':offer_provider_id' => $data['offer_provider_id'],
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE MODIFICATION D'UN ENREGISTREMENT -------------- 
    public function updateOffer($id, $data) 
    {
        $sql = "UPDATE offer
                SET offer_name = :offer_name, 
                    offer_price = :offer_price, 
                    offer_context = :offer_context,
                    offer_type = :offer_type,
                    offer_provider_id = :offer_provider_id
                WHERE offer_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':offer_name' => $data['offer_name'],
            ':offer_price' => $data['offer_price'],
            ':offer_context' => $data['offer_context'],
            ':offer_type' => $data['offer_type'],
            ':offer_provider_id' => $data['offer_provider_id'],
            ':id' => $id
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE SUPPRIMER UN ENREGISTREMENT --------------
    public function deleteOffer($id) 
    {
        $sql ="DELETE FROM offer WHERE offer_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    //-------------- FONCTION REQUETE SQL RECUPERANT LES FK --------------

    // Récupération des opérateurs pour les afficher dans la page de détail
    public function getProvidersByOfferId(int $offer_id) 
    {

        $sql = "SELECT provider_id, provider_name
                FROM provider
                INNER JOIN offer ON offer.offer_provider_id = provider.provider_id
                WHERE offer.offer_id = :offer_id
                ORDER BY provider_name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':offer_id' => $offer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupération des opérateurs pour les afficher dans un select
    public function getAllProviders() 
    {

        $sql = "SELECT provider_id, provider_name
                FROM provider
                ORDER BY provider_name ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupération des lignes téléphoniques pour les afficher dans un select
    public function getAllPhoneLines() 
    {

        // Récupération des lignes téléphoniques pour les afficher dans le select
        $sql = "SELECT phone_line_id, phone_line_number
                FROM phone_line
                ORDER BY phone_line_number ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupération des lignes téléphoniques pour les afficher dans la page de détail
    public function getPhoneLinesByOfferId(int $offer_id)
    { 
        $sql = "SELECT phone_line_id, phone_line_number
                FROM phone_line
                WHERE phone_line_offer_id = :offer_id
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':offer_id' => $offer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}