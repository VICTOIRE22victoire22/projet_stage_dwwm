
    <h1 class="form-title" >
        Ajout d’une ligne téléphonique
        <span class="animated-icon">
            <svg viewBox="0 0 64 64" width="70" height="70">
                <!-- Points de connexion -->
                <circle cx="16" cy="32" r="5" fill="#0077cc">
                    <animate attributeName="r" values="5;7;5" dur="1.2s" repeatCount="indefinite"/>
                </circle>
                <circle cx="48" cy="32" r="5" fill="#0077cc">
                    <animate attributeName="r" values="5;7;5" dur="1.2s" repeatCount="indefinite" begin="0.3s"/>
                </circle>
                <!-- Ligne téléphonique -->
                <line x1="16" y1="32" x2="48" y2="32" stroke="#0077cc" stroke-width="3" stroke-linecap="round">
                    <animate attributeName="stroke-dasharray" values="0,32;32,0;0,32" dur="1.2s" repeatCount="indefinite"/>
                </line>
                <!-- Ombre pulsante -->
                <ellipse cx="32" cy="50" rx="12" ry="3" fill="#555">
                    <animate attributeName="rx" values="11;13;11" dur="1.2s" repeatCount="indefinite"/>
                </ellipse>
            </svg>
        </span>
    </h1>

    <section class="form-container">
        <form method="POST" action="">

            <div class="form-group">
                <label for="phone_line_number" class="form-label">Numéro de ligne téléphonique :</label>
                <input type="text" class="form-input" id="phone_line_number" name="phone_line_number" required>
            </div>

            <div class="form-group">
                <label for="phone_line_status" class="form-label">Statut de la ligne téléphonique :</label>
                <select name="phone_line_status" id="phone_line_status" class="form-input" required>
                    <option value="en service">En service</option>
                    <option value="résiliée">Résiliée</option>
                </select>
            </div>

            <div class="form-group">
                <label for="phone_line_termination_number" class="form-label">Numéro de résiliation :</label>
                <input type="text" class="form-input" id="phone_line_termination_number" name="phone_line_termination_number">
            </div>

            <div class="form-group">
                <label for="phone_line_termination_date" class="form-label">Date de résiliation :</label>
                <input type="date" class="form-input" id="phone_line_termination_date" name="phone_line_termination_date">
            </div>

            <div class="form-group">
                <label for="phone_line_box_return_date" class="form-label">Date de retour de la box :</label>
                <input type="date" class="form-input" id="phone_line_box_return_date" name="phone_line_box_return_date">
            </div>

            <div class="form-group">
                <label for="phone_line_designation" class="form-label">Désignation :</label>
                <input type="text" class="form-input" id="phone_line_designation" name="phone_line_designation">
            </div>

            <div class="form-group">
                <label for="phone_line_agent_id" class="form-label">Ligne téléphonique attribuée à :</label>
                <select name="phone_line_agent_id" id="phone_line_agent_id" class="form-input">
                    <option value="">Aucun agent</option>
                    <?php foreach ($agents as $agent): ?>
                        <option value="<?= htmlspecialchars($agent['agent_id']) ?>">
                            <?= htmlspecialchars($agent['agent_firstname']) . " " . htmlspecialchars($agent['agent_lastname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="phone_line_building_id" class="form-label">Bâtiment de la ligne :</label>
                <select name="phone_line_building_id" id="phone_line_building_id" class="form-input">
                    <option value="">Aucun bâtiment</option>
                    <?php foreach ($buildings as $building): ?>
                        <option value="<?= htmlspecialchars($building['building_id']) ?>">
                            <?= htmlspecialchars($building['building_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="phone_line_offer_id" class="form-label">Offre de la ligne :</label>
                <select name="phone_line_offer_id" id="phone_line_offer_id" class="form-input">
                    <option value="">Aucune offre</option>
                    <?php foreach ($offers as $offer): ?>
                        <option value="<?= htmlspecialchars($offer['offer_id']) ?>">
                            <?= htmlspecialchars($offer['offer_name']) ?>
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
            <a href="/index.php?page=phone_line/index&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>" class="return_link-form">
                Retour à la liste des lignes téléphoniques
            </a>
        </p>
    </section>

