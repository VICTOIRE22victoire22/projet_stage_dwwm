<?php

// Modèle de classe pour la table Equipment

class EquipmentRepository 
{
    private $pdo;

    public function __construct($pdo) 
    {
        $this->pdo = $pdo;
    }

    // -------- FONCTION PERMETTANT DE RECUPERER TOUS LES EQUIPEMENTS -------- 
    public function getAllEquipments (
        string $type, 
        string $sort = 'equipment_series_number', 
        string $order = 'asc', 
        string $search = '', 
        int $limit = 10, 
        int $offset = 0): array 
    {

        $sortableColumns = ['equipment_series_number', 'equipment_model', 'equipment_type'];
        $sort = in_array($sort, $sortableColumns) ? $sort : 'equipment_series_number';
        $order = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'ASC';

        $sql = "SELECT
                    equipment_id,
                    equipment_series_number,
                    equipment_model,
                    equipment_type
                FROM equipment
                WHERE equipment_type = :type";

        $params = [':type' => $type];
        if (!empty($search)) {
            $sql .= " AND (
                        equipment_series_number LIKE :search OR
                        equipment_model LIKE :search OR
                        equipment_type LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }


        $sql .= " ORDER BY $sort $order";

        if($limit !== null && $offset !== null) {
            $sql .= " LIMIT :offset, :limit";
        }

        $stmt = $this->pdo->prepare($sql);

        // Bind dynamique des paramètres
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        if ($limit !== null && $offset !== null) {
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------- FONCTION DE COMPTAGE UTILE A LA PAGINATION --------

    public function countAll(string $type, string $search = ''): int 
    {

        $sql = "SELECT COUNT(*)
                FROM equipment
                WHERE equipment_type = :type";
        $params = [':type' => $type];

        if (!empty($search)) {
            // Colonnes sur lesquelles on effectue la recherche
            $columns = [
                'equipment_series_number',
                'equipment_model',
                'equipment_type'
            ];

            // Construire dynamiquement le WHERE
            $whereParts = [];
            foreach ($columns as $index => $column) {
                $param = ":search$index";
                $whereParts[] = "$column LIKE $param";
                $params[$param] = '%' . $search . '%';
            }

            // la méthode implode() permet de rassembler les éléments d'un tableau en une chaîne de caractères.
            $sql .= " AND (" . implode(" OR ", $whereParts) . ")";
        }

        $stmt = $this->pdo->prepare($sql);

        // Bind des paramètres
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // -------- FONCTION PERMETTANT DE RECUPERER LES INFORMATIONS D'UN EQUIPEMENT SELON SON ID ET SON TYPE --------
    public function getEquipmentById(int $id, string $type): ?array 
    {
        $validTypes = ['box', 'routeur', 'transmetteur'];
        if (!in_array($type, $validTypes, true)) {
            return null; // type invalide
        }

        $sql = "SELECT 
                equipment_id,
                equipment_series_number, 
                equipment_model, 
                equipment_type 
            FROM equipment 
            WHERE equipment_type = :type AND equipment_id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':type' => $type,
            ':id' => $id
        ]);

        $equipment = $stmt->fetch(PDO::FETCH_ASSOC);

        return $equipment ?: null; // retourne null si aucun résultat
    }
    

    // -------- FONCTION PERMETTANT L'AJOUT D'UN EQUIPEMENT -------- 
    public function addEquipment(array $data): void 
    {

        $sql = "INSERT INTO equipment (
                equipment_series_number, 
                equipment_model, 
                equipment_type
                ) VALUES (
                :equipment_series_number, 
                :equipment_model, 
                :equipment_type
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':equipment_series_number' => $data['equipment_series_number'],
            ':equipment_model' => $data['equipment_model'],
            ':equipment_type' => $data['equipment_type']
        ]);
    }

    // -------- FONCTION PERMETTANT DE MODIFIER UNE BOX --------  
    public function updateBox(int $id, array $data): void
    {

        $sql = "UPDATE equipment
                        SET equipment_series_number = :equipment_series_number, 
                            equipment_model = :equipment_model,
                            equipment_type = :equipment_type 
                        WHERE equipment_id = :id
                        AND equipment_type = 'box'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':equipment_series_number' => $data['equipment_series_number'],
            ':equipment_model' => $data['equipment_model'],
            ':equipment_type' => $data['equipment_type'],
            ':id' => $id
        ]);
    }

    // -------- FONCTION PERMETTANT DE MODIFIER UN ROUTEUR -------- 
    public function updateRouteur(int $id, array $data): void
    {

        $sql = "UPDATE equipment
                        SET equipment_series_number = :equipment_series_number, 
                            equipment_model = :equipment_model,
                            equipment_type = :equipment_type 
                        WHERE equipment_id = :id
                        AND equipment_type = 'routeur'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':equipment_series_number' => $data['equipment_series_number'],
            ':equipment_model' => $data['equipment_model'],
            ':equipment_type' => $data['equipment_type'],
            ':id' => $id
        ]);
    }

    // -------- FONCTION PERMETTANT DE MODIFIER UN TRANSMETTEUR -------- 
    public function updateTransmetteur(int $id, array $data): void
    {

        $sql = "UPDATE equipment
                        SET equipment_series_number = :equipment_series_number, 
                            equipment_model = :equipment_model,
                            equipment_type = :equipment_type 
                        WHERE equipment_id = :id
                        AND equipment_type = 'transmetteur'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':equipment_series_number' => $data['equipment_series_number'],
            ':equipment_model' => $data['equipment_model'],
            ':equipment_type' => $data['equipment_type'],
            ':id' => $id
        ]);
    }

    // -------- FONCTION PERMETTANT DE SUPPRIMER UN EQUIPEMENT -------- 
    public function deleteEquipment(int $id, string $type): void 
    {
        // vérification du type
        $validTypes = ['box', 'routeur', 'transmetteur'];
        if (!in_array($type, $validTypes, true)) {
            throw new InvalidArgumentException("Type d'équipement invalide");
        }

        $sql = "DELETE FROM equipment WHERE equipment_id = :id AND equipment_type = :type";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':type' => $type
        ]);
    }
}