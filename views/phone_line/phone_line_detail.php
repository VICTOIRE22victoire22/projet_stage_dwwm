
    <h1 class='title_detail'>Détail de la ligne téléphonique</h1>

    <!-- Lien vers le formulaire d’ajout -->
    <?php if(in_array($_SESSION['user_role'], ['admin', 'super-admin'])): ?>
        <a href="/index.php?page=phone_line/add" class="add-form">
            <svg width="25" height="25" viewBox="0 0 56 56">
                <path fill="#c9caca" d="M10.785 20.723c0-6.657 3.516-10.102 10.195-10.102h21.047v-.586c0-4.828-2.46-7.265-7.36-7.265H9.638c-4.899 0-7.36 2.437-7.36 7.265v24.656c0 4.852 2.461 7.266 7.36 7.266h1.148Zm10.57 32.508h25.032c4.875 0 7.336-2.415 7.336-7.243V21.074c0-4.828-2.461-7.265-7.336-7.265H21.356c-4.922 0-7.36 2.437-7.36 7.265v24.914c0 4.828 2.438 7.243 7.36 7.243m12.563-9.165c-1.078 0-1.945-.867-1.945-2.039v-6.515h-6.586c-1.078 0-2.063-.938-2.063-2.016c0-1.031.985-1.992 2.063-1.992h6.586v-6.492c0-1.149.867-2.016 1.945-2.016s1.922.867 1.922 2.016v6.492h6.375c1.195 0 2.18.914 2.18 1.992c0 1.102-.985 2.016-2.18 2.016H35.84v6.515c0 1.172-.844 2.04-1.922 2.04"/>
            </svg>
            Ajouter une ligne téléphonique
        </a>
    <?php endif; ?>

    <section class="section-common">
        <table class="table_detail">
            <thead>
                <tr>
                    <th class="label">Numéro de ligne:</th>
                    <th class="label">Statut:</th>
                    <th class="label">Numéro de résiliation:</th>
                    <th class="label">Date de résiliation:</th>
                    <th class="label">Date de retour de la box:</th>
                    <th class="label">Désignation:</th>
                    <th class="label">Agent:</th>
                    <th class="label">Bâtiment:</th>
                    <th class="label">Offre:</th>
                    <?php if (in_array($_SESSION['user_role'], ['admin', 'super-admin'])): ?>
                        <th class="label">Actions:</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="value"><?= htmlspecialchars($phone_line['phone_line_number']) ?></td>
                    <td class="value"><?= htmlspecialchars($phone_line['phone_line_status']) ?></td>
                    <td class="value">
                        <?= empty($phone_line['phone_line_termination_number']) ? '' : htmlspecialchars($phone_line['phone_line_termination_number']) ?>
                    </td>

                    <td class="value">
                        <?= empty($phone_line['phone_line_termination_date']) ? '' : htmlspecialchars($phone_line['phone_line_termination_date']) ?>
                    </td>

                    <td class="value">
                        <?= empty($phone_line['phone_line_box_return_date']) ? '' : htmlspecialchars($phone_line['phone_line_box_return_date']) ?>
                    </td>

                    <td class="value">
                        <?= empty($phone_line['phone_line_designation']) ? 'Designation non fournie' : htmlspecialchars($phone_line['phone_line_designation']) ?>
                    </td>
                    <td class="value">
                        <?= empty($phone_line['agent_fullname']) ? 'Aucun agent associé à la ligne téléphonique.' : htmlspecialchars($phone_line['agent_fullname']) ?>
                    </td>
                    <td class="value">
                        <?= empty($phone_line['building_name']) ? 'Aucun bâtiment associé au téléphone fixe.' : htmlspecialchars($phone_line['building_name']) ?>
                    </td>
                    <td class="value">
                        <?php if (!empty($offer) && !empty($offer['offer_id'])): ?>
                            <a href="/index.php?page=offer/detail&id=<?= urlencode((string)$offer['offer_id']) ?>" class="table-link">
                                <?= htmlspecialchars($offer['offer_name']) ?>
                            </a>
                        <?php else: ?>
                            Aucune offre associée au téléphone fixe.
                        <?php endif; ?>
                    </td>
                    <?php if (in_array($_SESSION['user_role'], ['admin', 'super-admin'])): ?>
                    <td class="actions">
                        <div class="action-container">
                            <span class="action-icon">
                                <a href="/index.php?page=phone_line/edit&id=<?=$phone_line['phone_line_id'] ?>&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24">
                                        <path fill="#030303" d="M9 15v-4.25l9.175-9.175q.3-.3.675-.45t.75-.15q.4 0 .763.15t.662.45L22.425 3q.275.3.425.663T23 4.4t-.137.738t-.438.662L13.25 15zm10.6-9.2l1.425-1.4l-1.4-1.4L18.2 4.4zM5 21q-.825 0-1.412-.587T3 19V5q0-.825.588-1.412T5 3h8.925L7 9.925V17h7.05L21 10.05V19q0 .825-.587 1.413T19 21z"/>
                                    </svg>
                                </a>
                            </span>

                            <span class="action-icon">
                                <form method="POST" action="/index.php?page=phone_line/index&<?= http_build_query($_GET) ?>">
                                    <input type="hidden" name="phone_line_id" value="<?= htmlspecialchars($phone_line['phone_line_id']) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                    <input type="hidden" name="returnLink" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                                    <button type="submit" name="delete_phone_line" class="btn-delete"
                                    onclick="return confirm('Voulez-vous vraiment supprimer cette ligne téléphonique ?');">
                                        <svg width="24" height="24" viewBox="0 0 24 24">
                                            <path fill="#030303" d="M4 8h16v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1zm3-3V3a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v2h5v2H2V5zm2-1v1h6V4zm0 8v6h2v-6zm4 0v6h2v-6z"/>
                                        </svg>
                                    </button>
                                </form>
                            </span>
                        </div>
                    </td>

                    <?php endif; ?>
                </tr>
            </tbody>
        </table>
    </section>

    <section>
        <h2>Numéro(s) SDA</h2>

        <?php if (empty($sda_numbers)): ?>
            <p>Aucun numéro SDA associé.</p>
        <?php else: ?>
            <ul class="sda-list">
                <?php foreach ($sda_numbers as $sda): ?>
                    <li>
                        <span><?= htmlspecialchars($sda['sda_number']) ?></span>

                        
                        <div class="sda-actions">
                            <!-- Modifier -->
                            <?php if(in_array($_SESSION['user_role'], ['admin', 'super-admin'])): ?>
                                <a href="/index.php?page=sda_number/edit&id=<?= $sda['sda_id'] ?>" title="Modifier">
                                    <svg width="32" height="32" viewBox="0 0 24 24">
                                        <path fill="#000000" d="M9 15v-4.25l9.175-9.175q.3-.3.675-.45t.75-.15q.4 0 .763.15t.662.45L22.425 3q.275.3.425.663T23 4.4t-.137.738t-.438.662L13.25 15zm10.6-9.2l1.425-1.4l-1.4-1.4L18.2 4.4zM5 21q-.825 0-1.412-.587T3 19V5q0-.825.588-1.412T5 3h8.925L7 9.925V17h7.05L21 10.05V19q0 .825-.587 1.413T19 21z"/>
                                    </svg>
                                </a>

                                <!-- Supprimer -->
                                <form method="POST" action="/index.php?page=sda_number/delete">
                                    <input type="hidden" name="sda_id" value="<?= htmlspecialchars($sda['sda_id']) ?>">
                                    <input type="hidden" name="sda_phone_line_id" value="<?=htmlspecialchars($phone_line['phone_line_id']) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token_delete_sda) ?>">
                                    <input type="hidden" name="returnLink" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                                    <button type="submit" name="delete_sda" class="btn-sda-delete" onclick="return confirm('Voulez-vous vraiment supprimer ce numéro SDA ?');" title="Supprimer">
                                        <svg width="32" height="32" viewBox="0 0 24 24">
                                            <path fill="#000000" d="M4 8h16v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1zm3-3V3a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v2h5v2H2V5zm2-1v1h6V4zm0 8v6h2v-6zm4 0v6h2v-6z"/>
                                        </svg>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- Ajouter -->
        <?php if(in_array($_SESSION['user_role'], ['admin', 'super-admin'])): ?>
            <div class="add-sda">
                <a href="/index.php?page=sda_number/add&sda_phone_line_id=<?= $phone_line['phone_line_id'] ?>">
                    <svg width="25" height="25" viewBox="0 0 56 56">
                        <path fill="#030303" d="M10.785 20.723c0-6.657 3.516-10.102 10.195-10.102h21.047v-.586c0-4.828-2.46-7.265-7.36-7.265H9.638c-4.899 0-7.36 2.437-7.36 7.265v24.656c0 4.852 2.461 7.266 7.36 7.266h1.148Zm10.57 32.508h25.032c4.875 0 7.336-2.415 7.336-7.243V21.074c0-4.828-2.461-7.265-7.336-7.265H21.356c-4.922 0-7.36 2.437-7.36 7.265v24.914c0 4.828 2.438 7.243 7.36 7.243m12.563-9.165c-1.078 0-1.945-.867-1.945-2.039v-6.515h-6.586c-1.078 0-2.063-.938-2.063-2.016c0-1.031.985-1.992 2.063-1.992h6.586v-6.492c0-1.149.867-2.016 1.945-2.016s1.922.867 1.922 2.016v6.492h6.375c1.195 0 2.18.914 2.18 1.992c0 1.102-.985 2.016-2.18 2.016H35.84v6.515c0 1.172-.844 2.04-1.922 2.04"/>
                    </svg>
                    Ajouter un numéro SDA
                </a>
            </div>
        <?php endif; ?>
    </section>

    <!-- permet le retour à la liste -->
    <p class="return_link">
        <a href="/index.php?page=phone_line/index&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>" class="return_link-detail">
            Retour à la liste des lignes téléphoniques
        </a>
    </p>
