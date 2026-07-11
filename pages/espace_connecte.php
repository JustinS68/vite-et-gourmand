<?php
declare(strict_types=1);

require_once '../config/database.php';
include 'includes/site_top.php';

class UserAccount
{
    private PDO $pdo;
    private int $userId;

    public function __construct(PDO $pdo, int $userId)
    {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function getInfos(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT nom, prenom, email, telephone, adresse
            FROM utilisateur WHERE id = :id'
        );
        $stmt->execute([':id' => $this->userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function updateInfos(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE utilisateur
            SET nom = :nom, prenom = :prenom,
            telephone = :telephone, adresse = :adresse
            WHERE id = :id'
        );

        try {
            return $stmt->execute([
                ':nom' => $data['nom'],
                ':prenom' => $data['prenom'],
                ':telephone' => $data['telephone'],
                ':adresse' => $data['adresse'],
                ':id' => $this->userId,
            ]);
        } catch (PDOException $e) {
            error_log('Erreur update : ' . $e->getMessage());

            return false;
        }
    }
}

class UserCommandes
{
    private PDO $pdo;
    private int $userId;

    public function __construct(PDO $pdo, int $userId)
    {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function getCommandes(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.statut, c.date_prestation, m.titre as menu
            FROM commande c
            JOIN menu m ON c.id_menu = m.id
            WHERE c.id_utilisateur = :id
            ORDER BY c.date_prestation DESC'
        );
        $stmt->execute([':id' => $this->userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function annulerCommande(int $commandeId): bool
    {
        // Vérifie que la commande appartient à l'utilisateur et n'est pas acceptée
        $stmt = $this->pdo->prepare(
            'SELECT statut FROM commande
            WHERE id = :id AND id_utilisateur = :userId'
        );
        $stmt->execute([':id' => $commandeId, ':userId' => $this->userId]);
        $commande = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$commande || $commande['statut'] === 'accepte') {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE commande SET statut = :statut WHERE id = :id'
        );

        return $stmt->execute([
            ':statut' => 'annulee',
            ':id' => $commandeId,
        ]);
    }

    public function getSuivi(int $commandeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT statut, date_heure
            FROM suivi_commande
            WHERE id_commande = :id
            ORDER BY date_heure ASC'
        );
        $stmt->execute([':id' => $commandeId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class MonComptePage
{
    private UserAccount $account;
    private UserCommandes $commandes;
    private int $userId;
    private ?string $message = null;

    public function __construct(PDO $pdo, int $userId)
    {
        $this->userId = $userId;
        $this->account = new UserAccount($pdo, $userId);
        $this->commandes = new UserCommandes($pdo, $userId);
    }

    public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['update_infos'])) {
                $ok = $this->account->updateInfos($_POST);
                $this->message = $ok ? 'Informations mises à jour !' : 'Erreur lors de la mise à jour.';
            }

            if (isset($_POST['annuler_commande'])) {
                $commandeId = (int) ($_POST['commande_id'] ?? 0);
                $ok = $this->commandes->annulerCommande($commandeId);
                $this->message = $ok ? 'Commande annulée.' : 'Impossible d\'annuler cette commande.';
            }
        }
    }

    public function render(): void
    {
        $infos = $this->account->getInfos();
        $commandes = $this->commandes->getCommandes();
        ?>
        <main>
            <h1>Mon compte</h1>

            <?php if ($this->message): ?>
                <p style="color:green"><?= htmlspecialchars($this->message) ?></p>
            <?php endif; ?>

            <section class="infos-personnelles">
                <h2>Mes informations</h2>
                <form method="POST">
                    <input type="text" name="nom" value="<?= htmlspecialchars($infos['nom']) ?>" required>
                    <input type="text" name="prenom" value="<?= htmlspecialchars($infos['prenom']) ?>" required>
                    <input type="text" name="telephone" value="<?= htmlspecialchars($infos['telephone']) ?>" required>
                    <input type="text" name="adresse" value="<?= htmlspecialchars($infos['adresse']) ?>" required>
                    <button type="submit" name="update_infos">Mettre à jour</button>
                </form>
            </section>

            <section class="mes-commandes">
                <h2>Mes commandes</h2>
                <?php if (empty($commandes)): ?>
                    <p>Aucune commande pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($commandes as $commande): ?>
                        <div class="commande">
                            <p><strong><?= htmlspecialchars($commande['menu']) ?></strong></p>
                            <p>Statut : <?= htmlspecialchars($commande['statut']) ?></p>
                            <p>Date : <?= htmlspecialchars($commande['date_prestation']) ?></p>

                            <?php if ($commande['statut'] !== 'accepte'): ?>
                                <form method="POST">
                                    <input type="hidden" name="commande_id" value="<?= $commande['id'] ?>">
                                    <button type="submit" name="annuler_commande">Annuler</button>
                                </form>
                            <?php endif; ?>

                            <?php if ($commande['statut'] === 'accepte'): ?>
                                <h3>Suivi :</h3>
                                <?php $suivi = $this->commandes->getSuivi($commande['id']); ?>
                                <?php foreach ($suivi as $etape): ?>
                                    <p><?= htmlspecialchars($etape['statut']) ?>
                                    — <?= htmlspecialchars($etape['date_heure']) ?></p>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </main>
        <?php
        include 'includes/site_bottom.php';
    }
}

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['user']['id'];
$page = new MonComptePage($pdo, $userId);
$page->handleRequest();
$page->render();

