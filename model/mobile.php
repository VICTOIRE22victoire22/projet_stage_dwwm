<?php 

// Modèle de classe pour la table Mobile

class MobileRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION DE TOUS LES ENREGISTREMENTS DE LA TABLE --------------
    public function getAll($sort = 'mobile_brand', $order = 'asc', $search = '', $limit = null, $offset = null) 
    {

        // Colonnes autorisées pour le tri
        $sortableColumns = ['mobile_brand', 'mobile_model', 'mobile_imei', 'mobile_purchase_date', 'mobile_exit_date', 'mobile_reconditioned', 'mobile_status', 'phone_line_number', 'agent_fullname'];

        // Validation du tri
        $sort = in_array($sort, $sortableColumns) ? $sort : 'mobile_brand';
        $order = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'asc';

        $params = [];

        // Préparation de la requête SQL
        $sql = "SELECT 
                m.mobile_id, 
                m.mobile_brand, 
                m.mobile_model, 
                m.mobile_imei, 
                -- m.mobile_purchase_date, 
                -- m.mobile_exit_date, 
                m.mobile_reconditioned, 
                m.mobile_status, 
                p.phone_line_number AS phone_line_number, 
                CONCAT(a.agent_firstname, ' ', a.agent_lastname) AS agent_fullname
            FROM mobile m
            LEFT JOIN phone_line p ON m.mobile_phone_line_id = p.phone_line_id
            LEFT JOIN agent a ON m.mobile_agent_id = a.agent_id";
                
        //Si recherche, ajouter un WHERE
        if ($search !== '' && $search !== null) {

            if (in_array($search, ['0', '1'], true)) {
                // Recherche stricte sur les booléens (0 / 1)
                $sql .= " WHERE (m.mobile_reconditioned = :bool_search OR m.mobile_status = :bool_search)";
                $params[':bool_search'] = (int)$search;
            } else {
                // Recherche textuelle sur les autres colonnes
                $sql .= " WHERE (
                        m.mobile_brand LIKE :search OR 
                        m.mobile_model LIKE :search OR
                        m.mobile_imei LIKE :search OR
                        m.mobile_purchase_date LIKE :search OR
                        m.mobile_exit_date LIKE :search OR
                        CAST(m.mobile_reconditioned AS CHAR) LIKE :search OR
                        CAST(m.mobile_status AS CHAR) LIKE :search OR
                        p.phone_line_number LIKE :search OR
                        CONCAT(a.agent_firstname, ' ', a.agent_lastname) LIKE :search
                    )";
                $params[':search'] = '%' . $search . '%';
            }
        }

        // Ajouter l'ordre
        $sql .= " ORDER BY $sort $order";

        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT :offset, :limit";
        }

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        if ($limit !== null && $offset !== null) {
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
        // avec jointure pour permettre la recherche sur le numéro de ligne ou le nom de l'agent.
        $sql = "SELECT COUNT(*) 
                FROM mobile m
                LEFT JOIN phone_line p ON m.mobile_phone_line_id = p.phone_line_id
                LEFT JOIN agent a ON m.mobile_agent_id = a.agent_id";

        // Tableau contenant les paramètres pour la requête préparée
        $params = [];

        if (!empty($search)) {
            // Colonnes sur lesquelles on souhaite effectuer la recherche
            $columns = [
                'm.mobile_brand',
                'm.mobile_model',
                'm.mobile_imei',
                'p.phone_line_number',
                'a.agent_firstname',
                'a.agent_lastname',
                'CAST(m.mobile_reconditioned AS CHAR)',     // booléen casté en texte
                'm.mobile_status',    
                'm.mobile_purchase_date',
                'm.mobile_exit_date'
            ];

            // Tableau qui contiendra chaque condition du WHERE générée dynamiquement
            $whereParts = [];

            /**
            * GESTION SPÉCIALE DES VALEURS "0" ET "1"
            * -----------------------------------------------------------
            * Si l'utilisateur recherche strictement "0" ou "1",
            * on considère qu'il veut filtrer les colonnes booléennes :
            *  - mobile_reconditioned
            *  - mobile_status
            *
            * Dans ce cas on utilise "=" et non LIKE
            */

            if (in_array($search, ['0', '1'], true)) {

                // Conditions spécifiques aux champs booléens
                $whereParts[] = 'm.mobile_reconditioned = :bool_search';
                $whereParts[] = 'm.mobile_status = :bool_search';

                // Conversion en entier car dans la BDD les booléens sont souvent stockés en TINYINT(1)
                $params[':bool_search'] = (int)$search;

            } else {

                /**
                * SINON : recherche textuelle classique sur toutes les colonnes
                * -------------------------------------------------------------
                * Pour chaque colonne, on génère :
                *   colonne LIKE :searchX
                * avec un paramètre unique pour éviter les conflits
                */

                foreach ($columns as $index => $column) {
                    $param = ":search$index";       // nom du paramètre unique
                    $whereParts[] = "$column LIKE $param";      // ajout de la condition au tableau
                    $params[$param] = '%' . $search . '%';      // valeur liée au paramètre. On entoure la recherche de '%' pour utiliser l'opérateur LIKE   
                }
            }

            // la méthode implode() permet de rassembler les éléments d'un tableau en une chaîne de caractères.
            // assemble toutes les conditions avec OR pour former le WHERE
            $sql .= " WHERE " . implode(" OR ", $whereParts);
        }

        // Préparation de la requête SQL
        $stmt = $this->pdo->prepare($sql);

        // Liaison de chaque paramètre créé dynamiquement à sa valeur
        // - Si valeur entière → bind INT
        // - Sinon → bind STRING
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
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
            mobile.mobile_id, 
            mobile.mobile_brand, 
            mobile.mobile_model, 
            mobile.mobile_imei, 
            mobile.mobile_purchase_date, 
            mobile.mobile_exit_date, 
            mobile.mobile_reconditioned, 
            mobile.mobile_status,
            mobile.mobile_phone_line_id,
            mobile.mobile_agent_id,
            phone_line.phone_line_number AS phone_line_number, 
            CONCAT(agent.agent_firstname, ' ', agent.agent_lastname) AS agent_fullname
        FROM mobile
        LEFT JOIN phone_line ON mobile.mobile_phone_line_id = phone_line.phone_line_id
        LEFT JOIN agent ON mobile.mobile_agent_id = agent.agent_id
        WHERE mobile.mobile_id = :id";  
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $mobile = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$mobile) {
            return null;
        }

        return $mobile;
    }

    // -------------- FONCTION REQUETE SQL D'AJOUT D'UN ENREGISTREMENT--------------
    public function addMobile($data) 
    {

        $sql = "INSERT INTO mobile (
                    mobile_brand, 
                    mobile_model, 
                    mobile_imei, 
                    mobile_purchase_date, 
                    mobile_reconditioned, 
                    mobile_status, 
                    mobile_phone_line_id, 
                    mobile_agent_id, 
                    mobile_exit_date)
                VALUES (
                    :mobile_brand, 
                    :mobile_model, 
                    :mobile_imei, 
                    :mobile_purchase_date, 
                    :mobile_reconditioned, 
                    :mobile_status, 
                    :mobile_phone_line_id, 
                    :mobile_agent_id, 
                    :mobile_exit_date)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':mobile_brand' => $data['mobile_brand'],
            ':mobile_model' => $data['mobile_model'],
            ':mobile_imei' => $data['mobile_imei'],
            ':mobile_purchase_date' => $data['mobile_purchase_date'],
            ':mobile_exit_date' => $data['mobile_exit_date'],
            ':mobile_reconditioned' => $data['mobile_reconditioned'],
            ':mobile_status' => $data['mobile_status'],
            ':mobile_phone_line_id' => $data['mobile_phone_line_id'],
            ':mobile_agent_id' => $data['mobile_agent_id']
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE MODIFICATION D'UN ENREGISTREMENT --------------
    public function updateMobile($id, $data) 
    {

        $sql = "UPDATE mobile
                        SET mobile_brand = :mobile_brand, 
                            mobile_model = :mobile_model, 
                            mobile_imei = :mobile_imei,
                            mobile_purchase_date = :mobile_purchase_date,
                            mobile_exit_date = :mobile_exit_date,
                            mobile_reconditioned = :mobile_reconditioned,
                            mobile_status = :mobile_status, 
                            mobile_phone_line_id = :mobile_phone_line_id,
                            mobile_agent_id = :mobile_agent_id
                        WHERE mobile_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':mobile_brand' => $data['mobile_brand'],
            ':mobile_model' => $data['mobile_model'],
            ':mobile_imei' => $data['mobile_imei'],
            ':mobile_purchase_date' => $data['mobile_purchase_date'],
            ':mobile_exit_date' => $data['mobile_exit_date'],
            ':mobile_reconditioned' => $data['mobile_reconditioned'],
            ':mobile_status' => $data['mobile_status'],
            ':mobile_phone_line_id' => $data['mobile_phone_line_id'],
            ':mobile_agent_id' => $data['mobile_agent_id'],
            ':id' => $id
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE SUPPRIMER UN ENREGISTREMENT --------------
    public function deleteMobile($id) 
    {
        $sql = "DELETE FROM mobile WHERE mobile_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    //-------------- FONCTION REQUETE SQL RECUPERANT LES FK --------------
    public function getAllPhoneLines() 
    {

        // Récupération des lignes téléphoniques pour les afficher dans le select
        $sql = "SELECT phone_line_id, phone_line_number
                FROM phone_line
                WHERE phone_line_number LIKE '06%' OR phone_line_number LIKE '07%'
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
}