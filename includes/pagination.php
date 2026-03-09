<!-- Vue d'affichage de la pagination

    Variables attendues :
    - $pagination: tableau d'éléments généré par le controller (numéro, url, page courante),
    - $indexSort: instanciation de la classe IndexSort
-->

<div class="pagination-container">

    <!---------- FORMULAIRE DU NOMBRE DE RESULTATS PAR PAGE ---------->
    <!-- Ce formulaire permet de choisir combien de ligne afficher (10, 20, 50 ou 100) -->
    <form method="get" action="<?= htmlspecialchars($indexSort->baseUrl) ?>" class="pagination-form">
        <?php
            // Conservation des paramètres GET sauf limit et page_number
            // limit et page_number sont redéfinis par le formulaire ou la pagiantion
            // Pour chacun des autres paramètres on crée un champ caché afin qu'il soit conservé lors du submit du formulaire
            foreach ($_GET as $key => $value) {
                if (!in_array($key, ['limit', 'page_number'])) {
                    echo "<input type='hidden' name='" .htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
                }
            }
        ?>
        <!-- Menu déroulant pour sélectionner le nombre d'éléments à afficher -->

        <label for="limit"> Résultats par page:</label>
        <select name="limit" id="limit" onchange="this.form.submit()">
            <!-- On propose plusieurs options fixes -->
            <?php foreach ([10, 20, 50, 100] as $option): ?>
                <!-- Chaque option représente un nombre de résultats -->
                <!-- L'attribut 'selected' est ajouté si la valeur correspond à la limite actuelle -->
                <option value="<?= $option ?>" <?= ($indexSort->limit == $option) ? 'selected' : ''?>>
                    <?= $option ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Liens de pagination -->

    <div class="pagination-links">
        <!-- Si la page actuelle est supérieur à 1, on affiche un lien vers la page précédente (<<) -->
        <?php if ($indexSort->currentPage > 1): ?>
            <a href="<?= htmlspecialchars($indexSort->pageUrl($indexSort->currentPage - 1)) ?>">
               &laquo;
            </a>
        <?php endif; ?>
        
        <!-- On parcourt le tableau $pagiantion généré par le contrôleur
            Chaque élément contient : 
            - 'page': numéro de la page
            - 'url': lien complet vers cette page
            - 'current': booléen indiquant si c'est la page en cours
        -->
        <?php foreach ($pagination as $page): ?>
            <!-- Lien vers une page spécifique -->
            <!-- La classe 'active' est ajoutée à la page actuellement affichée -->
            <a href="<?= htmlspecialchars($page['url']) ?>" class="<?= $page['current'] ? 'active' : '' ?>">
                <?= $page['page'] ?>
            </a>
        <?php endforeach; ?>
        
        <!-- Si la page actuelle est inférieur au nombre total de page, on affiche un lien vers la page suivante (>>) -->
        <?php if ($indexSort->currentPage < $indexSort->totalPages()): ?>
            <a href="<?= htmlspecialchars($indexSort->pageUrl($indexSort->currentPage + 1)) ?>">
                &raquo;
            </a>
        <?php endif; ?>
    </div>
</div>