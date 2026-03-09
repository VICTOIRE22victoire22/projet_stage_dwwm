<!--
    Vue partielle affichant la liste complète des urgences depuis la base de données.

    Intégrée dynamiquement dans 'index.php' (pas d'en-tête HTML ici).
    Les données PHP ($emergencies, $colonnes, $search, $sort, $order...) sont injectées par le contrôleur.

    Fonctionnalités :
    - Affichage, recherche, tri et pagination des urgences.
    - Accès complet au CRUD (ajout, détail, modification, suppression).
    - Inclusion du composant de pagination à la fin de la vue.
-->

<h2 class="title">
    <svg class="icon" width="32" height="32" viewBox="0 0 24 24">
        <path fill="#030303" fill-rule="evenodd" d="M19 2v3h3v4h-3v3h-4V9h-3V5h3V2zm-7.59 13.22l-1.442 2.416a14.3 14.3 0 0 1-3.637-3.637l2.416-1.442l-1.879-6.575l-5.848.7l.051.937a16.2 16.2 0 0 0 2.078 7.089a16.2 16.2 0 0 0 2.643 3.467a16.2 16.2 0 0 0 3.467 2.642a16.2 16.2 0 0 0 7.089 2.078l.936.052l.701-5.848l-6.575-1.88Z" clip-rule="evenodd"/>
    </svg>
    URGENCES
</h2>

<!-- Lien vers le formulaire d’ajout -->
<?php if(in_array($_SESSION['user_role'], ['admin', 'super-admin'])): ?>
    <a href="/index.php?page=emergency/add&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>" class="add-form">
        <svg width="25" height="25" viewBox="0 0 56 56">
            <path fill="#c9caca" d="M10.785 20.723c0-6.657 3.516-10.102 10.195-10.102h21.047v-.586c0-4.828-2.46-7.265-7.36-7.265H9.638c-4.899 0-7.36 2.437-7.36 7.265v24.656c0 4.852 2.461 7.266 7.36 7.266h1.148Zm10.57 32.508h25.032c4.875 0 7.336-2.415 7.336-7.243V21.074c0-4.828-2.461-7.265-7.336-7.265H21.356c-4.922 0-7.36 2.437-7.36 7.265v24.914c0 4.828 2.438 7.243 7.36 7.243m12.563-9.165c-1.078 0-1.945-.867-1.945-2.039v-6.515h-6.586c-1.078 0-2.063-.938-2.063-2.016c0-1.031.985-1.992 2.063-1.992h6.586v-6.492c0-1.149.867-2.016 1.945-2.016s1.922.867 1.922 2.016v6.492h6.375c1.195 0 2.18.914 2.18 1.992c0 1.102-.985 2.016-2.18 2.016H35.84v6.515c0 1.172-.844 2.04-1.922 2.04"/>
        </svg>
        &nbsp Ajouter une urgence
    </a>
<?php endif; ?>

<?php if (!empty($emergencies)): ?>
    <!-- Barre de recherche -->
    <form method="GET" action="/index.php" class="search-form">
        <input type="hidden" name="page" value="emergency/index">
        <input type="text" name="search" placeholder="Rechercher une urgence..." value="<?= htmlspecialchars($search ?? '') ?>" />
        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort ?? 'phone_line_number') ?>">
        <input type="hidden" name="order" value="<?= htmlspecialchars($order ?? 'asc') ?>">
        <button type="submit">Rechercher</button>
        <a href="/index.php?page=emergency/index" class="btn-reset">Réinitialiser</a>
    </form>

    <!-- Affichage du nombres total d'enregistrement avec ou sans filtres de recherche -->
    <div class="items">
        <?php if(!empty($indexSort->search)): ?>
            <p class ="items-number">
                Nombre total d'urgence(s) pour votre recherche <?= htmlspecialchars($indexSort->search) ?> : <?= htmlspecialchars($indexSort->totalResults) ?>
            </p>
        <?php else: ?>
            <p class= "items-number">
                Nombre total d'urgence(s) enregistrées : <?= htmlspecialchars($indexSort->totalResults) ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Tableau des urgences avec tri et actions -->
    <table>
        <thead>
            <tr>
                <!-- Boucle affichant le nom des colonnes -->
                <?php foreach ($colonnes as $colonne => $label): ?>
                    <th>
                        <!-- Le lien pointe vers l'URL généré par Indexsort
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
          <?php foreach ($emergencies as $emergency): ?>
            <tr>
                <?php foreach (array_keys($colonnes) as $colonne): ?>
                    <td data-label="<?= htmlspecialchars($colonnes[$colonne]) ?>"><?= $emergency[$colonne] ?></td>
                <?php endforeach; ?>

                <td class="actions">
                    <span class="action-icon">
                        <a href="/index.php?page=emergency/detail&id=<?=$emergency['emergency_id'] ?>&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>">
                            <svg width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#030303" d="m19.3 8.9l3.2 3.2l-1.4 1.4l-3.2-3.2q-.525.3-1.125.5T15.5 11q-1.875 0-3.187-1.312T11 6.5t1.313-3.187T15.5 2t3.188 1.313T20 6.5q0 .675-.2 1.275T19.3 8.9m-3.8.1q1.05 0 1.775-.725T18 6.5t-.725-1.775T15.5 4t-1.775.725T13 6.5t.725 1.775T15.5 9M4 22q-.825 0-1.412-.587T2 20V6q0-.825.588-1.412T4 4h5.5q-.275.625-.375 1.288t-.1 1.312q0 2.725 1.925 4.55t4.575 1.825q.475 0 .95-.063t.975-.212L20 15.25V20q0 .825-.587 1.413T18 22z"/>
                            </svg>
                        </a>
                    </span>

                    <?php if(in_array($_SESSION['user_role'], ['admin', 'super-admin'])): ?>
                        <span class="action-icon">
                            <a href="/index.php?page=emergency/edit&id=<?=$emergency['emergency_id'] ?>&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>">
                                <svg width="24" height="24" viewBox="0 0 24 24">
                                    <path fill="#030303" d="M9 15v-4.25l9.175-9.175q.3-.3.675-.45t.75-.15q.4 0 .763.15t.662.45L22.425 3q.275.3.425.663T23 4.4t-.137.738t-.438.662L13.25 15zm10.6-9.2l1.425-1.4l-1.4-1.4L18.2 4.4zM5 21q-.825 0-1.412-.587T3 19V5q0-.825.588-1.412T5 3h8.925L7 9.925V17h7.05L21 10.05V19q0 .825-.587 1.413T19 21z"/>
                                </svg>
                            </a>
                        </span>
                    <?php endif; ?>

                    <?php if(in_array($_SESSION['user_role'], ['admin', 'super-admin'])): ?>
                        <span class="action-icon">
                            <form method="POST" action="/index.php?page=emergency/index&<?= http_build_query($_GET) ?>">
                                <input type="hidden" name="emergency_id" value="<?= htmlspecialchars($emergency['emergency_id']) ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="returnLink" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                                <button type="submit" name="delete_emergency" class="btn-delete"
                                    onclick="return confirm('Voulez-vous vraiment supprimer cette urgence ?');">
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
    <p>Aucune urgence trouvée en base de données.</p>
    <div>
        <a href="/index.php?page=emergency/index" class="return_link-index">
            Retour à la liste des urgences
        </a>
    </div> 
<?php endif; ?>