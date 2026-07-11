<?php
include 'includes/site_top.php'; ?>

<?php
require_once '../config/database.php'; ?>

<?php
class Menu
{
    private $id;
    private $titre;
    private $description;
    private $prixBase;
    private $theme;
    private $regime;
    private $nbPersonnes;

    public function __construct(array $data)
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : 0;
        $this->titre = isset($data['titre']) ? $data['titre'] : '';
        $this->description = isset($data['description']) ? $data['description'] : '';
        $this->prixBase = isset($data['prix_base']) ? (float)$data['prix_base'] : 0.0;
        $this->theme = isset($data['theme']) ? $data['theme'] : '';
        $this->regime = isset($data['regime']) ? $data['regime'] : '';
        $this->nbPersonnes = isset($data['nb_personnes']) ? (int)$data['nb_personnes'] : null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getPrixBase()
    {
        return $this->prixBase;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function getRegime()
    {
        return $this->regime;
    }

    public function getNbPersonnes()
    {
        return $this->nbPersonnes;
    }
}

class MenuRepository
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll()
    {
        $requete = $this->pdo->query("SELECT * FROM menu");
        $rows = $requete->fetchAll(PDO::FETCH_ASSOC);
        $menus = [];

        foreach ($rows as $row) {
            $menus[] = new Menu($row);
        }

        return $menus;
    }
} 



class MenuRenderer
{
    public static function renderMenuCard(Menu $menu)
    {
        $titre = htmlspecialchars($menu->getTitre());
        $description = htmlspecialchars($menu->getDescription());
        $prix = htmlspecialchars($menu->getPrixBase());
        $theme = htmlspecialchars($menu->getTheme());
        $regime = htmlspecialchars($menu->getRegime());
        $personnes = $menu->getNbPersonnes();
        $personnesData = $personnes !== null ? htmlspecialchars((string)$personnes) : '';



        $html = '';
        $html .= "<div class='carte-menu' data-prix='" . $prix . "' data-theme='" . $theme . "' data-regime='" . $regime . "' data-personnes='" . $personnesData . "'>";
        $html .= "<h2>{$titre}</h2>";
        $html .= "<p>{$description}</p>";
        $html .= "<p>{$prix} €/personne</p>";

        if ($theme !== '') {
            $html .= "<p>Thème : {$theme}</p>";
        }

        if ($regime !== '') {
            $html .= "<p>Régime : {$regime}</p>";
        }

        if ($personnes !== null) {
            $html .= "<p>Personnes : {$personnes}</p>";
        }

        $html .= '<a href="detail_menu.php?id=' . $menu->getId() . '">Voir le menu</a>';
        $html .= "</div>";

        return $html;
    }
} ?>

<?php
$repository = new MenuRepository($pdo);
$menus = $repository->findAll();
?>


    <main>
        <section class="container filter">
            <h1>Filtres :</h1>
            <div class="filter-row">
                <label>Prix minimum <input type="number" id="prix_min" placeholder="0" min="0"></label>
            <label>Prix maximum <input type="number" id="prix_max" placeholder="999" min="0"></label>
        </div>
        <div class="filter-row">
            <label>Thème <input type="text" id="theme" placeholder="Thème"></label>
            <label>Régime <input type="text" id="regime" placeholder="Régime"></label>
            <label>Nb de personnes min <input type="number" id="nb_personnes_min" placeholder="0" min="0"></label>
        </div>
    </section>

    <section class="menus-container" id="menus-container">
        <?php
//genere le HTML de chaque menu suite au filtrage de l'utilsateur
        foreach ($menus as $menu) {
//construit la carte HTML a partir des informations d'un menu
            echo MenuRenderer::renderMenuCard($menu);
        }
        ?>
    </section>
</main>

//filtres les menus selon les criteres de l'utilisateur
<script>
(function() {
    var prixMinInput = document.getElementById('prix_min');
    var prixMaxInput = document.getElementById('prix_max');
    var themeInput = document.getElementById('theme');
    var regimeInput = document.getElementById('regime');
    var personnesInput = document.getElementById('nb_personnes_min');
    var menusContainer = document.getElementById('menus-container');
    var menuCards = menusContainer ? Array.prototype.slice.call(menusContainer.querySelectorAll('.carte-menu')) : [];

    function normalize(text) {
        return text ? text.toString().trim().toLowerCase() : '';
    }

    function filterMenus() {
        var prixMin = parseFloat(prixMinInput.value) || 0;
        var prixMax = prixMaxInput.value !== '' ? parseFloat(prixMaxInput.value) : null;
        var theme = normalize(themeInput.value);
        var regime = normalize(regimeInput.value);
        var personnes = personnesInput.value !== '' ? parseInt(personnesInput.value, 10) : null;

        menuCards.forEach(function(card) {
            var cardPrix = parseFloat(card.dataset.prix) || 0;
            var cardTheme = normalize(card.dataset.theme);
            var cardRegime = normalize(card.dataset.regime);
            var cardPersonnes = card.dataset.personnes ? parseInt(card.dataset.personnes, 10) : null;
            var visible = true;

            if (prixMin && cardPrix < prixMin) {
                visible = false;
            }
            if (prixMax !== null && cardPrix > prixMax) {
                visible = false;
            }
            if (theme && cardTheme.indexOf(theme) === -1) {
                visible = false;
            }
            if (regime && cardRegime.indexOf(regime) === -1) {
                visible = false;
            }
            if (personnes !== null && cardPersonnes !== null && cardPersonnes < personnes) {
                visible = false;
            }

            card.style.display = visible ? '' : 'none';
        });
    }

    [prixMinInput, prixMaxInput, themeInput, regimeInput, personnesInput].forEach(function(input) {
        if (input) {
            input.addEventListener('input', filterMenus);
        }
    });

    filterMenus();
})();
</script>

<?php include 'includes/site_bottom.php'; ?>