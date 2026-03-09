<?php

// Modèle de classe pour la table Building

class BuildingRepository 
{
    private $pdo;

    public function __construct($pdo) 
    {
        $this->pdo = $pdo;
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION DE TOUS LES ENREGISTREMENTS DE LA TABLE --------------
    public function getAll($sort = 'building_name', $order = 'asc', $search = '', $limit = null, $offset = null) 
    {
        
        // Colonnes autorisées pour le tri
        $sortableColumns = ['building_name', 'building_address', 'building_erp_category', 'site_name'];

        // Validation du tri
        $sort = in_array($sort, $sortableColumns) ? $sort : 'building_name';
        $order = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'asc';

        $params = [];

        // Préparation de la requête SQL
        $sql = "SELECT 
            building.building_id, 
            building.building_name, 
            building.building_address, 
            building.building_erp_category, 
            site.site_name AS site_name 
        FROM building
        LEFT JOIN site ON building.building_site_id = site.site_id";

        //Si recherche, ajouter un WHERE
        if (!empty($search)) {
            $sql .= " WHERE 
                building_name LIKE :search OR 
                building_address LIKE :search OR
                building_erp_category LIKE :search OR
                site.site_name LIKE :search";
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
    public function countAll(string $search = ''): int 
    {
        // Requête de base pour compter tous les enregistrements 
        // avec jointure pour permettre la recherche sur le nom du site.
         
        $sql = "SELECT COUNT(*) 
                FROM building
                LEFT JOIN site ON building.building_site_id = site.site_id";
        
        // Tableau des paramètres envoyés à la requête préparée
        $params = [];

        if (!empty($search)) {
            // Colonnes sur lesquelles on souhaite effectuer la recherche
            $columns = [
                'building_name',
                'building_address',
                'building_erp_category',
                'site.site_name'
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

        // Exécute la requête SQL
        $stmt->execute();

        // Retourne le nombre d'enregistrements trouvés correspondant à la recherche
        return (int)$stmt->fetchColumn();
    }


    // -------------- FONCTION REQUETE SQL DE RECUPERATION D'UN ENREGISTREMENT EN FONCTION DE L'ID --------------
    public function getById(int $id): ?array 
    {

        $sql = "SELECT 
            building.building_id, 
            building.building_name, 
            building.building_address, 
            building.building_erp_category,
            building.building_site_id, 
            site.site_name AS site_name 
        FROM building
        LEFT JOIN site ON building.building_site_id = site.site_id
        WHERE building.building_id = :id"; 
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $building = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$building) {
            return null;
        }

        return $building;
    }
    

    // -------------- FONCTION REQUETE SQL D'AJOUT D'UN ENREGISTREMENT--------------
    public function addBuilding($data) 
    {
        $sql = "INSERT INTO building (
                building_name,
                building_address,
                building_erp_category,
                building_site_id
                ) VALUES (
                :building_name,
                :building_address,
                :building_erp_category,
                :building_site_id
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':building_name' => $data['building_name'],
            ':building_address' => $data['building_address'],
            ':building_erp_category' => $data['building_erp_category'],
            ':building_site_id' => $data['building_site_id']
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE MODIFICATION D'UN ENREGISTREMENT --------------
    public function updateBuilding($id, $data) 
    {

        $sql = "UPDATE building
                       SET building_name = :building_name,
                           building_address = :building_address,
                           building_erp_category = :building_erp_category,
                           building_site_id = :building_site_id
                      WHERE building_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':building_name' => $data['building_name'],
            ':building_address' => $data['building_address'],
            ':building_erp_category' => $data['building_erp_category'],
            ':building_site_id' => $data['building_site_id'],
            ':id' => $id
        ]);   
    }

    // -------------- FONCTION REQUETE SQL DE SUPPRIMER UN ENREGISTREMENT --------------
    public function deleteBuilding($id) 
    {

        // Supprime le bâtiment.
        $sql = "DELETE FROM building WHERE building_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    //-------------- FONCTION REQUETE SQL RECUPERANT LES FK --------------
    public function getAllSites() 
    {

        // Récupération des sites pour les afficher dans le select
        $sql = "SELECT  site_id, site_name  FROM site ORDER BY site_name ASC"; 
        $stmt = $this->pdo->query($sql); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --------------- FONCTION REQUETE SQL DE RECUPERATION DE TOUTES LES URGENCES ASSOCIEES A UN BATIMENT DONNE SOUS FORME DE TABLEAU ASSOCIATIF -----------------------
    public function getEmergenciesByBuildingId(int $buildingId): array 
    {
        $sql = "SELECT 
            emergency.emergency_id, 
            emergency.emergency_phone_line_id AS phone_line_id, 
            emergency.emergency_type,
            phone_line.phone_line_number
        FROM emergency emergency
        LEFT JOIN phone_line phone_line ON emergency.emergency_phone_line_id = phone_line.phone_line_id
        WHERE emergency.emergency_building_id = :building_id ";
    
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':building_id' => $buildingId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --------------- FONCTION REQUETE SQL DE RECUPERATION DE TOUS LES AGENTS ASSOCIES A UN BATIMENT DONNE SOUS FORME DE TABLEAU ASSOCIATIF -----------------------
    public function getPhoneLinesByBuildingId(int $buildingId): array 
    {
        $sql = "SELECT 
            phone_line.phone_line_id,
            phone_line.phone_line_designation, 
            CONCAT(agent.agent_firstname, ' ', agent.agent_lastname) AS agent_fullname,
            phone_line.phone_line_number
        FROM phone_line
        LEFT JOIN agent ON phone_line.phone_line_agent_id = agent.agent_id
        WHERE phone_line.phone_line_building_id = :building_id
          AND phone_line.phone_line_id NOT IN (
              SELECT emergency_phone_line_id FROM emergency
              WHERE emergency_phone_line_id IS NOT NULL)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':building_id' => $buildingId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

