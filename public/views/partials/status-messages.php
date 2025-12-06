<?php 
// wiadomość sukcesu
if (isset($_GET['registered']) && $_GET['registered'] === 'true') : ?>
    <p class="success-messages">Account has been successfully created!</p>
<?php endif; 

// wiadomość błędu
if (isset($messages)) : ?>
    <p class="error-messages"><?= $messages ?></p>
<?php endif; ?>