<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un numéro SDA</title>
    <link rel="stylesheet" href="/../css/form.css"/>
</head>
<body>
    <h1 class="form-title">Modifier un numéro SDA</h1>

    <section class="form-container">
        <form method="POST" action="">
            <input type="hidden" name="phone_line_id" value="<?= htmlspecialchars($selected_phone_line_id) ?>">

            <div class="form-group">
                <label for="sda_number" class="form-label">Numéro SDA :</label>
                <input type="text" class="form-input" id="sda_number" name="sda_number" value="<?= htmlspecialchars($sda_number_value) ?? '' ?>" required>
            </div>

            <div class="form-group">
                <label for="sda_phone_line_id" class="form-label">Ligne téléphonique associée :</label>
                <select name="sda_phone_line_id" class="form-input" id="sda_phone_line_id">
                    <?php foreach ($phone_lines as $phone_line): ?>
                        <option value="<?= htmlspecialchars($phone_line['phone_line_id']) ?>" <?= $phone_line['phone_line_id'] == $sda_number_phone_line_id ? 'selected' : "" ?>>
                            <?= htmlspecialchars($phone_line['phone_line_number']) ?>
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
        <!-- permet le retour à la page de détail si on souhaite annuler l'action -->
        <p>
            <a href="/index.php?page=phone_line/detail&id=<?= htmlspecialchars($selected_phone_line_id) ?>" class="return_link-form">
                Retour à la page de détail
            </a>
        </p>
    </section>
</body>
</html>
