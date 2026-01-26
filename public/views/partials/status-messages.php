<?php 
 // Wiadomość sukcesu
if (isset($_GET['registered']) && $_GET['registered'] === 'true') : ?>
    <p class="success-messages">Account has been successfully created!</p>
<?php endif; ?>

<?php 
// Wiadomość błędu
if (isset($messages)) : ?>
    <p class="error-messages"><?= htmlspecialchars($messages) ?></p>
<?php endif; ?>