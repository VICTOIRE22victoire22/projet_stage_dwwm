
    <h1 class="form-title">Modifier un transmetteur</h1>

    <section class="form-container">
        <!-- L’action conserve le type et l’ID -->
        <form method="POST" action="/index.php?page=equipment/edit&type=<?= htmlspecialchars($type) ?>&id=<?= htmlspecialchars($id) ?>">
            <!-- Champ caché pour garder le type -->
            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">

            <div class="form-group">
                <label for="equipment_series_number" class="form-label">Numéro de série de l'équipement :</label>
                <input type="text" class="form-input" id="equipment_series_number" name="equipment_series_number" value="<?= htmlspecialchars($equipment_series_number) ?>" required>
            </div>

            <div class="form-group">
                <label for="equipment_model" class="form-label">Modèle de l'équipement :</label>
                <input type="text" class="form-input" id="equipment_model" name="equipment_model" value="<?= htmlspecialchars($equipment_model) ?>" required>
            </div>

            <div class="form-group">
                <label for="equipment_type" class="form-label">Type d'équipement :</label>
                <select name="equipment_type" id="equipment_type" class="form-input" required>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= htmlspecialchars($t) ?>" <?= $equipment_type === $t ? 'selected' : "" ?>>
                            <?= ucfirst($t) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Input pour le CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-actions">
                <input type="submit" class="form-submit-btn" value="Envoyer">
            </div>
        </form>
        <!-- permet le retour à la liste -->
        <p class="return_link">
            <a href="/index.php?page=equipment/index&type=<?= urlencode($type) ?>&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>" class="return_link-form">
                Retour à la liste des <?= htmlspecialchars($type) ?>s
            </a>
        </p>
    </section>

