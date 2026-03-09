<?php

// Modèle de classe pour la table Provider

class ProviderRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION DE TOUS LES ENREGISTREMENTS DE LA TABLE --------------
    public function getAll($sort = 'provider_name', $order = 'asc', $search = '', $limit = null, $offset = null) 
    {

        // Colonnes autorisées pour le tri
        $sortableColumns = ['provider_name'];

        // Validation du tri
        $sort = in_array($sort, $sortableColumns) ? $sort : 'provider_name';
        $order = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'asc';

        $params = [];

        // Préparation de la requête SQL
        $sql = "SELECT 
                    provider_id, 
                    provider_name 
                FROM provider";

        //Si recherche, ajouter un WHERE
    
        if (!empty($search)) {
            $sql .= " WHERE 
                provider_name LIKE :search"; 
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
        $sql = "SELECT COUNT(*) FROM provider";

        // Tableau contenant les paramètres pour la requête préparée
        $params = [];

        // Si recherche n'est pas vide on ajoute un WHERE
        if (!empty($search)) {
            $sql .= " WHERE provider_name LIKE :search";

            // Préparation de la valeur à lier au paramètre SQL 
            // On entoure la recherche de '%' pour utiliser l'opérateur LIKE
            $params[':search'] = '%' .$search . '%';
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

        $sql = "SELECT 
                    provider.provider_id, 
                    provider.provider_name
                FROM provider
                WHERE provider_id = :id"; 
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]); 
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$provider) {
            return null;
        }

        return $provider;
    }

    // -------------- FONCTION REQUETE SQL D'AJOUT D'UN ENREGISTREMENT--------------
    public function addProvider($data) 
    {
        $sql = "INSERT INTO provider (provider_name) 
                VALUES (:provider_name)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':provider_name'=> $data['provider_name']]);
    }

    // -------------- FONCTION REQUETE SQL DE MODIFICATION D'UN ENREGISTREMENT --------------
    public function updateProvider($id, $data) 
    {
        $sql = "UPDATE provider 
                SET provider_name = :provider_name 
                WHERE provider_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':provider_name' => $data['provider_name'],
            ':id' => $id
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE SUPPRIMER UN ENREGISTREMENT --------------
    public function deleteProvider($id) 
    {
        
        // Supprime l'opérateur
        $sql = "DELETE FROM provider WHERE provider_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    //-------------- FONCTION REQUETE SQL RECUPERANT LES FK --------------
    public function getAllOffers(int $providerId): array 
    {

        $sql = "SELECT offer_id, offer_name
                FROM offer
                WHERE offer_provider_id = :provider_id
                ORDER BY offer_name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':provider_id' => $providerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllInvoices(int $providerId): array 
    {

        $sql = "SELECT invoice_id, invoice_number
                FROM invoice
                WHERE invoice_provider_id = :provider_id
                ORDER BY invoice_number ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':provider_id' => $providerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}