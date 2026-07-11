<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

class EmployeCommandes
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getCommandes(string $filtreStatut = '', string $filtreClient = ''): array
    {
        $sql = 'SELECT c.id, c.statut, c.date_prestation, c.adresse_prestation, m.titre AS menu,
                       u.nom, u.prenom, u.email, u.telephone
                FROM commande c
                JOIN menu m ON c.id_menu = m.id
                JOIN utilisateur u ON c.id_utilisateur = u.id
                WHERE 1=1';

        $params = [];

        if ($filtreStatut !== '') {
            $sql .= ' AND c.statut = :statut';
            $params[':statut'] = $filtreStatut;
        }

        if ($filtreClient !== '') {
            $sql .= ' AND (u.nom LIKE :client OR u.prenom LIKE :client OR u.email LIKE :client)';
            $params[':client'] = '%' . $filtreClient . '%';
        }

        $sql .= ' ORDER BY c.date_prestation DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function changerStatut(int $commandeId, string $statut, int $employeId, string $motif = '', string $modeContact = ''): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO SUIVI_COMMANDE (id_commande, statut, id_employe, motif_annulation, mode_contact)
            VALUES (:id_commande, :statut, :id_employe, :motif_annulation, :mode_contact)'
        );

        try {
            $stmt->execute([
                ':id_commande' => $commandeId,
                ':statut' => $statut,
                ':id_employe' => $employeId,
                ':motif_annulation' => $motif,
                ':mode_contact' => $modeContact
            ]);

            $stmt2 = $this->pdo->prepare('UPDATE commande SET statut = :statut WHERE id = :id');
            return $stmt2->execute([':statut' => $statut, ':id' => $commandeId]);
        } catch (PDOException $e) {
            error_log('Erreur statut : ' . $e->getMessage());
            return false;
        }
    }
}
    
class EmployeAvis
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAvisEnAttente(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.id, a.note, a.commentaire, u.nom, u.prenom
            FROM avis a
            JOIN utilisateur u ON a.id_utilisateur = u.id
            WHERE a.valide = 0'
        );
        $stmt->execute();
        return $stmt->fetchALL(PDO::FETCH_ASSOC);
    }

    public function validerAvis(int $avisId, bool $valide): bool
    {
        $stmt = $this->pdo->prepare('UPDATE avis SET valide = :valide WHERE id = :id');
        return $stmt->execute([':valide' => $valide ? 1 : 0, ':id' => $avisId]);
    }
}

class EmployeMenus
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getMenus(): array
    {
        $stmt = $this->pdo->prepare('SELECT id, titre, prix_base, stock FROM menu');
        $stmt->execute();
        return $stmt->fetchALL(PDO::FETCH_ASSOC);
    }

    public function supprimerMenu(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM menu WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}

class EspaceEmployePage
{
    private EmployeCommandes $commandes;
    private EmployeAvis $avis;
    private EmployeMenus $menus;
    private int $employeId;
    private ?string $message = null;

    public function __construct(PDO $pdo, int $employeId)
    {
        $this->employeId = $employeId;
        $this->commandes = new EmployeCommandes($pdo);
        $this->avis = new EmployeAvis($pdo);
        $this->menus = new EmployeMenus($pdo);
    }

    public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        if (isset($_POST['changer_statut'])) {
            $commandeId = (int) ($_POST['commande_id'] ?? 0);
            $statut = $_POST['statut'] ?? '';
            $motif = $_POST['motif'] ?? '';
            $modeContact = $_POST['mode_contact'] ?? '';

            if ($statut === 'annulee' && (empty($motif) || empty($modeContact))) {
                $this->message = "Motif d'annulation et mode de contact obligatoires pour annuler.";
                return;
            }

            $ok = $this->commandes->changerStatut($commandeId, $statut, $this->employeId, $motif, $modeContact);
            $this->message = $ok ? 'Statut mis a jour !' : 'Erreur lors de la mise a jour.';
        }

        if (isset($_POST['valider_avis'])) {
            $avisId = (int) ($_POST['avis_id'] ?? 0);
            $valide = isset($_POST['approuver']);
            $ok = $this->avis->validerAvis($avisId, $valide);
            $this->message = $ok ? 'Avis traite!' : 'Erreur.';
        }

        if (isset($_POST['supprimer_menu'])) {
            $menuId = (int) ($_POST['menu_id'] ?? 0);
            $ok = $this->menus->supprimerMenu($menuId);
            $this->message = $ok ? 'Menu supprime !' : 'Erreur.' ;
        }
    }

    public function render(): void
    {
        if ($this->message) {
            echo '<p class="message">' . htmlspecialchars($this->message) . '</p>';
        }

        include 'includes/site_top.php';

        $filtreStatut = $_GET['statut'] ?? '';
        $filtreClient = $_GET['client'] ?? '';
        $commandes = $this->commandes->getCommandes($filtreStatut, $filtreClient);
        $avisEnAttente = $this->avis->getAvisEnAttente();
        $menus = $this->menus->getMenus();
        ?>
        <main>
            <section class="filtres">
                <h2>Choix des commandes</h2>
                <form method="GET">
                    <select name="statut">
                        <option value="">Tous les statuts</option>
                        <option value="accepte">Accepte</option>
                        <option value="en preparation">En preparation</option>
                        <option value="en cours de livraison">En cours de livraison</option>
                        <option value="livre">Livre</option>
                        <option value="en attente retour materiel">En attente de retour du materiel</option>
                        <option value="terminee">Terminee</option>
                        <option value="annulee">Annulee</option>
                    </select>
                    <input type="text" name="client" placeholder="Nom ou email client" value="<?= htmlspecialchars($filtreClient) ?>">
                    <button type="submit">Filtrer</button>
                </form>
            </section>

            <section class="commande-employe">
                <h2>Commandes</h2>
                <?php foreach ($commandes as $commande): ?>
                    <div class="commande">
                        <p><strong><?= htmlspecialchars($commande['menu']) ?></strong></p>
                        <p>Client : <?= htmlspecialchars($commande['nom']) ?> <?= htmlspecialchars($commande['prenom']) ?> </p>
                        <p>Statut : <?= htmlspecialchars($commande['statut']) ?></p>
                        <p>Date : <?= htmlspecialchars($commande['date_prestation']) ?></p>

                        <form method="POST">
                            <input type="hidden" name="commande_id" value="<?= $commande['id'] ?>">
                            <select name="statut">
                                <option value="accepte">Accepte</option>
                                <option value="en preparation">En preparation</option>
                                <option value="en cours de livraison">En cours de livraison</option>
                                <option value="livre">Livre</option>
                                <option value="en attente retour materiel">En attente de retour du materiel</option>
                                <option value="terminee">Terminee</option>
                                <option value="annulee">Annulee</option>
                            </select>
                            <input type="text" name="mode_contact" placeholder="mode de contact (si annulation)">
                            <textarea name="motif" placeholder="motif (si annulation)"></textarea>
                            <button type="submit" name="changer_statut">Mettre a jour</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </section>

            <section class="avis-employe">
                <h2>avis a valider</h2>
                <?php if (empty($avisEnAttente)): ?>
                    <p>Aucun avis en attente</p>
                <?php else: ?>
                    <?php foreach ($avisEnAttente as $avis): ?>
                        <div class="avis">
                            <p><?= htmlspecialchars($avis['nom']) ?> - Note : <?= $avis['note'] ?> /5</p>
                            <p><?= htmlspecialchars($avis['commentaire']) ?></p>
                            <form method="POST">
                                <input type="hidden" name="avis_id" value="<?= $avis['id'] ?>">
                                <input type="hidden" name="approuver" value="0">
                                <button type="submit" name="valider_avis" value="1" onclick="this.form.approuver.value=1">Approuver</button>
                                <button type="submit" name="valider_avis" value="1" onclick="this.form.approuver.value=0">Refuser</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <section class="menus-employe">
                <h2>Gestion des menus</h2>
                <?php foreach ($menus as $menu): ?>
                    <div class="menu">
                        <p><strong><?= htmlspecialchars($menu['titre']) ?></strong> - <?= $menu['prix_base'] ?>€</p>
                        <p>Stock : <?= $menu['stock'] ?></p>
                        <form method="POST">
                            <input type="hidden" name="menu_id" value="<?= $menu['id'] ?>">
                            <button type="submit" name="supprimer_menu">Supprimer le menu</button>
                        </form>
                        <a href="modifier_menu.php?id=<?= $menu['id'] ?>">Modifier le menu</a>
                    </div>
                <?php endforeach; ?>
            </section>
        </main>
        <?php
        include 'includes/site_bottom.php';
    }
}

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['employe', 'administrateur'])) {
    header('Location: login.php');
    exit;
}

$employeId = (int) $_SESSION['user']['id'];
$page = new EspaceEmployePage($pdo, $employeId);
$page->handleRequest();
$page->render();
