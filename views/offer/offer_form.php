
    <h1 class="form-title" style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-bottom: 20px;">
        Ajout d’une nouvelle offre
        <span class="animated-icon" style="display: flex; align-items: center; transform: translateY(-6px);">
            <svg viewBox="0 0 64 64" width="60" height="60" style="display: block;">
                <!-- Ombre -->
                <rect x="18" y="58" width="28" height="4" fill="#555">
                    <animate attributeName="width" values="26;32;26" dur="1.2s" repeatCount="indefinite" />
                </rect>
                <!-- Boîte cadeau principale -->
                <rect x="20" y="26" width="24" height="24" rx="2" ry="2" fill="#007bff" stroke="#fff" stroke-width="2">
                    <animateTransform attributeName="transform" type="translate" values="0,0;0,-4;0,0" dur="1.2s" repeatCount="indefinite" />
                </rect>
                <!-- Rubans -->
                <rect x="31" y="26" width="2" height="24" fill="#fff" />
                <rect x="20" y="36" width="24" height="2" fill="#fff" />
                <path d="M32 26 C26 20,24 22,26 26 Z" fill="#ffeb3b">
                    <animate attributeName="fill" values="#ffeb3b;#fff;#ffeb3b" dur="1.5s" repeatCount="indefinite" />
                </path>
                <path d="M32 26 C38 20,40 22,38 26 Z" fill="#ffeb3b">
                    <animate attributeName="fill" values="#ffeb3b;#fff;#ffeb3b" dur="1.5s" begin="0.3s" repeatCount="indefinite" />
                </path>
                <!-- Halo -->
                <circle cx="32" cy="38" r="14" fill="none" stroke="#ffeb3b" stroke-width="1" opacity="0.6">
                    <animate attributeName="r" values="12;16;12" dur="2s" repeatCount="indefinite" />
                    <animate attributeName="opacity" values="0.6;0.2;0.6" dur="2s" repeatCount="indefinite" />
                </circle>
            </svg>
        </span>
    </h1>

    <section class="form-container">
        <form method="POST" action="">

            <div class="form-group">
                <label for="offer_name" class="form-label">Nom de l'offre :</label>
                <input type="text" class="form-input" id="offer_name" name="offer_name" required>
            </div>

            <div class="form-group">
                <label for="offer_price" class="form-label">Montant de l'offre :</label>
                <input type="number" class="form-input" min="0" step="0.01" id="offer_price" name="offer_price" required>
            </div>

            <div class="form-group">
                <label for="offer_context" class="form-label">Contexte de l'offre :</label>
                <select name="offer_context" id="offer_context" class="form-input" required>
                    <option value="" disabled selected hidden>Sélectionner un contexte de marché</option>
                    <?php foreach ($offer_contexts as $context): ?>
                        <option value="<?= htmlspecialchars($context) ?>">
                            <?= ucfirst($context) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="offer_type" class="form-label">Type d'offre :</label>
                <select name="offer_type" id="offer_type" class="form-input" required>
                    <option value="" disabled selected hidden>Sélectionner un type d'offre</option>
                    <?php foreach ($offer_types as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>">
                            <?= ucfirst($type) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="offer_provider_id" class="form-label">Opérateur :</label>
                <select name="offer_provider_id" id="offer_provider_id" class="form-input" required>
                    <option value="" disabled selected hidden>Sélectionner un opérateur</option>
                    <?php foreach ($providers as $provider): ?>
                        <option value="<?= htmlspecialchars($provider['provider_id']) ?>">
                            <?= htmlspecialchars($provider['provider_name']) ?>
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
            <a href="/index.php?page=offer/index&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>" class="return_link-form">
                Retour à la liste des offres
            </a>
        </p>
    </section>

