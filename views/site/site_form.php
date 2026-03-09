 
    <h1 class="form-title">
        Ajout d’un nouveau site
        <span class="animated-icon">
            <!-- Icône de localisation animée -->
            <svg viewBox="0 0 64 64" width="60" height="60">
            <!-- Ombre au sol -->
            <circle cx="32" cy="60" r="4" fill="#555">
                <animate attributeName="r" values="3;5;3" dur="1.2s" repeatCount="indefinite" />
            </circle>
            <!-- Marqueur principal -->
            <path fill="#00aaff" stroke="#fff" stroke-width="2" d="M32 4C22 4 14 12 14 22c0 11.5 16 32 18 34 2-2 18-22.5 18-34C50 12 42 4 32 4z">
                <animateTransform attributeName="transform" type="translate" values="0,0;0,-3;0,0" dur="1.2s" repeatCount="indefinite"/>
            </path>
            <!-- Cercle intérieur -->
            <circle cx="32" cy="22" r="6" fill="#fff">
                <animate attributeName="r" values="6;8;6" dur="1.2s" repeatCount="indefinite" />
            </circle>
            </svg>
        </span>
    </h1>


    <section class="form-container">
        <form method="POST" action="">

            <div class="form-group">
                <label for="site_name" class="form-label">Nom du site :</label>
                <input type="text" class="form-input" id="site_name" name="site_name" required>
            </div>

            <!-- Input pour le CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-actions">
                <input type="submit" class="form-submit-btn" value="Envoyer">
            </div>
        </form>
        
        <!-- permet le retour à la liste -->
        <p>
            <a href="/index.php?page=site/index&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>" class="return_link-form">
                Retour à la liste des sites
            </a>
        </p>
    </section>


