<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

class AdministrateurCommandes
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

    public function changerStatut(int $commandeId, string $statut, int $administrateurId, string $motif = '', string $modeContact = ''): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO SUIVI_COMMANDE (id_commande, statut, id_administrateur, motif_annulation, mode_contact)
            VALUES (:id_commande, :statut, :id_administrateur, :motif_annulation, :mode_contact)'
        );

        try {
            $stmt->execute([
                ':id_commande' => $commandeId,
                ':statut' => $statut,
                ':id_administrateur' => $administrateurId,
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
    
class AdministrateurAvis
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

class AdministrateurMenus
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

public function creerMenu(string $titre, float $prixBase, int $stock): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO menu (titre, prix_base, stock)
            VALUES (:titre, :prix_base, :stock)'
        );

        return $stmt->execute([
            ':titre' => $titre,
            ':prix_base' => $prixBase,
            ':stock' => $stock,
        ]);
    }
}

class GestionEmployes
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getEmployes(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nom, prenom, email, actif
            FROM utilisateur WHERE role = :role'
        );
        $stmt->execute([':role'=>'employe']);
        return $stmt->fetchALL(PDO::FETCH_ASSOC);
    }

    public function creerEmploye(string $nom, string $prenom, string $email, string $motDePasse): bool 
    {
        $hash = password_hash($motDePasse, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            'INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role, actif)
            VALUES (:nom, :prenom, :email, :mot_de_passe, :role, 1)'
        );
        try {
            return $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':mote_de_passe' => $hash,
                ':role' => 'employe'
            ]);
        }   catch (PDOException $e) {
            error_log('Erreur creation employe : ' . $e->getMessage());
            return false;
        }
    }

    public function desactiverEmploye(int $id): bool 
{
    $stmt = $this->pdo->prepare(
        'UPDATE utilisateur SET actif = 0 WHERE id = :id AND role = :role'
    );
    return $stmt->execute([':id' => $id, ':role' => 'employe']);
}

    public function activerEmploye(int $id): bool 
{
    $stmt = $this->pdo->prepare(
        'UPDATE utilisateur SET actif = 1 WHERE id = :id AND role = :role'
    );
    return $stmt->execute([':id' => $id, ':role' => 'employe']);
}
}


class EspaceAdministrateurPage
{
    private AdministrateurCommandes $commandes;
    private AdministrateurAvis $avis;
    private AdministrateurMenus $menus;
    private GestionEmployes $employes;
    private int $administrateurId;
    private ?string $message = null;

    public function __construct(PDO $pdo, int $administrateurId)
    {
        $this->administrateurId = $administrateurId;
        $this->commandes = new AdministrateurCommandes($pdo);
        $this->avis = new AdministrateurAvis($pdo);
        $this->menus = new AdministrateurMenus($pdo);
        $this->employes = new GestionEmployes($pdo);
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

            $ok = $this->commandes->changerStatut($commandeId, $statut, $this->administrateurId, $motif, $modeContact);
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

        if (isset($_POST['creer_menu'])) {
            $titre = trim($_POST['titre_menu'] ?? '');
            $prixBase = (float) ($_POST['prix_menu'] ?? 0);
            $stock = (int) ($_POST['stock_menu'] ?? 0);

            if ($titre !== '' && $prixBase > 0 && $stock >= 0) {
                $ok = $this->menus->creerMenu($titre, $prixBase, $stock);
                $this->message = $ok ? 'Menu cree !' : 'Erreur lors de la creation du menu.';
            } else {
                $this->message = 'Veuillez renseigner un titre, un prix et un stock valides.';
            }
        }

        if (isset($_POST['creer_employe'])) {
            $nom = trim($_POST['nom_employe'] ?? '');
            $prenom = trim($_POST['prenom_employe'] ?? '');
            $email = trim($_POST['email_employe'] ?? '');
            $motDePasse = $_POST['motDePasse_employe'] ?? '';
            
            if ($nom && $prenom && $email && $motDePasse)  {
                $ok = $this->employes->creerEmploye($nom, $prenom, $email, $motDePasse);
                $this->message = $ok ? 'Employe cree !' : 'Erreur l employe n a pas ete cree';
            }
        }

        if (isset($_POST['desactiver_employe'])) {
            $id = (int) ($_POST['employe_id'] ?? 0);
            $ok = $this->employes->activerEmploye($id);
            $this->message = $ok ? 'Employe reactive' : 'Erreur lors de la reactivation de l employe';
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
        $employes = $this->employes->getEmployes();
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

            <section class="commande-administrateur">
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

            <section class="avis-administrateur">
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

            <section class="menus-administrateur">
                <h2>Gestion des menus</h2>
                <h3>Creer un menu</h3>
                <form method="POST" class="creer-menu-form">
                    <input type="text" name="titre_menu" placeholder="Titre du menu" required>
                    <input type="number" step="0.01" name="prix_menu" placeholder="Prix de base" min="0" required>
                    <input type="number" name="stock_menu" placeholder="Stock" min="0" required>
                    <button type="submit" name="creer_menu">Creer le menu</button>
                </form>

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

            <section class="gestion-employes">
                <h2>Gestion des employes</h2>

                <h3>Creer un compte employe</h3>
                <form method="POST">
                    <input type="text" name="nom_employe" placeholder="Nom" required>
                    <input type="text" name="prenom_employe" placeholder="Prenom" required>
                    <input type="email" name="email_employe" placeholder="Email" required>
                    <input type="password" name="motDePasse_employe" placeholder="Mot de Passe" required>
                    <button type="submit" name="creer_employe">Creer l'employe</button>
                </form>

                <h3>Employes existant</h3>
                <?php foreach ($employes as $employe): ?>
                    <div class="employe">
                        <p>
                            <?= htmlspecialchars($employe['nom']) ?> <?= htmlspecialchars($employe['prenom']) ?>
                            <?= htmlspecialchars($employe['email']) ?> <?= $employe['actif'] ? 'Actif' : 'Inactif' ?>
                        </p>
                        <form method="POST">
                            <input type="hidden" name="employe_id" value="<?= $employe['id'] ?>">
                            <?php if ($employe['actif']): ?>
                                <button type="submit" name="desactiver_employe">Desactiver le compte</button>
                            <?php else: ?>
                                <button type="submit" name="activer_employe">Reactiver le compte</button>
                            <?php endif; ?>
                        </form>
                    </div>
                <?php endforeach; ?>
            </section>
        </main>
        
        <?php
        include 'includes/site_bottom.php';
    }
}

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['administrateur'])) {
    header('Location: login.php');
    exit;
}

$administrateurId = (int) $_SESSION['user']['id'];
$page = new EspaceAdministrateurPage($pdo, $administrateurId);
$page->handleRequest();
$page->render();
