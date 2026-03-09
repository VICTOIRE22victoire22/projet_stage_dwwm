<!--
    Vue partielle affichant la liste complète des equipements depuis la base de données.

    Cette vue s'adapte dynamiquement en fonction du type

    Intégrée dynamiquement dans `index.php` (pas d’en-tête HTML ici).
    Les données PHP ($equipments, $colonnes, $search, $sort, $order...) sont injectées par le contrôleur.

    Fonctionnalités :
    - Affichage, recherche, tri et pagination des équipements.
    - Accès complet au CRUD (ajout, détail, modification, suppression).
    - Inclusion du composant de pagination à la fin de la vue.
-->

<!-- Icône + titre -->
<h2 class="title">
    <svg class="icon" width="32" height="32" viewBox="0 0 12 12">
        <path fill="#030303" d="M1 12h9v-1h1V2h-1V1H1v1H0v9h1Zm1-3V7h1v2Zm2 0V6h1v3Zm2 0V5h1v4Zm2 0V4h1v5Zm0 0"/>
    </svg>
    <?= htmlspecialchars($type_labels[$type] ?? 'Équipements') ?>
</h2>

<!-- Lien vers le formulaire d’ajout -->
<?php if(in_array($_SESSION['user_role'], ['admin', 'super-admin'])): ?>
    <a href="/index.php?page=equipment/add&type=<?= urlencode($type) ?>&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>" class="add-form">
        <svg width="25" height="25" viewBox="0 0 56 56">
            <path fill="#c9caca" d="M10.785 20.723c0-6.657 3.516-10.102 10.195-10.102h21.047v-.586c0-4.828-2.46-7.265-7.36-7.265H9.638c-4.899 0-7.36 2.437-7.36 7.265v24.656c0 4.852 2.461 7.266 7.36 7.266h1.148Zm10.57 32.508h25.032c4.875 0 7.336-2.415 7.336-7.243V21.074c0-4.828-2.461-7.265-7.336-7.265H21.356c-4.922 0-7.36 2.437-7.36 7.265v24.914c0 4.828 2.438 7.243 7.36 7.243m12.563-9.165c-1.078 0-1.945-.867-1.945-2.039v-6.515h-6.586c-1.078 0-2.063-.938-2.063-2.016c0-1.031.985-1.992 2.063-1.992h6.586v-6.492c0-1.149.867-2.016 1.945-2.016s1.922.867 1.922 2.016v6.492h6.375c1.195 0 2.18.914 2.18 1.992c0 1.102-.985 2.016-2.18 2.016H35.84v6.515c0 1.172-.844 2.04-1.922 2.04"/>
        </svg>
        &nbsp Ajouter <?= strtolower($type_labels[$type]) ?>
    </a>
<?php endif; ?>

<?php if (!empty($equipments)): ?>
    <!-- Barre de recherche -->
    <?php $search = $_GET['search'] ?? ''; ?>
    <form method="GET" action="/index.php" class="search-form">
        <input type="hidden" name="page" value="equipment/index">
        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
        <input type="text" name="search" placeholder="Rechercher un <?= strtolower($type_labels[$type]) ?? 'équipement' ?>..." value="<?= htmlspecialchars($search) ?>" />
        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
        <input type="hidden" name="order" value="<?= htmlspecialchars($order) ?>">
        <input type="hidden" name="page_num" value="<?= htmlspecialchars($currentPage ?? 1) ?>">
        <button type="submit">Rechercher</button>
        <a href="/index.php?page=equipment/index&type=<?= urlencode($type) ?>" class="btn-reset">Réinitialiser</a>
    </form>

    <!-- Affichage du nombres total d'enregistrement avec ou sans filtres de recherche -->
    <div class="items">
        <?php if(!empty($indexSort->search)): ?>
            <p class ="items-number">
                Nombre total de <?=htmlspecialchars($type) ?>(s) pour votre recherche <?= htmlspecialchars($indexSort->search) ?> : <?= htmlspecialchars($indexSort->totalResults) ?>
            </p>
        <?php else: ?>
            <p class= "items-number">
                Nombre total de <?=htmlspecialchars($type) ?>(s) enregistrés : <?= htmlspecialchars($indexSort->totalResults) ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Tableau des équipements avec tri et actions -->
    <table>
        <thead>
            <tr>
                <!-- Boucle affichant le nom des colonnes -->
                <?php foreach ($colonnes as $colonne => $label): ?>
                    <th>
                        <!-- Le lien pointe vers l'URL généré par IndexSort
                        $triInfos fourni la flèche affichée qui change de couleur selon si la colonne courante est triée ou non. -->
                        <a href="<?= htmlspecialchars($triInfos[$colonne]['url']) ?>" class="sortable-header">
                            <?= htmlspecialchars($label) ?>
                            <span class="<?= $indexSort->sort === $colonne ? 'arrow-white' : 'arrow-grey' ?>">
                                <?= htmlspecialchars($triInfos[$colonne]['arrow']) ?>
                            </span>
                        </a>
                    </th>
                <?php endforeach; ?>
                <th colspan="3" class="actions">Actions</th>
            </tr>
        </thead>

        <tbody>
          <?php foreach ($equipments as $item): ?>
            <tr>
                <?php foreach (array_keys($colonnes) as $colonne): ?>
                    <td data-label="<?= htmlspecialchars($colonnes[$colonne]) ?>">
                        <?= htmlspecialchars($item[$colonne])?>
                    </td>
                <?php endforeach; ?>

                <td class="actions">
                    <span class="action-icon">
                        <a href="/index.php?page=equipment/detail&type=<?= urlencode($type) ?>&id=<?= $item['equipment_id'] ?>&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>">
                            <svg width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#030303" d="m19.3 8.9l3.2 3.2l-1.4 1.4l-3.2-3.2q-.525.3-1.125.5T15.5 11q-1.875 0-3.187-1.312T11 6.5t1.313-3.187T15.5 2t3.188 1.313T20 6.5q0 .675-.2 1.275T19.3 8.9m-3.8.1q1.05 0 1.775-.725T18 6.5t-.725-1.775T15.5 4t-1.775.725T13 6.5t.725 1.775T15.5 9M4 22q-.825 0-1.412-.587T2 20V6q0-.825.588-1.412T4 4h5.5q-.275.625-.375 1.288t-.1 1.312q0 2.725 1.925 4.55t4.575 1.825q.475 0 .95-.063t.975-.212L20 15.25V20q0 .825-.587 1.413T18 22z"/>
                            </svg>
                        </a>
                    </span>

                    <?php if(in_array($_SESSION['user_role'], ['admin', 'super-admin'])): ?>
                        <span class="action-icon">
                            <a href="/index.php?page=equipment/edit&type=<?= urlencode($type) ?>&id=<?= $item['equipment_id'] ?>&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>">
                                <svg width="24" height="24" viewBox="0 0 24 24">
                                    <path fill="#030303" d="M9 15v-4.25l9.175-9.175q.3-.3.675-.45t.75-.15q.4 0 .763.15t.662.45L22.425 3q.275.3.425.663T23 4.4t-.137.738t-.438.662L13.25 15zm10.6-9.2l1.425-1.4l-1.4-1.4L18.2 4.4zM5 21q-.825 0-1.412-.587T3 19V5q0-.825.588-1.412T5 3h8.925L7 9.925V17h7.05L21 10.05V19q0 .825-.587 1.413T19 21z"/>
                                </svg>
                            </a>
                        </span>
                    <?php endif; ?>

                    <?php if(in_array($_SESSION['user_role'], ['admin', 'super-admin'])): ?>
                        <span class="action-icon">
                            <form method="POST" action="/index.php?page=equipment/index&<?= http_build_query(array_merge($_GET, ['type' => $type])) ?>">
                                <input type="hidden" name="equipment_id" value="<?= htmlspecialchars($item['equipment_id']) ?>">
                                <input type="hidden" name="equipment_type" value="<?= htmlspecialchars($item['equipment_type']) ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="returnLink" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                                <button type="submit" name="delete_equipment" class="btn-delete"
                                    onclick="return confirm('Voulez-vous vraiment supprimer ce <?= strtolower($type_labels[$type]) ?> ?');">
                                    <!-- icône corbeille -->
                                    <svg width="24" height="24" viewBox="0 0 24 24">
                                        <path fill="#030303" d="M4 8h16v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1zm3-3V3a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v2h5v2H2V5zm2-1v1h6V4zm0 8v6h2v-6zm4 0v6h2v-6z"/>
                                    </svg>
                                </button>
                            </form>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
    </table>
<!-- Inclusion du fichier contenant la vue de la pagination -->
<?php
    include __DIR__ . '/../../includes/pagination.php';
?>

<?php else: ?>
    <p>Aucun(e) <?= strtolower($type_labels[$type]) ?> trouvé(e) en base de données.</p>
    <div>
        <a href="/index.php?page=equipment/index&type=<?= urlencode($type) ?>" class="return_link-index">
            Retour à la liste des <?= strtolower($type_labels[$type]) ?>
        </a>
    </div> 
<?php endif; ?>