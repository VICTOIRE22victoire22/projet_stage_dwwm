
<?php

// Modèle de classe pour la table Agent

class AgentRepository {
    private $pdo;

    public function __construct($pdo) 
    {
        $this->pdo = $pdo;
    }


    // -------------- FONCTION REQUETE SQL DE RECUPERATION DE TOUS LES ENREGISTREMENTS DE LA TABLE --------------
    public function getAll($sort = 'agent_firstname', $order = 'asc', $search = '', $limit = null, $offset = null) 
    {

        // Colonnes autorisées pour le tri
        $sortableColumns = ['agent_firstname', 'agent_lastname', 'agent_service'];

        // Validation du tri
        $sort = in_array($sort, $sortableColumns) ? $sort : 'agent_firstname';
        $order = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'asc';

        $params = [];

        // Préparation de la requête SQL
        $sql = "SELECT 
            agent_id, 
            agent_firstname, 
            agent_lastname, 
            agent_service 
        FROM agent";

        //Si recherche, ajouter un WHERE
    
        if (!empty($search)) {
            $sql .= " WHERE 
                agent_firstname LIKE :search OR 
                agent_lastname LIKE :search OR
                agent_service LIKE :search";
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

    // -------------- FONCTION DE COMPTAGE UTILE A LA PAGINATION --------------
    public function countAll(string $search = ''): int 
    {
        // Requête de base pour compter tous les enregistrements 
        $sql = "SELECT COUNT(*) FROM agent";

        // Tableau contenant les paramètres pour la requête préparée
        $params = [];

        if (!empty($search)) {
            // Colonnes sur lesquelles on souhaite effectuer la recherche
            $columns = [
                'agent_firstname',
                'agent_lastname',
                'agent_service'
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
                $param = ":search$index";
                $whereParts[] = "$column LIKE $param";
                $params[$param] = '%' . $search . '%';
            }

            // la méthode implode() permet de rassembler les éléments d'un tableau en une chaîne de caractères.
            $sql .= " WHERE " . implode(" OR ", $whereParts);
        }

        $stmt = $this->pdo->prepare($sql);

        // Bind dynamique des paramètres
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }


    // -------------- FONCTION REQUETE SQL DE RECUPERATION D'UN ENREGISTREMENT EN FONCTION DE L'ID --------------
    public function getById(int $id): ?array 
    {

        $sql = "SELECT
            agent_id,
            agent_firstname,
            agent_lastname,
            agent_service
        FROM agent 
        WHERE agent_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $agent = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$agent) {
            return null;
        }

        return $agent;
    }

    // -------------- FONCTION REQUETE SQL D'AJOUT D'UN ENREGISTREMENT--------------
    public function addAgent($data) 
    {

        $sql = "INSERT INTO agent (
                agent_firstname, 
                agent_lastname, 
                agent_service
                ) VALUES (
                :agent_firstname, 
                :agent_lastname, 
                :agent_service
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':agent_firstname' => $data['agent_firstname'],
            ':agent_lastname' => $data['agent_lastname'],
            ':agent_service' => $data['agent_service']
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE MODIFICATION D'UN ENREGISTREMENT --------------
    public function updateAgent($id, $data) 
    {

        $sql = "UPDATE agent 
                        SET agent_firstname = :agent_firstname, 
                            agent_lastname = :agent_lastname, 
                            agent_service = :agent_service 
                        WHERE agent_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':agent_firstname' => $data['agent_firstname'],
            ':agent_lastname' => $data['agent_lastname'],
            ':agent_service' => $data['agent_service'],
            ':id' => $id
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE SUPPRIMER UN ENREGISTREMENT --------------
    public function deleteAgent($id) 
    {

        $sql = "DELETE FROM agent WHERE agent_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    
}
