<?php 
// Initialize database on first load
require_once 'init-db.php';
?>

<?php include 'pages/includes/site_top.php'; ?>

<?php require_once 'config/database.php'; ?>

<?php
class AvisPage
{
    private ?PDO $pdo;
    private array $avisList = [];

    public function __construct(?PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->loadAvis();
    }

    private function loadAvis(): void
    {
        if (!$this->pdo instanceof PDO) {
            return;
        }

        try {
            $stmt = $this->pdo->prepare("SELECT commentaire FROM avis WHERE valide = :valide");
            $stmt->execute(['valide' => 1]);
            $this->avisList = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des avis: " . $e->getMessage());
        }
    }

    public function renderAvis(): void
    {
        echo '<section class="avis-client">' . PHP_EOL;
        echo '    <div class="avis-container">' . PHP_EOL;

        if (!empty($this->avisList)) {
            foreach ($this->avisList as $avis) {
                echo '        <div class="avis">';
                echo '            <p>' . htmlspecialchars($avis, ENT_QUOTES, 'UTF-8') . '</p>' . PHP_EOL;
                echo '        </div>';
            }
        } else {
            echo '        <p>Aucun avis disponible pour le moment.</p>' . PHP_EOL;
        }

        echo '    </div>' . PHP_EOL;
        echo '</section>' . PHP_EOL;
    }
}

$avisPage = new AvisPage($pdo);
?>

<main>
    <!-- Contenu principal de la page -->
    <section class="presentation">
        <h1>Bienvenue sur Vite Gourmand</h1>
        <p> Sur Bordeaux et sa region depuis plus de 25ans, nous proposons des menus divers et varies pour tout types d'evenement.</p>
        <p> Bonne decouverte et bon appetit</p>
    </section>

    <!--Section mise en avant de l'entreprise-->
    <section class="professionalisme">
        <h4> Depuis deja deux decennies, Julie et Jose s'appliquent a vous servir des buffets de qualite dans un delai raisonnable et pour petit ou grand comite a 
    des prix attractifs. Ayant demarre leur activite avec les buffets du reveillon de Noel et le week-end de Paques, le succes et le bouche a oreille ont
    pousse Julie et Jose a agrandir leur offre et propose desormais d'autres buffet a themes tel que le buffet d'entreprise ou encore le vegetarien </h4>
    </section>

    <!-- Section avis client -->
    <?php $avisPage->renderAvis(); ?>
</main>

<?php include 'pages/includes/site_bottom.php'; ?>

