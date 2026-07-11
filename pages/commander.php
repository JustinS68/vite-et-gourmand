<?php declare(strict_types=1); ?>

<?php include '../pages/includes/site_top.php'; ?>
<?php


require_once '../config/database.php';

//rendre la page de commande intuitive pour l'utilisateur
class CommanderPage
{
    private string $title = '';
    private ?int $menuId;
    private PDO $pdo;

    public function __construct(PDO $pdo, ?int $menuId)
    {
        $this->pdo = $pdo;
        $this->menuId = $menuId;
    }

    public function render(): void
{
        $prixBase = $this->getPrixBase();
        $nbPersonnesMinimum = $this->getNbPersonnesMinimum();
        ?>
        <main>
            <section class="title">
            <?php if ($this->menuId !== null): ?>
                <h1>Menu sélectionné : <?php echo htmlspecialchars($this->getMenuTitle(), ENT_QUOTES, 'UTF-8'); ?></h1>
            <?php endif; ?>
            <h1><?php echo htmlspecialchars($this->title, ENT_QUOTES, 'UTF-8'); ?></h1>
            </section>
            <section class="container">
                <h1>Commander votre menu</h1>
                <div class="filter-row">
                    <label>
                        <input placeholder="Nombre de personnes" type="number" value="<?php echo $nbPersonnesMinimum; ?>" id="nbre_personnes_min" placeholder="<?php echo $nbPersonnesMinimum; ?>" min="<?php echo $nbPersonnesMinimum; ?>" max="999" value="0">
                    </label>

                    <label>
                        <input type="text" id="adresse" placeholder="Numéro et rue">
                        <input type="text" id="ville" placeholder="Ville">
                        <input type="text" id="code_postal" placeholder="Code postal">
                        <input type="number" id="distance_km" placeholder="Distance en km" min="0" step="0.1" value="0">
                    </label>

                    <label>
                        <input type="text" id="prix_total" placeholder="0" readonly>
                    </label>

                    <script>
                        const prixBase = <?php echo $prixBase; ?>;
                        const nbrePersonnesInput = document.getElementById('nbre_personnes_min');
                        const villeInput = document.getElementById('ville');
                        const distanceKmInput = document.getElementById('distance_km');
                        const prixTotalInput = document.getElementById('prix_total');

                        const nbPersonnesMinimum = <?php echo $nbPersonnesMinimum; ?>;
                        const deliveryFeeBase = 5;
                        const deliveryFeePerKm = 0.59;

                        function calculerPrix() {
                            const nbrePersonnes = parseInt(nbrePersonnesInput.value, 10) || 0;
                            const ville = (villeInput.value || '').trim().toLowerCase();
                            const distanceKm = parseFloat(distanceKmInput.value) || 0;

                            let total = prixBase * nbrePersonnes;

                            if (nbrePersonnes >= nbPersonnesMinimum) {
                                total *= 0.90;
                            }

                            let livraison = deliveryFeeBase;

                            if (ville !== '' && !['bordeaux', 'bordelais'].includes(ville)) {
                                livraison += deliveryFeePerKm * distanceKm;
                            }

                            total += livraison;
                            prixTotalInput.value = total.toFixed(2) + ' €';
                        }

                        [nbrePersonnesInput, villeInput, distanceKmInput].forEach((input) => {
                            input.addEventListener('input', calculerPrix);
                        });

                        calculerPrix();
                    </script>
                </div>
            </section>
                          <div class="bouton-commander">
                              <a id="commander-btn" class="commander-link" href="../pages/payment.php"><h1>Commander</h1></a>
                          </div>
            
        </main>
        <?php
    }

//afficher le prix de maniere dynamique pour ameliorer l'experience de l'utilisateur
    private function getPrixBase(): float
    {
        if ($this->menuId === null) {
            return 0.0;
        }

        $stmt = $this->pdo->prepare('SELECT prix_base FROM menu WHERE id = :id');
        $stmt->execute([':id' => $this->menuId]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        return isset($menu['prix_base']) ? (float) $menu['prix_base'] : 0.0;
    }

    private function getNbPersonnesMinimum(): int
    {
        if ($this->menuId === null) {
            return getNbPersonnesMinimum  ();
        }

        $stmt = $this->pdo->prepare('SELECT nb_personnes_min FROM menu WHERE id = :id');
        $stmt->execute([':id' => $this->menuId]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        return isset($menu['nb_personnes_min']) ? (int) $menu['nb_personnes_min'] : $this->nbPersonnesMinimum;
    }

    private function getMenuTitle(): string
    {
        if ($this->menuId === null) {
            return 'Aucun menu sélectionné';
        }

        $stmt = $this->pdo->prepare('SELECT titre FROM menu WHERE id = :id');
        $stmt->execute([':id' => $this->menuId]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        return $menu['titre'] ?? 'Menu introuvable';
    }
}

$menuId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$page = new CommanderPage($pdo, $menuId);
$page->render();
?>


<?php include '../pages/includes/site_bottom.php'; ?>
