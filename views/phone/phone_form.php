
    <h1 class="form-title">
        Ajout d’un téléphone fixe
        <span class="animated-icon">
            <svg viewBox="0 0 64 64" width="70" height="70">
            <!-- Base du téléphone -->
                <rect x="16" y="30" width="32" height="16" rx="4" ry="4" fill="#00aaff" stroke="#fff" stroke-width="2">
                    <animateTransform attributeName="transform" type="translate" values="0,0;0,-2;0,0" dur="1.2s" repeatCount="indefinite"/>
                </rect>
            <!-- Combiné rétro -->
                <path d="M18,30 C12,20 52,20 46,30" fill="none" stroke="#00aaff" stroke-width="4" stroke-linecap="round">
                    <animateTransform attributeName="transform" type="translate" values="0,0;0,-3;0,0" dur="1.2s" repeatCount="indefinite"/>
                </path>
                <!-- Cadran rotatif -->
                <circle cx="32" cy="38" r="4" fill="#fff" stroke="#00aaff" stroke-width="2">
                    <animateTransform attributeName="transform" type="rotate" from="0 32 38" to="360 32 38" dur="4s" repeatCount="indefinite"/>
                </circle>
                <!-- Ombre -->
                <ellipse cx="32" cy="48" rx="8" ry="2" fill="#555">
                    <animate attributeName="rx" values="7;9;7" dur="1.2s" repeatCount="indefinite"/>
                </ellipse>
            </svg>
        </span>
    </h1>

    <section class="form-container">
        <form method="POST" action="">

            <div class="form-group">
                <label for="phone_brand" class="form-label">Marque du téléphone :</label>
                <input type="text" class="form-input" id="phone_brand" name="phone_brand" required>
            </div>

            <div class="form-group">
                <label for="phone_model" class="form-label">Modèle du téléphone :</label>
                <input type="text" class="form-input" id="phone_model" name="phone_model" required>
            </div>

            <div class="form-group">
                <label for="phone_status" class="form-label">Statut du téléphone :</label>
                <select name="phone_status" id="phone_status" class="form-input" required>
                    <option value="en service">En service</option>
                    <option value="cassé">Cassé</option>
                    <option value="en stock">En stock</option>
                </select>
            </div>

            <div class="form-group">
                <label for="phone_line_id" class="form-label">Ligne téléphonique liée :</label>
                <select name="phone_line_id" id="phone_line_id" class="form-input">
                    <?php foreach ($phone_lines as $phone_line): ?>
                        <option value="<?= htmlspecialchars($phone_line['phone_line_id']) ?>">
                            <?= htmlspecialchars($phone_line['phone_line_number']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="phone_agent_id" class="form-label">Téléphone attribué à :</label>
                <select name="phone_agent_id" id="phone_agent_id" class="form-input">
                    <option value="">Aucun agent</option>
                    <?php foreach ($agents as $agent): ?>
                        <option value="<?= htmlspecialchars($agent['agent_id']) ?>">
                            <?= htmlspecialchars($agent['agent_firstname']) . " " . htmlspecialchars($agent['agent_lastname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="phone_building_id" class="form-label">Téléphone situé :</label>
                <select name="phone_building_id" id="phone_building_id" class="form-input">
                    <option value="">Aucun bâtiment</option>
                    <?php foreach ($buildings as $building): ?>
                        <option value="<?= htmlspecialchars($building['building_id']) ?>">
                            <?= htmlspecialchars($building['building_name']) ?>
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
            <a href="/index.php?page=phone/index&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>" class="return_link-form">
                Retour à la liste des téléphones
            </a>
        </p>
    </section>