<?php
class SiteTop
{
    private bool $isLoggedIn;

    public function __construct()
    {
        // Démarre la session si nécessaire pour vérifier l'état de connexion de l'utilisateur.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->isLoggedIn = isset($_SESSION['user']);
    }

    public function render(): void
    {
        echo '<!DOCTYPE html>' . PHP_EOL;
        echo '<html lang="fr">' . PHP_EOL;
        echo '<head>' . PHP_EOL;
        echo '    <meta charset="UTF-8">' . PHP_EOL;
        echo '    <meta name="viewport" content="width=device-width, initial-scale=1.0">' . PHP_EOL;
        echo '    <title>Vite Gourmand</title>' . PHP_EOL;
        echo '    <link rel="stylesheet" href="/vite-gourmand/public/css/style.css">' . PHP_EOL;
        echo '    <link rel="stylesheet" href="/vite-gourmand/public/css/index.css">' . PHP_EOL;
        echo '    <link rel="stylesheet" href="/vite-gourmand/public/css/style_menu.css">' . PHP_EOL;
        echo '    <link rel="stylesheet" href="/vite-gourmand/public/css/style_detail_menu.css">' . PHP_EOL;
        echo '    <link rel="stylesheet" href="/vite-gourmand/public/css/style_detail_login.css">' . PHP_EOL;
        echo '    <link rel="stylesheet" href="/vite-gourmand/public/css/commande.css">' . PHP_EOL;
        echo '    <link rel="stylesheet" href="/vite-gourmand/public/css/style_contact.css">' . PHP_EOL;
        echo '    <link rel="stylesheet" href="/vite-gourmand/public/css/css_global.css">' . PHP_EOL;
        echo '</head>' . PHP_EOL;
        echo '<body>' . PHP_EOL . PHP_EOL;
        echo '<header>' . PHP_EOL;
        echo '  <nav>' . PHP_EOL;
        echo '    <div class="logo">' . PHP_EOL;
        echo '      <a href="/vite-gourmand/">Vite & Gourmand</a>' . PHP_EOL;
        echo '    </div>' . PHP_EOL;
        echo '    <ul>' . PHP_EOL;
        echo '      <p><li><a href="/vite-gourmand/">Accueil</a></li>|<li><a href="/vite-gourmand/pages/menu.php">Menus</a></li>';

        // Affiche un menu différent selon que l'utilisateur est connecté et selon son rôle.
        if ($this->isLoggedIn) {
            $role = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : 'user';
            if ($role === 'employee') {
                echo '| <li><a href="/vite-gourmand/pages/espace_employe.php">Mon compte</a></li>' . PHP_EOL;
            } elseif ($role === 'administrateur') {
                echo '| <li><a href="/vite-gourmand/pages/espace_admin.php">Mon compte</a></li>' . PHP_EOL;
            } else {
                echo '| <li><a href="/vite-gourmand/pages/espace_connecte.php">Mon compte</a></li>' . PHP_EOL;
            }

            echo '      <li><a href="/vite-gourmand/pages/deconnexion.php">Déconnexion</a></li>';
        } else {
            // Propose la connexion aux visiteurs non authentifiés.
            echo '| <li><a href="/vite-gourmand/pages/login.php">Connexion</a></li>';
        }

        echo '| <li><a href="/vite-gourmand/pages/contact.php">Contact</a></li>' . PHP_EOL;
        echo '      </li></p>' . PHP_EOL;
        echo '    </ul>' . PHP_EOL;
        echo '  </nav>' . PHP_EOL;
        echo '</header>' . PHP_EOL;
    }
}

$siteTop = new SiteTop();
$siteTop->render();
      
