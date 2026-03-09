<!-- 
    Code HTML permettant d'afficher le formulaire d'ajout d'un bâtiment.
    Ici le code PHP permet d'afficher dynamiquement les options du select afin de choisir
    le site auquel est lié le bâtiment.
-->
 
  <h1 class="form-title">
    Ajout d’un nouveau bâtiment
    <span class="animated-icon">
        <!-- Icône SVG animée du bâtiment -->
        <svg viewBox="0 0 64 64" width="60" height="60">
          <!-- Ombre au sol -->
          <rect x="18" y="58" width="28" height="4" fill="#555">
            <animate attributeName="width" values="26;32;26" dur="1.2s" repeatCount="indefinite"/>
          </rect>
          <!-- Corps du bâtiment -->
          <rect x="20" y="24" width="24" height="32" fill="#007bff" stroke="#fff" stroke-width="2">
            <animateTransform attributeName="transform" type="translate" values="0,0;0,-3;0,0" dur="1.2s" repeatCount="indefinite"/>
          </rect>
          <!-- Toit -->
          <polygon points="18,24 32,12 46,24" fill="#0056b3" stroke="#fff" stroke-width="2">
            <animateTransform attributeName="transform" type="translate" values="0,0;0,-3;0,0" dur="1.2s" repeatCount="indefinite"/>
          </polygon>
          <!-- Fenêtres -->
          <rect x="24" y="30" width="4" height="4" fill="#fff">
            <animate attributeName="fill" values="#fff;#ffeb3b;#fff" dur="2s" repeatCount="indefinite"/>
          </rect>
          <rect x="30" y="30" width="4" height="4" fill="#ffeb3b">
            <animate attributeName="fill" values="#ffeb3b;#fff;#ffeb3b" dur="2.3s" repeatCount="indefinite"/>
          </rect>
          <rect x="36" y="30" width="4" height="4" fill="#fff">
            <animate attributeName="fill" values="#fff;#ffeb3b;#fff" dur="2.6s" repeatCount="indefinite"/>
          </rect>
          <rect x="24" y="38" width="4" height="4" fill="#ffeb3b">
            <animate attributeName="fill" values="#ffeb3b;#fff;#ffeb3b" dur="1.8s" repeatCount="indefinite"/>
          </rect>
          <rect x="30" y="38" width="4" height="4" fill="#fff">
            <animate attributeName="fill" values="#fff;#ffeb3b;#fff" dur="2s" repeatCount="indefinite"/>
          </rect>
          <rect x="36" y="38" width="4" height="4" fill="#ffeb3b">
            <animate attributeName="fill" values="#ffeb3b;#fff;#ffeb3b" dur="2.4s" repeatCount="indefinite"/>
          </rect>
          <!-- Porte -->
          <rect x="30" y="46" width="4" height="8" fill="#2b2f33"/>
        </svg>
    </span>
  </h1>

    <section class="form-container">
        <form method="POST" action="">
            
            <div class="form-group">
                
                <label for="building_name" class="form-label">Nom du bâtiment :</label>
                <input type="text" class="form-input" id="building_name" name="building_name" required>
            </div>

            <div class="form-group">
                <label for="building_address" class="form-label">Adresse :</label>
                <input type="text" class="form-input" id="building_address" name="building_address" required>
            </div>

            <div class="form-group">
                <label for="building_erp_category" class="form-label">Catégorie ERP :</label>
                <input type="number" class="form-input" id="building_erp_category" name="building_erp_category" required>
            </div>

            <div class="form-group">
                <label for="building_site_id" class="form-label">Site lié au bâtiment :</label>
                <select name="building_site_id" id="building_site_id" class="form-input" required>
                    <?php foreach ($sites as $site): ?>
                        <option value="<?= htmlspecialchars($site['site_id']) ?>">
                            <?= htmlspecialchars($site['site_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Input pour le CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-actions">
                <input type="submit" class="form-submit-btn" value="Envoyer">
            </div>
        </form>
        <!-- permet le retour à la liste -->
        <p class="return_link">
            <a href="/index.php?page=building/index&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>" class="return_link-form">
                Retour à la liste des bâtiments
            </a>
        </p>
    </section>
