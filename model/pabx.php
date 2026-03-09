<?php

// Modèle de classe pour la table PABX

class PabxRepository {
    private $pdo;

    public function __construct($pdo) 
    {
        $this->pdo = $pdo;
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION DE TOUS LES ENREGISTREMENTS DE LA TABLE --------------
    public function getAll($sort = 'pabx_brand', $order = 'asc', $search = '', $limit = null, $offset = null) 
    {

        // Colonnes autorisées pour le tri
        $sortableColumns = ['pabx_brand'];

        // Validation du tri
        $sort = in_array($sort, $sortableColumns) ? $sort : 'pabx_brand';
        $order = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'asc';
        
        $params = [];

        // Préparation de la requête SQL
        $sql = "SELECT 
            pabx.pabx_id, 
            pabx.pabx_brand, 
            pabx.pabx_model, 
            pabx.pabx_series_number, 
            building.building_name AS building_name 
        FROM pabx
        LEFT JOIN building ON pabx.pabx_building_id = building.building_id";

        //Si recherche, ajouter un WHERE
        if (!empty($search)) {
            $sql .= " WHERE pabx.pabx_brand LIKE :search
                    OR pabx.pabx_model LIKE :search
                    OR pabx.pabx_series_number LIKE :search
                    OR building.building_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        // Ajouter l'ordre
        $sql .= " ORDER BY $sort $order";

        // Pagination SQL (LIMIT + OFFSET)
        if($limit !== null && $offset !== null) {
            $sql .= " LIMIT :offset, :limit";
        }

        $stmt = $this->pdo->prepare($sql);

        // Bind dynamique 
        foreach($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        if($limit !== null && $offset !== null) {
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
        // avec jointure pour permettre la recherche sur le nom du bâtiment.

        $sql = "SELECT COUNT(*) FROM pabx
                LEFT JOIN building ON pabx.pabx_building_id = building.building_id";
        $params = [];

        if (!empty($search)) {
            // Colonnes sur lesquelles on souhaite effectuer la recherche
            $columns = [
                'pabx_brand',
                'pabx_model',
                'pabx_series_number',
                'building.building_name'
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
                $whereParts[] = "$column LIKE $param";  // ajout de la condition au tableau
                $params[$param] = '%' . $search . '%';  // valeur liée au paramètre
            }

            // la méthode implode() permet de rassembler les éléments d'un tableau en une chaîne de caractères.
            // assemble toutes les conditions avec OR pour former le WHERE
            $sql .= " WHERE " . implode(" OR ", $whereParts);
        }

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
            pabx.pabx_id, 
            pabx.pabx_brand, 
            pabx.pabx_model, 
            pabx.pabx_series_number,
            pabx.pabx_building_id, 
            building.building_name AS building_name 
        FROM pabx
        LEFT JOIN building ON pabx.pabx_building_id = building.building_id
        WHERE pabx_id = :id"; 
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]); 
        $pabx = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pabx) {
            return null;
        }

        return $pabx;
    }

    // -------------- FONCTION REQUETE SQL D'AJOUT D'UN ENREGISTREMENT--------------
    public function addPabx($data) 
    {

        $sql = "INSERT INTO pabx (
                pabx_brand,
                pabx_model,
                pabx_series_number, 
                pabx_building_id
                ) VALUES (
                :pabx_brand, 
                :pabx_model, 
                :pabx_series_number, 
                :pabx_building_id
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':pabx_brand' => $data['pabx_brand'],
            ':pabx_model' => $data['pabx_model'],
            ':pabx_series_number' => $data['pabx_series_number'],
            ':pabx_building_id' => $data['pabx_building_id']
        ]);  
    }

    // -------------- FONCTION REQUETE SQL DE MODIFICATION D'UN ENREGISTREMENT --------------
    public function updatePabx($id, $data) 
    {

        $sql = "UPDATE pabx
                    SET pabx_brand = :pabx_brand,
                        pabx_model = :pabx_model,
                        pabx_series_number = :pabx_series_number,
                        pabx_building_id = :pabx_building_id 
                    WHERE pabx_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':pabx_brand' => $data['pabx_brand'],
            ':pabx_model' => $data['pabx_model'],
            ':pabx_series_number' => $data['pabx_series_number'],
            ':pabx_building_id' => $data['pabx_building_id'],
            ':id' => $id
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE SUPPRIMER UN ENREGISTREMENT --------------
    public function deletePabx($id) 
    {

        $sql = "DELETE FROM pabx WHERE pabx_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    //-------------- FONCTION REQUETE SQL RECUPERANT LES FK --------------
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