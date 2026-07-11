<?php require_once '../config/database.php'; ?>

<?php include 'includes/site_top.php'; ?>

<main>
<?php

class MenuDetail
{
    private PDO $pdo;
    private ?int $id;
    private array $menu = [];
    private array $images = [];
    private array $plats = [];
    private array $allergenes = [];

    public function __construct(PDO $pdo, ?int $id)
    {
        $this->pdo = $pdo;
        $this->id = $id;
    }

    public function load(): bool
    {
        if (!$this->id) {
            return false;
        }

        $stmt = $this->pdo->prepare('SELECT titre, description, prix_base FROM menu WHERE id = :id');
        $stmt->execute(['id' => $this->id]);
        $this->menu = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        if (empty($this->menu)) {
            return false;
        }

        $this->loadImages();
        $this->loadPlats();
        $this->loadAllergenes();

        return true;
    }

    private function loadImages(): void
    {
        $stmt = $this->pdo->prepare('SELECT url FROM IMAGE_MENU WHERE id_menu = :id');
        $stmt->execute(['id' => $this->id]);
        $this->images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function loadPlats(): void
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.nom, p.type, p.description
             FROM PLAT p
             JOIN MENU_PLAT mp ON p.id = mp.id_plat
             WHERE mp.id_menu = :id
             ORDER BY p.type'
        );
        $stmt->execute(['id' => $this->id]);
        $this->plats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function loadAllergenes(): void
    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT a.nom
             FROM ALLERGENE a
             JOIN PLAT_ALLERGENE pa ON a.id = pa.id_allergene
             JOIN MENU_PLAT mp ON pa.id_plat = mp.id_plat
             WHERE mp.id_menu = :id'
        );
        $stmt->execute(['id' => $this->id]);
        $this->allergenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function render(): void
    {
        echo '<div class="encadre-menu">';
        echo '<section class="detail-menu">';
        echo '<h1>' . htmlspecialchars($this->menu['titre']) . '</h1>';
        echo '</section>';

        echo '<section class="galerie">';
        foreach ($this->images as $image) {
            echo '<img src="' . htmlspecialchars($image['url']) . '" alt="Photo du menu">';
        }
        echo '</section>';

        echo '<div class="menu">';
        echo '<p>' . htmlspecialchars($this->menu['description']) . '</p>';
        echo '<p>prix: ' . (isset($this->menu['prix_base']) ? htmlspecialchars($this->menu['prix_base']) : 'N/A') . ' €</p>';
        echo '</div>';

        echo '<div class="menu-info">';
        $this->renderPlats();

        echo '<section class="conditions-et-allergenes">';
        echo '<h2> Conditions 14 jours a l\'avance minimum | allergenes: </h2>';
        if (empty($this->allergenes)) {
            echo '<p>Aucun allergène listé.</p>';
        } else {
            foreach ($this->allergenes as $allergene) {
                echo htmlspecialchars($allergene['nom']) . ' ';
            }
        }
        echo '</section>';
        echo '</div>';

        $commanderUrl = 'commander.php?id=' . (int) $this->id;
        echo '<a href="' . htmlspecialchars($commanderUrl, ENT_QUOTES, 'UTF-8') . '" class="btn-commander">Commander</a>';
        echo '</div>';
    }

    private function renderPlats(): void
    {
        echo '<section class="plats-menu">';
        echo '<h2>Les plats du Menu:</h2>';

        if (empty($this->plats)) {
            echo '<p>Aucun plat disponible.</p>';
            echo '</section>';
            return;
        }

        $typeActuel = '';
        foreach ($this->plats as $plat) {
            if ($plat['type'] !== $typeActuel) {
                if ($typeActuel !== '') {
                    echo '</ul>';
                }
                echo '<h3>' . htmlspecialchars(ucfirst($plat['type'])) . '</h3>';
                echo '<ul>';
                $typeActuel = $plat['type'];
            }
            echo '<li>' . htmlspecialchars($plat['nom']) . ' - ' . htmlspecialchars($plat['description']) . '</li>';
        }

        echo '</ul>';
        echo '</section>';
    }
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$detail = new MenuDetail($pdo, $id);

if (!$detail->load()) {
    echo '<p>Menu introuvable.</p>';
    exit;
}

$detail->render();
?>
</main>


<?php include 'includes/site_bottom.php'; ?>