<?php

// Modèle de classe pour la table User

class UsersRepository {
    private $pdo;

    public function __construct($pdo) 
    {
        $this->pdo = $pdo;
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION DE TOUS LES ENREGISTREMENTS DE LA TABLE --------------
    public function getAll($sort = 'user_firstname', $order = 'asc', $search = '', $limit = null, $offset = null) 
    {

        // Colonnes autorisées pour le tri
        $sortableColumns = ['user_firstname', 'user_lastname', 'user_email', 'user_login', 'user_role'];

        // Validation du tri
        $sort = in_array($sort, $sortableColumns) ? $sort : 'user_firstname';
        $order = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'asc';

        $params = [];

        // Préparation de la requête SQL

        $sql = "SELECT 
                    user_id, 
                    user_firstname, 
                    user_lastname, 
                    user_email, 
                    user_login, 
                    user_role 
                FROM users"; 

        //Si recherche, ajouter un WHERE
    
        if (!empty($search)) {
            $sql .= " WHERE 
                user_firstname LIKE :search OR 
                user_lastname LIKE :search OR
                user_email LIKE :search OR
                user_login LIKE :search OR
                user_role LIKE :search";
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
    public function countAll($search = '') 
    {
        // Requête de base pour compter tous les enregistrements 
        $sql = "SELECT COUNT(*) FROM users";

        // Tableau contenant les paramètres pour la requête préparée
        $params = [];

        if(!empty($search)) {
            // Colonnes sur lesquelles on souhaite effectuer la recherche 
            $columns = [
                'user_firstname',
                'user_lastname',
                'user_login',
                'user_email',
                'user_role'
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
                    user_id, 
                    user_firstname, 
                    user_lastname, 
                    user_email, 
                    user_login,
                    user_password, 
                    user_role 
                FROM users 
                WHERE user_id = :id"; 
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]); 
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$user) {
            return null;
        }

        return $user;
    }

    // -------------- FONCTION REQUETE SQL D'AJOUT D'UN ENREGISTREMENT--------------
    public function addUser($data) 
    {

        $sql = "INSERT INTO users (
                    user_firstname, 
                    user_lastname, 
                    user_email, 
                    user_login, 
                    user_password, 
                    user_role
                    ) VALUES (
                    :user_firstname, 
                    :user_lastname, 
                    :user_email, 
                    :user_login, 
                    :user_password, 
                    :user_role)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_firstname' => $data['user_firstname'],
            ':user_lastname' => $data['user_lastname'],
            ':user_email' => $data['user_email'],
            ':user_login' => $data['user_login'],
            ':user_password' => $data['user_password'],
            ':user_role' => $data['user_role']
        ]);
    }

    // -------------- FONCTION REQUETE SQL LA MODIFICATION D'UN ENREGISTREMENT --------------
    public function updateUser($id, $data) 
    {

        $sql = "UPDATE users 
                SET user_firstname = :user_firstname, 
                    user_lastname = :user_lastname, 
                    user_email = :user_email,
                    user_login = :user_login,
                    user_password = :user_password,
                    user_role = :user_role
                WHERE user_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_firstname' => $data['user_firstname'],
            ':user_lastname' => $data['user_lastname'],
            ':user_email' => $data['user_email'],
            ':user_login' => $data['user_login'],
            ':user_password' => $data['user_password'],
            ':user_role' => $data['user_role'],
            ':id' => $id
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE SUPPRIMER UN ENREGISTREMENT --------------
    public function deleteUser($id) 
    {

        $sql = "DELETE FROM users WHERE user_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }
}
