
    <h1 class="form-title">
        Ajout d’un PABX
        <span class="animated-icon">
            <svg  viewBox="0 0 64 64" width="70" height="70">
                <!-- Boîtier PABX bleu -->
                <rect x="14" y="20" width="36" height="24" rx="6" ry="6" fill="#0077cc" stroke="#fff" stroke-width="2">
                    <animateTransform attributeName="transform" type="scale" values="1;1.05;1" dur="1.2s" repeatCount="indefinite" additive="sum"/>
                </rect>
                <!-- Voyants lumineux animés en ligne -->
                <circle cx="22" cy="28" r="2" fill="#fff">
                    <animate attributeName="r" values="2;4;2" dur="1.2s" repeatCount="indefinite"/>
                </circle>
                <circle cx="32" cy="28" r="2" fill="#fff">
                    <animate attributeName="r" values="2;4;2" dur="1.2s" repeatCount="indefinite" begin="0.3s"/>
                </circle>
                <circle cx="42" cy="28" r="2" fill="#fff">
                    <animate attributeName="r" values="2;4;2" dur="1.2s" repeatCount="indefinite" begin="0.6s"/>
                </circle>
                <!-- Ligne animée -->
                <line x1="18" y1="36" x2="46" y2="36" stroke="#00ffff" stroke-width="2">
                    <animate attributeName="stroke-dasharray" values="0,30;30,0;0,30" dur="1.5s" repeatCount="indefinite"/>
                </line>
                <!-- Ombre pulsante -->
                <ellipse cx="32" cy="52" rx="14" ry="3" fill="#555">
                    <animate attributeName="rx" values="13;15;13" dur="1.2s" repeatCount="indefinite"/>
                </ellipse>
            </svg>
        </span>
    </h1>

    <section class="form-container">
        <form method="POST" action="">

            <div class="form-group">
                <label for="pabx_brand" class="form-label">Marque du pabx :</label>
                <input type="text" class="form-input" id="pabx_brand" name="pabx_brand" required>
            </div>

            <div class="form-group">
                <label for="pabx_model" class="form-label">Modèle du pabx :</label>
                <input type="text" class="form-input" id="pabx_model" name="pabx_model" required>
            </div>

            <div class="form-group">
                <label for="pabx_series_number" class="form-label">Numéro de série du pabx :</label>
                <input type="text" class="form-input" id="pabx_series_number" name="pabx_series_number" required>
            </div>

            <div class="form-group">
                <label for="pabx_building_id" class="form-label">Bâtiment du pabx :</label>
                <select name="pabx_building_id" id="pabx_building_id" class="form-input">
                    <option value="" disabled selected hidden>Sélectionner un bâtiment</option>
                    <option value="">En stock</option>
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
            <a href="/index.php?page=pabx/index&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>" class="return_link-form">
                Retour à la liste des PABX
            </a>
        </p>
    </section>


