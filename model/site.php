<?php

// Modèle de classe pour la table Site

class SiteRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION DE TOUS LES ENREGISTREMENTS DE LA TABLE --------------
    public function getAll($sort = 'site_name', $order = 'asc', $search = '', $limit = null, $offset = null) 
    {

        // Colonnes autorisées pour le tri
        $sortableColumns = ['site_name'];

        // Validation du tri
        $sort = in_array($sort, $sortableColumns) ? $sort : 'site_name';
        $order = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'asc';

        $params = [];
    
        $sql = "SELECT site_id, site_name FROM site"; 

        // Si recherche, ajoute un WHERE
        if(!empty($search)) {
            $sql .= " WHERE site_name LIKE :search";
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
        $sql = "SELECT COUNT(*) FROM site";

        // Tableau contenant les paramètres pour la requête préparée
        $params = [];

        // Si recherche n'est pas vide on ajoute un WHERE
        if (!empty($search)) {
            $sql .= " WHERE site_name LIKE :search";

            // Préparation de la valeur à lier au paramètre SQL 
            // On entoure la recherche de '%' pour utiliser l'opérateur LIKE
            $params[':search'] = '%' . $search . '%';
        }

        // Préparation de la requête SQL
        $stmt = $this->pdo->prepare($sql);

        // Exécute la requête SQL avec les paramètres liés.
        $stmt->execute($params);

        // Retourne le nombre d'enregistrements trouvés correspondant à la recherche
        return (int)$stmt->fetchColumn();
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION D'UN ENREGISTREMENT EN FONCTION DE L'ID --------------
    public function getById(int $id): ?array 
    {

        $sql = "SELECT site_id, site_name FROM site WHERE site_id = :id ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([":id" => $id]);
        $site = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$site) {
            return null;
        }

        return $site;
    }

    // -------------- FONCTION REQUETE SQL D'AJOUT D'UN ENREGISTREMENT--------------
    public function addSite($data) 
    {
        
        $sql = "INSERT INTO site (site_name)
                VALUES (:site_name)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':site_name' => $data['site_name']]);
    }

    // -------------- FONCTION REQUETE SQL DE MODIFICATION D'UN ENREGISTREMENT --------------
    public function updateSite($id, $data) 
    {

        $sql = "UPDATE site
                SET site_name = :site_name
                WHERE site_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':site_name' => $data['site_name'],
            ':id' => $id
        ]);     
    }

    // -------------- FONCTION REQUETE SQL DE SUPPRIMER UN ENREGISTREMENT --------------
    public function deleteSite($id) 
    {
        // Supprime le site
        $sql = "DELETE FROM site WHERE site_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    //-------------- FONCTION REQUETE SQL RECUPERANT LES FK --------------
    public function getBuildingsBySiteId(int $siteId) 
    {

        // Récupération des bâtiments pour les afficher sur la page de détail d'un site.
        $sql = "SELECT building_id, building_name 
                FROM building
                WHERE building_site_id = :site_id
                ORDER BY building_name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':site_id' => $siteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}