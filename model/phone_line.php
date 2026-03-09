<?php 

// Modèle de classe pour la table Phone_line 

class PhoneLineRepository {
    private $pdo;

    public function __construct($pdo) 
    {
        $this->pdo = $pdo;
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION DE TOUS LES ENREGISTREMENTS DE LA TABLE --------------
    public function getAll($sort = 'phone_line_number', $order = 'asc', $search = '', $limit = null, $offset = null) 
    {

        // Colonnes autorisées pour le tri
        $sortableColumns = ['phone_line_number', 'phone_line_status', 'phone_line_termination_number', 'phone_line_box_return_date', 'agent_fullname', 'building_name'];

        // Validation du tri
        $sort = in_array($sort, $sortableColumns) ? $sort : 'phone_line_number';
        $order = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'asc';

        $params = [];

        // Préparation de la requête SQL
        $sql = "SELECT
            phone_line.phone_line_id,
            phone_line.phone_line_number, 
            phone_line.phone_line_status, 
            phone_line.phone_line_termination_number,
            phone_line.phone_line_termination_date,
            phone_line.phone_line_box_return_date,
            phone_line.phone_line_designation, 
            CONCAT(agent.agent_firstname, ' ', agent.agent_lastname) AS agent_fullname,
            building.building_name AS building_name,
            offer.offer_name AS offer_name
            FROM phone_line
            LEFT JOIN agent ON phone_line.phone_line_agent_id = agent.agent_id
            LEFT JOIN building ON phone_line.phone_line_building_id = building.building_id
            LEFT JOIN offer ON phone_line.phone_line_offer_id = offer.offer_id";

        //Si recherche, ajouter un WHERE

        if (!empty($search)) {
            $sql .= " WHERE 
                phone_line_number LIKE :search OR 
                phone_line_status LIKE :search OR
                phone_line_termination_number LIKE :search OR
                phone_line_termination_date LIKE :search OR
                phone_line_box_return_date LIKE :search OR
                phone_line_designation LIKE :search OR
                CONCAT(agent.agent_firstname, ' ', agent.agent_lastname) LIKE :search OR
                building_name LIKE :search OR
                offer_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        // Ajoute l'ordre
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
        // Requête de base pour compter tous les enregistrements 
        // avec jointure pour permettre la recherche sur le nom de l'agent, du bâtiment et de l'offre.

        $sql = "SELECT COUNT(*) 
                FROM phone_line
                LEFT JOIN agent ON agent.agent_id = phone_line.phone_line_agent_id
                LEFT JOIN building ON building.building_id = phone_line_building_id
                LEFT JOIN offer ON offer.offer_id = phone_line_offer_id";

        // Tableau des paramètres envoyés à la requête préparée
        $params = [];

        if (!empty($search)) {
            // Colonnes sur lesquelles on souhaite effectuer la recherche
            $columns = [
                "phone_line.phone_line_number",
                "phone_line.phone_line_status",
                "phone_line.phone_line_termination_number",
                // Colonnes à caster en texte pour utiliser LIKE correctement
                "CAST(phone_line.phone_line_termination_date AS CHAR)",     
                "CAST(phone_line.phone_line_box_return_date AS CHAR)",  
                "phone_line.phone_line_designation",
                "CONCAT(agent.agent_firstname, ' ', agent.agent_lastname)", // Concaténation du prénom et nom de l'agent
                "building.building_name",
                "offer.offer_name"
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

            // La méthode implode() permet de rassembler les éléments d'un tableau en une chaîne de caractères.
            // assemble toutes les conditions avec OR pour former le WHERE
            $sql .= " WHERE " . implode(" OR ", $whereParts);
        }

        // Préparation de la requête SQL
        $stmt = $this->pdo->prepare($sql);

        // Liaison de chaque paramètre créé dynamiquement à sa valeur
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        // Exécute la requête SQL
        $stmt->execute();

        // Retourne le nombre d'enregistrements trouvés correspondant à la recherche
        return (int)$stmt->fetchColumn();
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION D'UN ENREGISTREMENT EN FONCTION DE L'ID --------------
    public function getById(int $id): ?array 
    {

        $sql = "SELECT 
                phone_line.*, 
                CONCAT(agent.agent_firstname, ' ', agent.agent_lastname) AS agent_fullname,
                building.building_name AS building_name,
                offer.offer_name AS offer_name
                FROM phone_line
                LEFT JOIN agent ON phone_line.phone_line_agent_id = agent.agent_id
                LEFT JOIN building ON phone_line.phone_line_building_id = building.building_id
                LEFT JOIN offer ON phone_line.phone_line_offer_id = offer.offer_id
                WHERE phone_line.phone_line_id = :id";  
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);  
            $phone_line = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$phone_line) {
            return null;
        }

        // Récupération de tous les numéros SDA associés à la ligne téléphonique
        $sql_sda = "SELECT sda_id, sda_number FROM sda_number WHERE sda_phone_line_id = :id";
        $stmt = $this->pdo->prepare($sql_sda);
        $stmt->execute([':id' => $id]);
        $sda_numbers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // On retourne tout sous forme de tableau 
        $phone_line['sda_numbers'] = $sda_numbers;

        return $phone_line;

    }

    // -------------- FONCTION REQUETE SQL D'AJOUT D'UN ENREGISTREMENT--------------
    public function addPhoneLine($data) 
    {
        $sql = "INSERT INTO phone_line (
                phone_line_number, 
                phone_line_status, 
                phone_line_termination_number,
                phone_line_termination_date,
                phone_line_box_return_date,
                phone_line_designation,
                phone_line_agent_id, 
                phone_line_building_id,
                phone_line_offer_id
                ) VALUES (
                :phone_line_number, 
                :phone_line_status, 
                :phone_line_termination_number,
                :phone_line_termination_date,
                :phone_line_box_return_date,
                :phone_line_designation, 
                :phone_line_agent_id, 
                :phone_line_building_id,
                :phone_line_offer_id
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':phone_line_number' => $data['phone_line_number'],
            ':phone_line_status' => $data['phone_line_status'],
            ':phone_line_termination_number' => $data['phone_line_termination_number'] ?? null,
            ':phone_line_termination_date' => $data['phone_line_termination_date'] ?? null,
            ':phone_line_box_return_date' => $data['phone_line_box_return_date'] ?? null,
            ':phone_line_designation' => $data['phone_line_designation'] ?? null,
            ':phone_line_agent_id' => $data['phone_line_agent_id'],
            ':phone_line_building_id' => $data['phone_line_building_id'],
            ':phone_line_offer_id' => $data['phone_line_offer_id']
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE MODIFICATION D'UN ENREGISTREMENT -------------- 
    public function updatePhoneLine($id, $data) 
    {
        $sql = "UPDATE phone_line
                SET phone_line_number = :phone_line_number, 
                    phone_line_status = :phone_line_status, 
                    phone_line_termination_number = :phone_line_termination_number,
                    phone_line_termination_date = :phone_line_termination_date,
                    phone_line_box_return_date = :phone_line_box_return_date,
                    phone_line_designation = :phone_line_designation,
                    phone_line_agent_id = :phone_line_agent_id,
                    phone_line_building_id = :phone_line_building_id,
                    phone_line_offer_id = :phone_line_offer_id
                WHERE phone_line_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':phone_line_number' => $data['phone_line_number'],
            ':phone_line_status' => $data['phone_line_status'],
            ':phone_line_termination_number' => $data['phone_line_termination_number'] ?? null,
            ':phone_line_termination_date' => $data['phone_line_termination_date'] ?? null,
            ':phone_line_box_return_date' => $data['phone_line_box_return_date'] ?? null,
            ':phone_line_designation' => $data['phone_line_designation'],
            ':phone_line_agent_id' => $data['phone_line_agent_id'],
            ':phone_line_building_id' => $data['phone_line_building_id'],
            ':phone_line_offer_id' => $data['phone_line_offer_id'],
            ':id' => $id 
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE SUPPRIMER UN ENREGISTREMENT --------------
    public function deletePhoneLine($id) 
    {

        // Supprime la ligne téléphonique.
        $sql = "DELETE FROM phone_line WHERE phone_line_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    //-------------- FONCTION REQUETE SQL RECUPERANT LES FK --------------
    public function getAllAgents() 
    {
        // Récupération des agents pour les afficher dans un select
        $sql = "SELECT agent_id, agent_firstname, agent_lastname,
                    CONCAT(agent.agent_firstname, ' ', agent.agent_lastname) AS agent_fullname
                    FROM agent
                    ORDER BY agent_fullname ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllBuildings() 
    {
        // Récupération des bâtiments pour les afficher dans le select
        $sql = "SELECT building_id, building_name
                FROM building
                ORDER BY building_name ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllOffers() 
    {
        // Récupération des offres pour les afficher dans le select
        $sql = "SELECT offer_id, offer_name
                FROM offer
                ORDER BY offer_name ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOfferByPhoneLineId(int $phone_line_id) 
    {
        // Récupération de l'offre associé à la ligne pour rediriger vers celle-ci depuis la page de détail
        $sql = "SELECT offer.offer_id, offer.offer_name
                FROM phone_line
                JOIN offer ON phone_line_offer_id = offer.offer_id
                WHERE phone_line.phone_line_id = :phone_line_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':phone_line_id' => $phone_line_id]);
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);

        return $offer ?: null;
    }
}