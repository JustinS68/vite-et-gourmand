<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

class ModifierMenu
{
    private PDO $pdo;
    private int $menuId;

    public function __construct(PDO $pdo, int $menuId)
    {
        $this->pdo = $pdo;
        $this->menuId = $menuId;
    }

    public function getMenu(): array 
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, titre, description, prix_base, stock, theme, regime, nb_personnes_min
            FROM menu WHERE id = :id'
        );
        $stmt->execute([':id' => $this->menuId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function modifier(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE menu SET
                titre = :titre,
                description = :description,
                prix_base = :prix_base,
                stock = :stock,
                theme = :theme,
                regime = :regime,
                nb_personnes_min = :nb_personnes_min WHERE id = :id'
        );
        try {
            return $stmt->execute([
                ':titre' => $data['titre'],
                ':description' => $data['description'],
                ':prix_base' => $data['prix_base'],
                ':stock' => $data['stock'],
                ':theme' => $data['theme'],
                ':regime' => $data['regime'],
                ':nb_personnes_min' => $data['nb_personnes_min'],
                ':id' => $this->menuId
            ]);
        } catch (PDOException $e) {
            error_log('Erreur de modification du menu: ' . $e->getMessage());
            return false;
        }
    }
}

class ModifierMenuPage
{
    private ModifierMenu $modifierMenu;
    private ?string $message = null;

    public function __construct(PDO $pdo, int $menuId)
    {
        $this->modifierMenu = new ModifierMenu($pdo, $menuId);
    }

    public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $data = [
            'titre' => trim($_POST['titre'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'prix_base' => (float) ($_POST['prix_base'] ?? 0),
            'stock' => (int) ($_POST['stock'] ?? 0),
            'theme' => trim($_POST['theme'] ?? ''),
            'regime' => trim($_POST['regime'] ?? ''),
            'nb_personnes_min' => (int) ($_POST['nb_personnes_min'] ?? 0)
        ];

        if (!$data['titre'] || !$data['description']) {
            $this->message = 'Titre et description du menu obligatoires';
            return;
        }

        $ok = $this->modifierMenu->modifier($data);
        $this->message = $ok ? 'Modifie avec succes' : 'Erreur, la modification a echoue';
    }

    public function render(): void 
    {
        $menu = $this->modifierMenu->getMenu   ();

        if (empty($menu)) {
            echo '<p>Menu introuvable</p>';
            return;
        }
    

        include 'includes/site_top.php';
        ?>

<main>
    <h1>Modifier le menu</h1>

    <?php if ($this->message): ?>
        <p style="color:green"><?= htmlspecialchars($this->message) ?></p>
    <?php endif; ?>
    

    <form method="POST">
        <label>Titre</label>
        <input type="text" name="titre" value="<?= htmlspecialchars($menu['titre']) ?>" required> 

        <label>Description</label>
        <textarea name="description" required><?= htmlspecialchars($menu['description']) ?></textarea>

        <label>Prix de base (€)</label>
        <input type="number" step="1" name="prix_base" value="<?= $menu['prix_base'] ?>" required>

        <label>Stock</label>
        <input type="number" name="stock" value="<?= $menu['stock'] ?>" required>

        <label>Theme</label>
        <select name="theme">
            <option value="noel" <?= $menu['theme'] === 'noel' ? 'selected' : '' ?>>Noel</option>
            <option value="paques" <?= $menu['theme'] === 'paques' ? 'selected' : '' ?>>paques</option>
            <option value="classique" <?= $menu['theme'] === 'classique' ? 'selected' : '' ?>>classique</option>
            <option value="evenement" <?= $menu['theme'] === 'evenement' ? 'selected' : '' ?>>evenement</option>
            <option value="romantique" <?= $menu['theme'] === 'romantique' ? 'selected' : '' ?>>romantique</option>
            <option value="vegetarien" <?= $menu['theme'] === 'vegetarien' ? 'selected' : '' ?>>vegetarien</option>
        </select>

        <label>Regime</label>
        <select name="regime">
            <option value="classique" <?= $menu['regime'] === 'classique' ? 'selected' : '' ?>>Classique</option>
            <option value="vegetarien" <?= $menu['regime'] === 'vegetarien' ? 'selected' : '' ?>>vegetarien</option>
            <option value="vegan" <?= $menu['regime'] === 'vegan' ? 'selected' : '' ?>>vegan</option>
        </select>

        <label>Nombres de personnes minimum</label>
        <input type="number" name="nb_personnes_min" value="<?= $menu['nb_personnes_min'] ?>" required>

        <button type="submit">Enregistrer les modifications</button>
        <a href="<?php
        $back = 'menu.php'; 
        if (isset($_SESSION['role'])) {
            if ($_SESSION['role'] === 'administrateur') {
                $back = 'espace_admin.php';
            } elseif ($_SESSION['role'] === 'employe') {
                $back = 'espace_employe.php';
            }
        }
        echo $back;
    ?>">Retour</a>
    </form>
</main>
<?php include 'includes/site_bottom.php' ;
    }
}
    



if (!isset($_SESSION['user']) ||
        !in_array($_SESSION['user']['role'], ['administrateur', 'employe'])) {
        header('Location: login.php');
        exit;
    }

$menuId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$menuId) {
    header('Location: administrateur.php');
    exit;
}

$page = new ModifierMenuPage($pdo, $menuId);
$page->handleRequest();
$page->render(); 




    
