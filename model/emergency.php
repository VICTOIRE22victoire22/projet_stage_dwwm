<?php

// Modèle de classe pour la table Emergency

class EmergencyRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION DE TOUS LES ENREGISTREMENTS DE LA TABLE --------------
    public function getAll($sort = 'phone_line_number', $order = 'asc', $search = '', $limit = null, $offset = null) {

        // Colonnes autorisées pour le tri
        $sortableColumns = ['phone_line_number', 'building_name', 'equipment_model', 'emergency_type'];

        // Validation du tri
        $sort = in_array($sort, $sortableColumns) ? $sort : 'phone_line_number';
        $order = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'asc';        
        
        $params = [];

        // Préparation de la requête SQL
        $sql = "SELECT 
            emergency.emergency_id, 
            phone_line.phone_line_number AS phone_line_number, 
            building.building_name AS building_name, 
            equipment.equipment_model AS equipment_model, 
            emergency.emergency_type 
        FROM emergency
        LEFT JOIN phone_line ON emergency.emergency_phone_line_id = phone_line.phone_line_id
        LEFT JOIN building ON emergency.emergency_building_id = building.building_id
        LEFT JOIN equipment ON emergency.emergency_equipment_id = equipment.equipment_id";

        //Si recherche, ajouter un WHERE
    
        if (!empty($search)) {
            $sql .= " WHERE 
                phone_line_number LIKE :search OR 
                building_name LIKE :search OR
                equipment_model LIKE :search OR
                emergency_type LIKE :search";
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

        $sql = "SELECT COUNT(*) FROM emergency
                LEFT JOIN phone_line
                ON emergency.emergency_phone_line_id = phone_line.phone_line_id
                LEFT JOIN equipment
                ON emergency.emergency_equipment_id = equipment.equipment_id";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE emergency_type LIKE :search 
                      OR equipment.equipment_model LIKE :search
                      OR phone_line.phone_line_number LIKE :search";
            $params[':search'] = '%' .$search . '%';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION D'UN ENREGISTREMENT EN FONCTION DE L'ID --------------
    public function getById(int $id): ?array {

        $sql = "SELECT 
            emergency.emergency_id,
            emergency.emergency_phone_line_id,
            emergency.emergency_building_id,
            emergency.emergency_equipment_id,
            phone_line.phone_line_number AS phone_line_number, 
            building.building_name AS building_name, 
            equipment.equipment_model AS equipment_model, 
            emergency.emergency_type 
        FROM emergency
        LEFT JOIN phone_line ON emergency_phone_line_id = phone_line_id
        LEFT JOIN building ON emergency_building_id = building_id
        LEFT JOIN equipment ON emergency_equipment_id = equipment_id
        WHERE emergency_id = :id"; 
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $emergency = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$emergency) {
            return null;
        }

        return $emergency;
    }


    // -------------- FONCTION REQUETE SQL D'AJOUT D'UN ENREGISTREMENT--------------
    public function addEmergency($data) {

        $sql = "INSERT INTO emergency (
                emergency_phone_line_id, 
                emergency_building_id, 
                emergency_equipment_id, 
                emergency_type) 
                VALUES (
                :emergency_phone_line_id, 
                :emergency_building_id, 
                :emergency_equipment_id, 
                :emergency_type
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':emergency_phone_line_id' => $data['emergency_phone_line_id'],
            ':emergency_building_id' => $data['emergency_building_id'],
            ':emergency_equipment_id' => $data['emergency_equipment_id'],
            ':emergency_type' => $data['emergency_type']
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE MODIFICATION D'UN ENREGISTREMENT --------------
    public function updateEmergency($id, $data) {

        $sql = "UPDATE emergency
                    SET emergency_phone_line_id = :emergency_phone_line_id,
                        emergency_building_id = :emergency_building_id,
                        emergency_equipment_id = :emergency_equipment_id, 
                        emergency_type = :emergency_type
                    WHERE emergency_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':emergency_phone_line_id' => $data['emergency_phone_line_id'],
            ':emergency_building_id' => $data['emergency_building_id'],
            ':emergency_equipment_id' => $data['emergency_equipment_id'],
            ':emergency_type' => $data['emergency_type'],
            ':id' => $id
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE SUPPRIMER UN ENREGISTREMENT --------------
    public function deleteEmergency($id) {

        $sql = "DELETE FROM emergency WHERE emergency_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    //-------------- FONCTION REQUETE SQL RECUPERANT LES FK --------------
    // public function getAllPhoneLines() 
    // {

    //     // Récupération des lignes téléphoniques pour les afficher dans le select
    //     $sql = "SELECT phone_line_id, phone_line_number 
    //             FROM phone_line
    //             WHERE phone_line_number LIKE '01%'
    //             ORDER BY phone_line_number ASC"; 
    //     $stmt = $this->pdo->query($sql); 
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }

    public function getAllBuildings() 
    {

        // Récupération des bâtiments pour les afficher dans le select
        $sql = "SELECT building_id, building_name
                FROM building
                ORDER BY building_name ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllEquipments() 
    {
        // Récupération des équipements pour les afficher dans le select
        $sql = "SELECT equipment_id, equipment_type, equipment_model
                FROM equipment
                ORDER BY equipment_type ASC, equipment_model ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fonction permettant de récupérer l'id d'une ligne via le numéro de téléphone
    public function getPhoneLineIdByNumber($number)
    {
        $sql = "SELECT phone_line_id FROM phone_line WHERE phone_line_number = :number LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':number' => $number]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (int)$result['phone_line_id'] : null;
    }

    // Fonction permettant de récupérer un numéro de ligne via son id 
    public function getPhoneLineNumberById($id): ?string
    {
        $sql = "SELECT phone_line_number FROM phone_line WHERE phone_line_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['phone_line_number'] ?? null;
    }
}