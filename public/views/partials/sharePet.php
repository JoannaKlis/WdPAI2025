<?php include '/app/public/views/partials/header.php'; ?>
<link rel="stylesheet" type="text/css" href="public/styles/pages/welcome.css">
<link rel="stylesheet" type="text/css" href="public/styles/components/items.css">
<link rel="stylesheet" type="text/css" href="public/styles/pages/editPet.css">
<title>Udostępnij dane weterynarzowi</title>
</head>
<body>
    <div class="wrapper">
        <div class="top-bar">
            <a href="/features?id=<?= $pet['id'] ?>" class="back-arrow">&lt;</a> 
            <h1>Udostępnij dane</h1>
        </div>

        <div style="margin-top: 15vh; display:flex; flex-direction:column; align-items:center;">
            <p style="color:#8F769E; font-size:1.2em; text-align:center;">
                Wybierz weterynarza, któremu chcesz przekazać pełną kartę pacjenta <b><?= htmlspecialchars($pet['name']) ?></b>:
            </p>

            <form method="POST" action="/sharePet?id=<?= $pet['id'] ?>" style="display:flex; flex-direction:column; gap:2vh; margin-top:2vh;">
                <select name="vet_id" required style="padding:1vw 2vw; border-radius:1vw; border:1px solid #934CBE; font-size:1.1em; font-family:'Jua';">
                    <option value="">-- Wybierz klinikę / lekarza --</option>
                    <?php foreach ($vets as $vet): ?>
                        <option value="<?= $vet['id'] ?>">
                            Lek. <?= htmlspecialchars($vet['firstname'] . ' ' . $vet['lastname']) ?> (<?= htmlspecialchars($vet['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="button" style="background:#934CBE; color:white; border:none; padding:0.8vw 2vw; border-radius:1vw; cursor:pointer; font-size:1.1em;">
                    Wyślij kartę do bazy weterynarza
                </button>
            </form>
        </div>
    </div>
</body>
</html>