<?php
// Définition de la base URL du projet
$base_url = '/'; 

// Récupération du type courant depuis l'URL pour le lien actif
$current_type = $_GET['type'] ?? '';
?>

<nav class="navbar fixed-top">
    <!-- Bouton hamburger pour mobile -->
    <div class="navbar-toggle" onclick="toggleMenu()">&#9776;</div>

    <ul class="dropdownmenu" id="navMenu">
        <li><a href="<?= $base_url ?>index.php" class="nav_link">ACCUEIL</a></li>

        <li class="has-submenu">
            <span onclick="toggleSubmenu(this)">INFRASTRUCTURES</span>
            <ul>
                <li><a href="<?= $base_url ?>index.php?page=site/index" class="nav_link">Sites</a></li>
                <li><a href="<?= $base_url ?>index.php?page=building/index" class="nav_link">Bâtiments</a></li>
            </ul>
        </li>

        <li class="has-submenu">
            <span onclick="toggleSubmenu(this)">FINANCES</span>
            <ul>
                <li><a href="<?= $base_url ?>index.php?page=invoice/index" class="nav_link">Factures</a></li>
                <li><a href="<?= $base_url ?>index.php?page=offer/index" class="nav_link">Offres</a></li>
                <li><a href="<?= $base_url ?>index.php?page=provider/index" class="nav_link">Opérateurs</a></li>
            </ul>
        </li>

        <li class="has-submenu">
            <span onclick="toggleSubmenu(this)">ADMINISTRATION</span>
            <ul>
                <?php if(in_array($_SESSION['user_role'], ['admin', 'super-admin'])): ?>
                    <li><a href="<?= $base_url ?>index.php?page=users/index" class="nav_link">Utilisateurs</a></li>
                <?php endif; ?>
                <li><a href="<?= $base_url ?>index.php?page=agent/index" class="nav_link">Agents</a></li>
            </ul>
        </li>

        <li class="has-submenu">
            <span onclick="toggleSubmenu(this)">MATERIEL</span>
            <ul>
                <li><a href="<?= $base_url ?>index.php?page=phone/index" class="nav_link">Téléphones fixes</a></li>
                <li><a href="<?= $base_url ?>index.php?page=mobile/index" class="nav_link">Téléphones mobiles</a></li>
                <?php
                $equipments_types = [
                    'box' => 'Box',
                    'routeur' => 'Routeur',
                    'transmetteur' => 'Transmetteur'
                ];

                foreach ($equipments_types as $type_key => $type_label) {
                    $active_class = ($current_type === $type_key) ? 'active' : '';
                    echo '<li><a href="'.$base_url.'index.php?page=equipment/index&type='.$type_key.'" class="nav_link '.$active_class.'">'.$type_label.'</a></li>';
                }
                ?>
                <li><a href="<?= $base_url ?>index.php?page=pabx/index" class="nav_link">PABX</a></li>
            </ul>
        </li>

        <li class="has-submenu">
            <span onclick="toggleSubmenu(this)">LIGNES</span>
            <ul>
                <li><a href="<?= $base_url ?>index.php?page=phone_line/index" class="nav_link">Lignes téléphoniques</a></li>
                <li><a href="<?= $base_url ?>index.php?page=emergency/index" class="nav_link">Urgences</a></li>
            </ul>
        </li>

        <li><a href="<?= $base_url ?>logout.php" class="nav_link">SE DECONNECTER</a></li>
    </ul>
</nav>

<!-- Script pour menu responsive -->
<script>
function toggleMenu() {
    const menu = document.getElementById("navMenu");
    if (menu.style.display === "flex" || menu.style.display === "") {
        menu.style.display = "none";
    } else {
        menu.style.display = "flex";
        menu.style.flexDirection = "column";
    }
}

// Sous-menus au clic (mobile)
function toggleSubmenu(element) {
    const submenu = element.nextElementSibling;
    if (submenu.style.display === "block") {
        submenu.style.display = "none";
    } else {
        submenu.style.display = "block";
    }
}
</script>

