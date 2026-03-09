<!-- 
    Code HTML permettant d'afficher le formulaire d'édition d'un agent.
    Ici le PHP permet d'afficher dans les inputs les données déjà existantes en base de données,
    et d'afficher les options disponibles pour les différents select.
-->
    <h1 class="form-title">Modification une urgence</h1>
    <section class="form-container">
        <form method="POST" action="">

            <div class="form-group">
                <label for="emergency_phone_line_id" class="form-label">Numéro de la ligne :</label>
                <input type ="text" id="emergency_phone_line_number" name="emergency_phone_line_number" value="<?=htmlspecialchars($emergency_phone_line_number) ?>"class="form-input" required>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-box">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="emergency_building_id" class="form-label">Bâtiment concerné :</label>
                <select name="emergency_building_id" id="emergency_building_id" class="form-input" required>
                    <?php foreach ($buildings as $building): ?>
                        <option value="<?= htmlspecialchars($building['building_id']) ?>" <?= $building['building_id'] === $emergency_building_id ? 'selected' : "" ?>>
                            <?= htmlspecialchars($building['building_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">        
                <label for="emergency_equipment_id" class="form-label">Équipement d'urgence :</label>
                <select name="emergency_equipment_id" id="emergency_equipment_id" class="form-input" required>
                    <?php foreach ($equipments as $equipment): ?>
                        <option value="<?= htmlspecialchars($equipment['equipment_id']) ?>" <?= $equipment['equipment_id'] === $emergency_equipment_id ? 'selected' : "" ?>>
                            <?= htmlspecialchars($equipment['equipment_type']) ?> (Modèle : <?= htmlspecialchars($equipment['equipment_model']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="emergency_type" class="form-label">Type d'urgence :</label>
                <select name="emergency_type" id="emergency_type" class="form-input" required>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= $emergency_type === $type ? 'selected' : "" ?>>
                            <?= ucfirst($type) ?>
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
            <a href="/index.php?page=emergency/index&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>" class="return_link-form">
                Retour à la liste des urgences
            </a>
        </p>
    </section>

