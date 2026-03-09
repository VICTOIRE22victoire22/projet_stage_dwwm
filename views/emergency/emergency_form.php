<!-- 
    Code HTML permettant d'afficher le formulaire d'ajout d'une urgence.
    Ici le code PHP permet d'afficher dynamiquement les options des selects.
-->
    <h1 class="form-title">
        Ajout d’une urgence
        <span class="animated-icon">
            <svg viewBox="0 0 64 64" width="70" height="70">
                <!-- Croix principale -->
                <rect x="28" y="16" width="8" height="32" fill="#00aaff" rx="1" ry="1">
                    <animate attributeName="fill" values="#00aaff;#0077cc;#00aaff" dur="1s" repeatCount="indefinite"/>
                </rect>
                <rect x="16" y="28" width="32" height="8" fill="#00aaff" rx="1" ry="1">
                    <animate attributeName="fill" values="#00aaff;#0077cc;#00aaff" dur="1s" repeatCount="indefinite"/>
                </rect>
                <!-- Ombre -->
                <ellipse cx="32" cy="52" rx="12" ry="3" fill="#555">
                    <animate attributeName="rx" values="11;13;11" dur="1.2s" repeatCount="indefinite"/>
                </ellipse>
            </svg>
        </span>
    </h1>

    <section class="form-container">
        <form method="POST" action="">
            
            <div class="form-group">
                <label for="emergency_phone_line_id" class="form-label">Numéro de la ligne :</label>
                <input type ="text" id="emergency_phone_line_number" name="emergency_phone_line_number" class="form-input" required>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-box">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="emergency_building_id" class="form-label">Bâtiment concerné :</label>
                <select name="emergency_building_id" id="emergency_building_id" class="form-input" required>
                    <option value="" disabled selected hidden>Sélectionner un bâtiment</option>
                    <?php foreach ($buildings as $building): ?>
                        <option value="<?= htmlspecialchars($building['building_id']) ?>">
                            <?= htmlspecialchars($building['building_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">        
                <label for="emergency_equipment_id" class="form-label">Équipement d'urgence :</label>
                <select name="emergency_equipment_id" id="emergency_equipment_id" class="form-input" required>
                    <option value="" disabled selected hidden>Sélectionner un équipement</option>
                    <?php foreach ($equipments as $equipment): ?>
                        <option value="<?= htmlspecialchars($equipment['equipment_id']) ?>">
                            <?= htmlspecialchars($equipment['equipment_type']) ?> (Modèle : <?= htmlspecialchars($equipment['equipment_model']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="emergency_type" class="form-label">Type d'urgence :</label>
                <select name="emergency_type" id="emergency_type" class="form-input" required>
                    <option value="" disabled selected hidden>Sélectionner un type d'urgence</option>
                    <option value="alarme intrusion">Alarme intrusion</option>
                    <option value="alarme incendie">Alarme incendie</option>
                    <option value="ascenseur">Ascenseur</option>
                    <option value="bouton urgence">Bouton d'urgence</option>
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

