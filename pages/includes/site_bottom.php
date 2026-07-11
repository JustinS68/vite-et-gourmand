<?php

class SiteBottom
{
    private string $hours = 'Lundi au Dimanche 8h-13h et 14h-19h30';
    private string $basePath = '/vite-gourmand/pages';

    public function render(): void
    {
        echo '<footer>';
        echo '<div class="footer-content">';
        echo '<p>Horaires : ' . $this->hours . ' | <a href="' . $this->getLegalUrl() . '">Mentions légales | CGV</a></p>';
        echo '</div>';
        echo '</footer>';
        echo '</body>';
        echo '</html>';
    }

    private function getLegalUrl(): string
    {
        return $this->basePath . '/mentions_legales_CGV.php';
    }
}

$siteBottom = new SiteBottom();
$siteBottom->render();
