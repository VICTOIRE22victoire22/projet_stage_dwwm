<?php

// Modèle de classe pour la table Sda_number

class SdaNumberRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // -------------- FONCTION REQUETE SQL DE RECUPERATION D'UN ENREGISTREMENT EN FONCTION DE L'ID --------------
    public function getById(int $id): ?array {
        $sql = "SELECT 
                    sda_id,
                    sda_number,
                    sda_phone_line_id
                FROM sda_number
                WHERE sda_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $sda_number = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$sda_number) {
            return null;
        }

        return $sda_number;
    }

    // -------------- FONCTION REQUETE SQL D'AJOUT D'UN ENREGISTREMENT--------------
    public function addSdaNumber($data) {
        $sql = "INSERT INTO sda_number (
                    sda_number,
                    sda_phone_line_id )
                VALUES (:sda_number, :sda_phone_line_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':sda_number' => $data['sda_number'],
            ':sda_phone_line_id' => $data['sda_phone_line_id']
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE MODIFICATION D'UN ENREGISTREMENT --------------
    public function updateSdaNumber($id, $data) {
        $sql = "UPDATE sda_number
                SET sda_number = :sda_number,
                    sda_phone_line_id = :sda_phone_line_id
                WHERE sda_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':sda_number' => $data['sda_number'],
            ':sda_phone_line_id' => $data['sda_phone_line_id'],
            ':id' => $id
        ]);
    }

    // -------------- FONCTION REQUETE SQL DE SUPPRIMER UN ENREGISTREMENT --------------.
    public function deleteSdaNumber($id) {
        $sql = "DELETE FROM sda_number WHERE sda_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    //-------------- FONCTION REQUETE SQL RECUPERANT LES FK --------------
    public function getAllPhoneLines() {

        // Récupération des lignes téléphoniques fixes pour les afficher dans un select
        $sql = "SELECT phone_line_id, phone_line_number
                FROM phone_line
                WHERE phone_line_number LIKE '01%' OR phone_line_number LIKE '09%'
                ORDER BY phone_line_number ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSelectedPhoneLine(int $id): ?array {

        $sql = "SELECT phone_line_id, phone_line_number
                FROM phone_line
                WHERE phone_line_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $phone_line = $stmt->fetch(PDO::FETCH_ASSOC);

        return $phone_line ?: null;
    }
}