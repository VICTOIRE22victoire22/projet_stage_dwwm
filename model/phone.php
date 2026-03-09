<?php

// Modèle de classe pour la table Phone

class PhoneRepository {
    private $pdo;

    public function __construct($pdo) 
    {
        $this->pdo = $pdo;
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION DE TOUS LES ENREGISTREMENTS DE LA TABLE --------------
    public function getAll($sort = 'phone_brand', $order = 'asc', $search = '', $limit = null, $offset = null) 
    {

        // Colonnes autorisées pour le tri
        $sortableColumns = ['phone_brand', 'phone_model', 'phone_status', 'phone_line_number', 'agent_fullname', 'building_name'];

        // Validation du tri
        $sort = in_array($sort, $sortableColumns) ? $sort : 'phone_brand';
        $order = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'asc';

        $params = [];

        // Préparation de la requête SQL
        $sql = "SELECT 
                phone.phone_id, 
                phone.phone_brand, 
                phone.phone_model, 
                phone.phone_status, 
                phone_line.phone_line_number AS phone_line_number, 
                CONCAT(agent.agent_firstname, ' ', agent.agent_lastname) AS agent_fullname,
                building.building_name AS building_name 
            FROM phone
            LEFT JOIN phone_line ON phone.phone_line_id = phone_line.phone_line_id
            LEFT JOIN agent ON phone.phone_agent_id = agent.agent_id
            LEFT JOIN building ON phone.phone_building_id = building.building_id";
        
        //Si recherche, ajouter un WHERE    
        if (!empty($search)) {
            $sql .= " WHERE (
                    phone.phone_brand LIKE :search OR 
                    phone.phone_model LIKE :search OR
                    building.building_name LIKE :search OR
                    CAST(phone.phone_status AS CHAR) LIKE :search OR
                    phone_line.phone_line_number LIKE :search OR
                    CONCAT(agent.agent_firstname, ' ', agent.agent_lastname) LIKE :search
                )";
    
            $params[':search'] = '%' . strtolower(trim($search)) . '%';
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
            $stmt->bindValue($key, $val, PDO::PARAM_STR);
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
        // avec jointure pour permettre la recherche sur le numéro de ligne, le nom de l'agent et le nom du bâtiment.

        $sql = "SELECT COUNT(*) 
            FROM phone
            LEFT JOIN phone_line ON phone.phone_line_id = phone_line.phone_line_id
            LEFT JOIN agent ON phone.phone_agent_id = agent.agent_id
            LEFT JOIN building ON phone.phone_building_id = building.building_id";

        // Tableau des paramètres envoyés à la requête préparée
        $params = [];

        if (!empty($search)) {
            // Colonnes sur lesquelles on souhaite effectuer la recherche
            $columns = [
                "phone.phone_brand",
                "phone.phone_model",
                "phone.phone_status",
                "phone_line.phone_line_number",
                "CONCAT(agent.agent_firstname, ' ', agent.agent_lastname)",     // Concaténation du prénom et nom de l'agent
                "building.building_name"
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
                $params[$param] = '%' . $search . '%';      // valeur liée au paramètre
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
        $stmt->execute($params);

        // Retourne le nombre d'enregistrements trouvés correspondant à la recherche
        return $stmt->fetchColumn();
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION D'UN ENREGISTREMENT EN FONCTION DE L'ID --------------
    public function getById(int $id): ?array 
    {

        $sql = "SELECT 
                    phone.phone_id, 
                    phone.phone_brand, 
                    phone.phone_model, 
                    phone.phone_status,
                    phone.phone_line_id,
                    phone.phone_agent_id,
                    phone.phone_building_id,
                    phone_line.phone_line_number AS phone_line_number, 
                    CONCAT(agent.agent_firstname, ' ', agent.agent_lastname) AS agent_fullname,
                    building.building_name AS building_name 
                FROM phone
                LEFT JOIN phone_line ON phone.phone_line_id = phone_line.phone_line_id
                LEFT JOIN agent ON phone.phone_agent_id = agent.agent_id
                LEFT JOIN building ON phone.phone_building_id = building.building_id
                WHERE phone.phone_id = :id"; 
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]); 
        $phone = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$phone) {
            return null;
        }

        return $phone;
    }

    // -------------- FONCTION REQUETE SQL D'AJOUT D'UN ENREGISTREMENT--------------
    public function addPhone($data) 
    {

        $sql = "INSERT INTO phone (
                    phone_brand, 
                    phone_model, 
                    phone_status, 
                    phone_line_id, 
                    phone_agent_id, 
                    phone_building_id) 
                VALUES (
                    :phone_brand, 
                    :phone_model, 
                    :phone_status, 
                    :phone_line_id, 
                    :phone_agent_id, 
                    :phone_building_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':phone_brand' => $data['phone_brand'],
            ':phone_model' => $data['phone_model'],
            ':phone_status' => $data['phone_status'],
            ':phone_line_id' => $data['phone_line_id'],
            ':phone_agent_id' => $data['phone_agent_id'],
            ':phone_building_id' => $data['phone_building_id'],
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE MODIFICATION D'UN ENREGISTREMENT --------------
    public function updatePhone($id, $data) 
    {

        $sql = "UPDATE phone
                SET phone_brand = :phone_brand, 
                    phone_model = :phone_model, 
                    phone_status = :phone_status,
                    phone_line_id = :phone_line_id,
                    phone_agent_id = :phone_agent_id,
                    phone_building_id = :phone_building_id
                WHERE phone_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':phone_brand' => $data['phone_brand'],
            ':phone_model' => $data['phone_model'],
            ':phone_status' => $data['phone_status'],
            ':phone_line_id' => $data['phone_line_id'],
            ':phone_agent_id' => $data['phone_agent_id'],
            ':phone_building_id' => $data['phone_building_id'],
            ':id' => $id
         ]);
    }

    // -------------- FONCTION REQUETE SQL DE SUPPRIMER UN ENREGISTREMENT --------------
    public function deletePhone($id) 
    {

        $sql = "DELETE FROM phone WHERE phone_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    //-------------- FONCTION REQUETE SQL RECUPERANT LES FK --------------
    public function getAllPhoneLines() 
    {

        // Récupération des lignes téléphoniques pour les afficher dans le select
        $sql = "SELECT phone_line_id, phone_line_number
                FROM phone_line
                WHERE phone_line_number LIKE '01%' OR phone_line_number LIKE '09%'
                ORDER BY phone_line_number ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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
}