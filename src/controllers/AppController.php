<?php

class AppController {

    // metoda sprawdzająca, czy żądanie to GET
        protected function isGet(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === 'GET';
    }

    // metoda sprawdzająca, czy żądanie to POST (pod bazę danych i sprawdzanie błędów)
    protected function isPost(): bool {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    }

    protected function render(string $template = null, array $variables = [])
    {
        if ($template === null) {
            return;
        }

        $templatePath = 'public/views/' . $template . '.html';
        $templatePath404 = 'public/views/404.html';

        if (!empty($variables)) {
            extract($variables); 
        }

        ob_start();

        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            include $templatePath404;
        }

        $output = ob_get_clean();

        echo $output;
    }
}