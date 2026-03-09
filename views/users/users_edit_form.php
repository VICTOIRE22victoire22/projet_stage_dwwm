
    <h1 class="form-title">
        Modifier un utilisateur
        <span class="animated-icon" style="display: flex; align-items: center; transform: translateY(-6px);">
            <svg viewBox="0 0 64 64" width="60" height="60" style="display: block;">
                <!-- Ombre au sol -->
                <ellipse cx="32" cy="60" rx="8" ry="4" fill="#555">
                    <animate attributeName="rx" values="7;9;7" dur="1.2s" repeatCount="indefinite" />
                </ellipse>

                <!-- Buste de l'opérateur -->
                <circle cx="32" cy="28" r="12" fill="#00aaff" stroke="#fff" stroke-width="2">
                    <animateTransform attributeName="transform" type="translate" values="0,0;0,-4;0,0"
                                  dur="1.2s" repeatCount="indefinite" />
                </circle>

                <!-- Épaules -->
                <rect x="20" y="40" width="24" height="14" rx="4" ry="4" fill="#00aaff" stroke="#fff" stroke-width="2">
                    <animateTransform attributeName="transform" type="translate" values="0,0;0,-2;0,0"
                                  dur="1.2s" repeatCount="indefinite" />
                </rect>

                <!-- Halo animé -->
                <circle cx="32" cy="28" r="16" fill="none" stroke="#ffeb3b" stroke-width="1" opacity="0.6">
                    <animate attributeName="r" values="14;18;14" dur="2s" repeatCount="indefinite" />
                    <animate attributeName="opacity" values="0.6;0.2;0.6" dur="2s" repeatCount="indefinite" />
                </circle>
            </svg>
        </span>
    </h1>

    <section class="form-container">
        <form method="POST" action="">

            <div class="form-group">
                <label for="user_firstname" class="form-label">Prénom :</label>
                <input type="text" class="form-input" id="user_firstname" name="user_firstname" value="<?= htmlspecialchars($firstname) ?>" required>
            </div>

            <div class="form-group">
                <label for="user_lastname" class="form-label">Nom :</label>
                <input type="text" class="form-input" id="user_lastname" name="user_lastname" value="<?= htmlspecialchars($lastname) ?>" required>
            </div>

            <div class="form-group">
                <label for="user_email" class="form-label">Email :</label>
                <input type="email" class="form-input" id="user_email" name="user_email" value="<?= htmlspecialchars($email) ?>" required>
            </div>

            <div class="form-group">
                <label for="user_login" class="form-label">Login :</label>
                <input type="text" class="form-input" id="user_login" name="user_login" value="<?= htmlspecialchars($login) ?>" required>
            </div>

            <div class="form-group">
                <label for="user_password" class="form-label">Mot de passe :</label>
                <input type="password" class="form-input" id="user_password" name="user_password">
            </div>

            <div class="form-group">
                <label for="user_role" class="form-label">Rôle de l'utilisateur :</label>
                <select name="user_role" class="form-input" id="user_role" required>
                    <?php foreach ($user_roles as $user_role): ?>
                        <option value="<?= htmlspecialchars($user_role) ?>" <?= $role === $user_role ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user_role) ?>
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
            <a href="/index.php?page=users/index&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? '') ?>&order=<?= urlencode($_GET['order'] ?? 'asc') ?>&page_number=<?= urlencode($_GET['page_number'] ?? 1) ?>&limit=<?= urlencode($_GET['limit'] ?? 10) ?>" class="return_link-form">
                Retour à la liste des utilisateurs
            </a>
        </p>
    </section>

