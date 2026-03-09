<!-- 
    Code HTML permettant d'afficher le formulaire d'édition d'un agent.
    Ici le PHP permet d'afficher dans les inputs les données déjà existantes en base de données.
-->

    <h1 class="form-title">
        Ajout d’un équipement
        <span class="animated-icon">
            <svg viewBox="0 0 64 64" width="70" height="70">
                <!-- Boîtier -->
                <rect x="16" y="20" width="32" height="24" rx="4" ry="4" fill="#00aaff" stroke="#fff" stroke-width="2">
                <animateTransform attributeName="transform" type="translate" values="0,0;0,-3;0,0" dur="1.2s" repeatCount="indefinite"/>
                </rect>
                <!-- Voyants lumineux -->
                <circle cx="22" cy="28" r="2" fill="#fff">
                    <animate attributeName="r" values="2;3;2" dur="1.2s" repeatCount="indefinite"/>
                </circle>
                <circle cx="32" cy="28" r="2" fill="#fff">
                    <animate attributeName="r" values="2;3;2" dur="1.2s" repeatCount="indefinite" begin="0.2s"/>
                </circle>
                <circle cx="42" cy="28" r="2" fill="#fff">
                    <animate attributeName="r" values="2;3;2" dur="1.2s" repeatCount="indefinite" begin="0.4s"/>
                </circle>
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
                <label for="equipment_series_number" class="form-label">Numéro de série de l'équipement :</label>
                <input type="text" class="form-input" id="equipment_series_number" name="equipment_series_number" required>
            </div>

            <div class="form-group">
                <label for="equipment_model" class="form-label">Modèle de l'équipement :</label>
                <input type="text" class="form-input" id="equipment_model" name="equipment_model" required>
            </div>

            <div class="form-group">
                <label for="equipment_type" class="form-label">Type d'équipement :</label>
                <select name="equipment_type" id="equipment_type" class="form-input" required>
                    <option value="box" <?= $type === 'box' ? 'selected' : '' ?>>Box</option>
                    <option value="routeur" <?= $type === 'routeur' ? 'selected' : '' ?>>Routeur</option>
                    <option value="transmetteur" <?= $type === 'transmetteur' ? 'selected' : '' ?>>Transmetteur</option>
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


