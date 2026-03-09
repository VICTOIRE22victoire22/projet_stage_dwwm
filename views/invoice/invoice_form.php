
    <h1 class="form-title" style="display:flex; align-items:center; justify-content:center; gap:10px;">
    Ajout d’une nouvelle facture
    <span class="animated-icon" style="display:inline-block; margin-top:2px;">
        <!-- Icône SVG animée pour Facture -->
        <svg  viewBox="0 0 64 64" width="60" height="60">
          <!-- Ombre -->
          <rect x="18" y="58" width="28" height="4" fill="#555">
            <animate attributeName="width" values="26;32;26" dur="1.2s" repeatCount="indefinite"/>
          </rect>
          <!-- Feuille principale -->
          <rect x="20" y="10" width="24" height="44" rx="2" ry="2" fill="#007bff" stroke="#fff" stroke-width="2">
            <animateTransform attributeName="transform" type="translate" values="0,0;0,-3;0,0"
                              dur="1.2s" repeatCount="indefinite"/>
          </rect>
          <!-- Coins de page plié -->
          <polygon points="44,10 44,22 32,10" fill="#0056b3" stroke="#fff" stroke-width="2">
            <animateTransform attributeName="transform" type="translate" values="0,0;0,-3;0,0"
                              dur="1.2s" repeatCount="indefinite"/>
          </polygon>
          <!-- Lignes de texte -->
          <rect x="24" y="20" width="16" height="2" fill="#fff" opacity="0.8">
            <animate attributeName="width" values="16;10;16" dur="2s" repeatCount="indefinite"/>
          </rect>
          <rect x="24" y="26" width="12" height="2" fill="#fff" opacity="0.7">
            <animate attributeName="width" values="12;18;12" dur="2s" begin="0.5s" repeatCount="indefinite"/>
          </rect>
          <!-- Symbole € animé -->
          <text x="32" y="46" text-anchor="middle" font-size="16" fill="#ffeb3b" font-family="Arial, sans-serif">€</text>
          <circle cx="32" cy="42" r="10" fill="none" stroke="#ffeb3b" stroke-width="1.5" opacity="0.8">
            <animate attributeName="r" values="8;12;8" dur="1.8s" repeatCount="indefinite"/>
            <animate attributeName="opacity" values="0.8;0.2;0.8" dur="1.8s" repeatCount="indefinite"/>
          </circle>
        </svg>
    </span>
</h1>

    <section class="form-container">
        <form method="POST" action="">

            <div class="form-group">
                <label for="invoice_number" class="form-label">Numéro de facture :</label>
                <input type="text" class="form-input" id="invoice_number" name="invoice_number" required>
            </div>

            <div class="form-group">
                <label for="invoice_date" class="form-label">Date de la facture :</label>
                <input type="date" class="form-input" id="invoice_date" name="invoice_date" required>
            </div>

            <div class="form-group">
                <label for="invoice_amount" class="form-label">Montant de la facture :</label>
                <input type="number" class="form-input" min="0" step="0.01" id="invoice_amount" name="invoice_amount" required>
            </div>

            <div class="form-group">
                <label for="invoice_account_number" class="form-label">Numéro de compte :</label>
                <input type="text" class="form-input" id="invoice_account_number" name="invoice_account_number" required>
            </div>

            <div class="form-group">
                <label for="invoice_sub_account_number" class="form-label">Numéro de sous-compte :</label>
                <input type="text" class="form-input" id="invoice_sub_account_number" name="invoice_sub_account_number">
            </div>

            <div class="form-group">
                <label for="invoice_status" class="form-label">Statut de la facture :</label>
                <select name="invoice_status" id="invoice_status" class="form-input" required>
                    <option value="" disabled selected hidden>Sélectionner un statut</option>
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= htmlspecialchars($s) ?>" <?= (isset($_POST['invoice_status']) && $_POST['invoice_status'] === $s) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($s)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="invoice_provider_id" class="form-label">Opérateur :</label>
                <select name="invoice_provider_id" id="invoice_provider_id" class="form-input" required>
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
            <a href="/index.php?page=invoice/index&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>" class="return_link-form">
                Retour à la liste des factures
            </a>
        </p>
    </section>

