<?php include '/app/public/views/partials/header.php'; ?>
<link rel="stylesheet" type="text/css" href="public/styles/components/profile.css">
<link rel="stylesheet" type="text/css" href="public/styles/pages/welcome.css">
<link rel="stylesheet" type="text/css" href="public/styles/pages/pets.css">
<link rel="stylesheet" type="text/css" href="public/styles/components/items.css">
<title>Panel Weterynarza</title>
</head>
<body>
    <div class="wrapper">
        <div class="top-bar">
            <a href="/logout" class="back-arrow">&lt;</a> 
            <h1>Moi Pacjenci</h1>
        </div>

        <!-- Pasek filtrowania -->
        <form method="GET" action="/vetPanel" style="display:flex; gap:1vw; justify-content:center; margin-top:12vh; flex-wrap:wrap;">
            <input type="text" name="search" placeholder="Szukaj: imię, czip, nazwisko..." value="<?= htmlspecialchars($currentSearch ?? '') ?>" style="padding:0.6vw 1.2vw; border-radius:1vw; border:1px solid #ccc; font-family:'Jua';">
            
            <select name="type" style="padding:0.6vw 1.2vw; border-radius:1vw; border:1px solid #ccc; font-family:'Jua';">
                <option value="">Wszystkie gatunki</option>
                <option value="dog" <?= ($currentType === 'dog') ? 'selected' : '' ?>>Pies</option>
                <option value="cat" <?= ($currentType === 'cat') ? 'selected' : '' ?>>Kot</option>
                <option value="other" <?= ($currentType === 'other') ? 'selected' : '' ?>>Inne</option>
            </select>

            <button type="submit" class="button" style="background:#8F769E; color:#fff; border:none; padding:0.6vw 1.5vw; border-radius:1vw; cursor:pointer;">Filtruj</button>
            <a href="/vetPanel" style="padding:0.6vw 1vw; text-decoration:none; color:#8F769E; display:flex; align-items:center;">Wyczyść</a>
        </form>

        <div class="pets-list items" style="margin-top: 3vh;">
            <?php if (empty($patients)): ?>
                <div class="card-name empty">Brak przypisanych pacjentów spełniających kryteria.</div>
            <?php else: ?>
                <?php foreach ($patients as $p): ?>
                    <a href="/patientDetails?id=<?= $p['id'] ?>" class="card-welcome entry" style="text-decoration:none;">
                        <div class="feature-icon-box health-box" style="overflow:hidden;">
                            <img src="<?= $p['picture_url'] ? $p['picture_url'] : 'public/img/others/default_pet.png' ?>" style="width:100%; height:100%; object-fit:cover;">
                        </div>
                        <div class="feature-text-container">
                            <span class="feature-text"><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['pet_type']) ?>)</span>
                            <span class="feature-subtext">Właściciel: <?= htmlspecialchars($p['owner_firstname'] . ' ' . $p['owner_lastname']) ?> (<?= htmlspecialchars($p['owner_email']) ?>)</span>
                            <span class="feature-subtext">Czip: <?= htmlspecialchars($p['microchip_number'] ?: 'Brak') ?> | Wiek: <?= htmlspecialchars($p['age_display'] ?? '') ?></span>
                        </div>
                        <span style="font-size: 2em; color: #8F769E;">&gt;</span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>