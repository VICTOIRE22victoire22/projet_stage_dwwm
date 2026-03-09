<?php

// Fonction permettant de récupérer les valeurs des enum des tables contenant des champs de ce type.

function getEnumValues(PDO $pdo, string $table, string $column): array {
    
    // Sécurisation des noms pour éviter les injections
    if (!preg_match('/^\w+$/', $table) || !preg_match('/^\w+$/', $column)) {
        throw new InvalidArgumentException("Nom de table ou colonne invalide.");
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM `$table` WHERE Field = '$column'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return [];
    }

    $type = $row['Type']; // contient les valeurs de l'enum
    preg_match("/^enum\((.*)\)$/", $type, $matches);

    if (!isset($matches[1])) {
        return [];
    }

    $enum = str_getcsv($matches[1], ",", "'"); // Plus sûr pour les virgules dans les enums
    return $enum;
}