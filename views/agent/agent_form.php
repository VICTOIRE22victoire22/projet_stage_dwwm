<!-- 
    Vue affichant le formulaire d’ajout d’un agent.

    Aucun affichage dynamique : le formulaire envoie les données en POST
    pour création en base via le contrôleur correspondant.
-->
    <h1 class="form-title">
        Ajout d’un nouvel agent
        <!-- Icône animée décorative -->
        <span class="animated-icon">
            <svg viewBox="0 0 64 64" width="60" height="60">
                <!-- Ombre -->
                <ellipse cx="32" cy="60" rx="8" ry="4" fill="#555">
                    <animate attributeName="rx" values="7;9;7" dur="1.2s" repeatCount="indefinite" />
                </ellipse>
                <!-- Tête -->
                <circle cx="32" cy="24" r="12" fill="#00aaff" stroke="#fff" stroke-width="2">
                    <animateTransform attributeName="transform" type="rotate" values="-5 32 24;5 32 24;-5 32 24" dur="1.5s" repeatCount="indefinite" />
                </circle>
                <!-- Chapeau / badge animé -->
                <rect x="24" y="12" width="16" height="4" fill="#ff9900">
                    <animateTransform attributeName="transform" type="translate" values="0,0;0,-2;0,0" dur="1.5s" repeatCount="indefinite"/>
                </rect>
                <!-- Corps -->
                <path d="M32 36 C25 36, 20 52, 32 52 C44 52, 39 36, 32 36 Z" fill="#00aaff" stroke="#fff" stroke-width="2">
                    <animateTransform attributeName="transform" type="translate" values="0,0;0,-2;0,0" dur="1.2s" repeatCount="indefinite" />
                </path>
                <!-- Yeux -->
                <circle cx="28" cy="22" r="1.5" fill="#fff"/>
                <circle cx="36" cy="22" r="1.5" fill="#fff"/>
                <!-- Sourire -->
                <path d="M28 28 Q32 32 36 28" stroke="#fff" stroke-width="2" fill="transparent">
                    <animate attributeName="d" values="M28 28 Q32 32 36 28; M28 28 Q32 30 36 28; M28 28 Q32 32 36 28" dur="1.2s" repeatCount="indefinite" />
                </path>
            </svg>
        </span>
    </h1>
 
    <section class="form-container">
        <!-- Formulaire d’ajout d’un agent -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="agent_firstname" class="form-label">Prénom :</label>
                <input type="text" class="form-input" id="agent_firstname" name="agent_firstname" required>
            </div>

            <div class="form-group">
                <label for="agent_lastname" class="form-label">Nom :</label>
                <input type="text" class="form-input" id="agent_lastname" name="agent_lastname" required>
            </div>

            <div class="form-group">
                <label for="agent_service" class="form-label">Service :</label>
                <input type="text" class="form-input" id="agent_service" name="agent_service" required>
            </div>

            <!-- Input pour le CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-actions">
                <input type="submit" class="form-submit-btn" value="Envoyer">
            </div>
        </form>
        <!-- Lien de retour vers la liste des agents -->
        <p class="return_link">
            <a href="/index.php?page=agent/index&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>" class="return_link-form">
                Retour à la liste des agents
            </a>
        </p>
    </section>
