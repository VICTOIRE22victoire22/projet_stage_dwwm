
    <h1 class="form-title">
    Ajout d’un téléphone mobile
    <span class="animated-icon">
        <svg viewBox="0 0 64 64" width="70" height="70">
            <!-- Corps du téléphone -->
            <rect x="22" y="12" width="20" height="40" rx="4" ry="4" fill="#00aaff" stroke="#fff" stroke-width="2">
                <animateTransform attributeName="transform" type="translate" values="0,0;0,-3;0,0" dur="1.2s" repeatCount="indefinite"/>
            </rect>

            <!-- Écran -->
            <rect x="25" y="16" width="14" height="28" rx="2" ry="2" fill="#fff">
                <animate attributeName="opacity" values="1;0.8;1" dur="1.2s" repeatCount="indefinite"/>
            </rect>

            <!-- Bouton inférieur -->
            <circle cx="32" cy="44" r="2" fill="#fff">
                <animate attributeName="r" values="2;3;2" dur="1.2s" repeatCount="indefinite"/>
            </circle>

            <!-- Ombre -->
            <ellipse cx="32" cy="56" rx="8" ry="2" fill="#555">
                <animate attributeName="rx" values="7;9;7" dur="1.2s" repeatCount="indefinite"/>
            </ellipse>
        </svg>
    </span>
</h1>

    <section class="form-container">
        <form method="POST" action="">

            <div class="form-group">
                <label for="mobile_brand" class="form-label">Marque du téléphone mobile :</label>
                <input type="text" class="form-input" id="mobile_brand" name="mobile_brand" required>
            </div>

            <div class="form-group">
                <label for="mobile_model" class="form-label">Modèle du téléphone mobile :</label>
                <input type="text" class="form-input" id="mobile_model" name="mobile_model" required>
            </div>

            <div class="form-group">
                <label for="mobile_imei" class="form-label">IMEI du téléphone mobile :</label>
                <input type="text" class="form-input" id="mobile_imei" name="mobile_imei" required>
            </div>

            <div class="form-group">
                <label for="mobile_purchase_date" class="form-label">Date d'achat :</label>
                <input type="date" class="form-input" id="mobile_purchase_date" name="mobile_purchase_date" required>
            </div>

            <div class="form-group">
                <label for="mobile_exit_date" class="form-label">Date de sortie du parc :</label>
                <input type="date" class="form-input" id="mobile_exit_date" name="mobile_exit_date">
            </div>

            <div class="form-group">
                <label class="form-label">Téléphone mobile reconditionné :</label>
                <div class="form-radio-group">
                    <label>
                        <!-- <input type="radio" class="form-input" name="mobile_reconditioned" value="1" required> Oui -->
                        <input type="radio" name="mobile_reconditioned" value="1" required> Oui
                    </label>
                    <label>
                        <!-- <input type="radio" class="form-input" name="mobile_reconditioned" value="0" required> Non -->
                        <input type="radio" name="mobile_reconditioned" value="0" required> Non 
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="mobile_status" class="form-label">Statut du téléphone mobile :</label>
                <select name="mobile_status" id="mobile_status" class="form-input" required>
                    <option value="en stock">En stock</option>
                    <option value="activé">Activé</option>
                    <option value="volé">Volé</option>
                    <option value="perdu">Perdu</option>
                    <option value="cassé">Cassé</option>
                    <option value="vendu">Vendu</option>
                    <option value="sortie de parc">Sortie de parc</option>
                </select>
            </div>

            <div class="form-group">
                <label for="mobile_phone_line_id" class="form-label">Ligne téléphonique liée :</label>
                <select name="mobile_phone_line_id" id="mobile_phone_line_id" class="form-input">
                    <option value="" disabled selected hidden>Sélectionner une ligne téléphonique</option>
                    <?php foreach ($phone_lines as $phone_line): ?>
                        <option value="<?= htmlspecialchars($phone_line['phone_line_id']) ?>">
                            <?= htmlspecialchars($phone_line['phone_line_number']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="mobile_agent_id" class="form-label">Téléphone mobile attribué à :</label>
                <select name="mobile_agent_id" id="mobile_agent_id" class="form-input">
                    <option value="" disabled selected hidden>Sélectionner un agent</option>
                    <option value="">Aucun agent</option>
                    <?php foreach ($agents as $agent): ?>
                        <option value="<?= htmlspecialchars($agent['agent_id']) ?>">
                            <?= htmlspecialchars($agent['agent_firstname']) . " " . htmlspecialchars($agent['agent_lastname']) ?>
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
            <a href="/index.php?page=mobile/index&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>" class="return_link-form">
                Retour à la liste des mobiles
            </a>
        </p>
    </section>

