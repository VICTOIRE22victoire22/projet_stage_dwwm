<?php

// Modèle de classe pour la table Invoice

class InvoiceRepository 
{
    private $pdo;

    public function __construct($pdo) 
    {
        $this->pdo = $pdo;
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION DE TOUS LES ENREGISTREMENTS DE LA TABLE --------------
    public function getAll($sort = 'invoice_number', $order = 'asc', $search = '', $limit = null, $offset = null) 
    {

        // Colonnes autorisées pour le tri
        $sortableColumns = ['invoice_number', 'invoice_date', 'invoice_amount', 'invoice_status', 'invoice_account_number', 'invoice_sub_account_number', 'provider_name'];

        // Validation du tri
        $sort = in_array($sort, $sortableColumns) ? $sort : 'invoice_number';
        $order = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'asc';

        $params = [];

        // Préparation de la requête SQL
        $sql = "SELECT 
            invoice.invoice_id, 
            invoice.invoice_number, 
            invoice.invoice_date, 
            invoice.invoice_amount, 
            invoice.invoice_status, 
            invoice.invoice_account_number, 
            invoice.invoice_sub_account_number, 
            provider.provider_name AS provider_name
        FROM invoice
        LEFT JOIN provider ON invoice.invoice_provider_id = provider.provider_id";

        //Si recherche, ajouter un WHERE
    
        if (!empty($search)) {
            $sql .= " WHERE 
                invoice_number LIKE :search OR 
                invoice_date LIKE :search OR
                invoice_amount LIKE :search OR
                invoice_status LIKE :search OR
                invoice_account_number LIKE :search OR
                invoice_sub_account_number LIKE :search OR
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
    public function countAll(string $search = ''): int
    {
        // Requête de base pour compter tous les enregistrements 
        // avec jointure pour permettre la recherche sur le nom de l'opérateur.

        $sql = "SELECT COUNT(*) 
                FROM invoice
                LEFT JOIN provider ON invoice.invoice_provider_id = provider.provider_id";
        $params = [];

        if (!empty($search)) {
            // Colonnes sur lesquelles effectuer la recherche
            $columns = [
                'invoice_number',
                'provider.provider_name',
                'invoice_status',
                'invoice_date',
                'invoice_amount'
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
            invoice.invoice_id, 
            invoice.invoice_number, 
            invoice.invoice_date, 
            invoice.invoice_amount, 
            invoice.invoice_status, 
            invoice.invoice_account_number, 
            invoice.invoice_sub_account_number,
            invoice.invoice_provider_id, 
            provider.provider_name AS provider_name
        FROM invoice
        LEFT JOIN provider ON invoice.invoice_provider_id = provider.provider_id
        WHERE invoice.invoice_id = :id"; 
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$invoice) {
            return null;
        }

        return $invoice;
    }

     // -------------- FONCTION REQUETE SQL D'AJOUT D'UN ENREGISTREMENT--------------
    public function addInvoice($data) 
    {

        $sql = "INSERT INTO invoice (
                invoice_number, 
                invoice_date, 
                invoice_amount, 
                invoice_status, 
                invoice_account_number, 
                invoice_sub_account_number, 
                invoice_provider_id
                ) VALUES (
                :invoice_number, 
                :invoice_date, 
                :invoice_amount, 
                :invoice_status, 
                :invoice_account_number, 
                :invoice_sub_account_number, 
                :invoice_provider_id )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':invoice_number' => $data['invoice_number'],
            ':invoice_date' => $data['invoice_date'],
            ':invoice_amount' => $data['invoice_amount'],
            ':invoice_status' => $data['invoice_status'],
            ':invoice_account_number' => $data['invoice_account_number'],
            ':invoice_sub_account_number' => $data['invoice_sub_account_number'],
            ':invoice_provider_id' => $data['invoice_provider_id']
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE MODIFICATION D'UN ENREGISTREMENT -------------- 
    public function updateInvoice($id, $data) 
    {

        $sql = "UPDATE invoice
                    SET invoice_number = :invoice_number, 
                        invoice_date = :invoice_date, 
                        invoice_amount = :invoice_amount,
                        invoice_account_number = :invoice_account_number,
                        invoice_sub_account_number = :invoice_sub_account_number,
                        invoice_status = :invoice_status,
                        invoice_provider_id = :invoice_provider_id
                    WHERE invoice_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':invoice_number' => $data['invoice_number'],
            ':invoice_date' => $data['invoice_date'],
            ':invoice_amount' => $data['invoice_amount'],
            ':invoice_account_number' => $data['invoice_account_number'],
            ':invoice_sub_account_number' => $data['invoice_sub_account_number'],
            ':invoice_status' => $data['invoice_status'],
            ':invoice_provider_id' => $data['invoice_provider_id'],
            ':id' => $id
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE SUPPRIMER UN ENREGISTREMENT --------------
    public function deleteInvoice($id) 
    {

        $sql = "DELETE FROM invoice WHERE invoice_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    //-------------- FONCTION REQUETE SQL RECUPERANT LES FK --------------
    public function getAllProviders() 
    {

        // Récupération des opérateurs pour les afficher dans un select
        $sql = "SELECT provider_id, provider_name
                FROM provider
                ORDER BY provider_name ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}