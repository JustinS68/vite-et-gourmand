<?php
declare(strict_types=1); ?>

<?php require_once '../config/database.php'; ?>

<?php
include 'includes/site_top.php'; ?>

<?php
class UserAuth
{
    private PDO $pdo;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function handlePost(string $mode): ?string
    {
        if ($mode === 'inscription') {
            return $this->registerUser();
        }
        if ($mode === 'connexion') {
            return $this->loginUser();
        }

        return null;
    }

private function registerUser(): ?string
{
    $nom= trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    if (!$nom || !$prenom || !$telephone || !$email || !$adresse || !$motDePasse) {
        return 'Tous les champs sont requis.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Adresse e-mail invalide.';
    }
    if (!$this->validatePassword($motDePasse)) {
        return 'Le mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
    }

    $hashedPassword = password_hash($motDePasse, PASSWORD_DEFAULT);

    $stmt = $this->pdo->prepare(
        'INSERT INTO utilisateur
        (nom, prenom, telephone, email, adresse, mot_de_passe, role)
        VALUES
        (:nom, :prenom, :telephone, :email, :adresse, :mot_de_passe, :role)'
    );

    try {
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':telephone' => $telephone,
            ':email' => $email,
            ':adresse' => $adresse,
            ':mot_de_passe' => $hashedPassword,
            ':role' => 'utilisateur',
        ]);
    } catch (PDOException $exception) {
        error_log('Erreur inscription : ' . $exception->getMessage());
        return 'Impossible de creer le compte. Veuillez réessayer plus tard.';
    }

    return 'Inscription reussie';
}

private function loginUser(): ?string
{
    $email = trim($_POST['email']?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    if (!$email || !$motDePasse) {
        return 'Tous les champs sont requis.';
    }

    $stmt = $this->pdo->prepare('SELECT id, nom, role, mot_de_passe FROM utilisateur WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (! $user || !password_verify($motDePasse, (string) $user['mot_de_passe'])) {
        return 'Email ou mot de passe incorrect.';
    }

    $_SESSION['user'] = [
        'id' => $user['id'],
        'nom' => $user['nom'],
        'role' => $user['role'],
    ];

    return 'Connexion reussie';
}

private function validatePassword(string $password): bool
{
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/', $password) === 1;

}
}
class LoginPage
{
    private UserAuth $auth;
    private string $mode;
    private ?string $message = null;

    public function __construct(PDO $pdo, string $mode)
    {
        $this->auth = new UserAuth($pdo);
        $this->mode = $mode;
    }

//la connection redirige directement vers le bon utilisateur (administrateur, employe ou utilisateur)

    public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->message = $this->auth->handlePost($this->mode);

            if ($this->message === 'Connexion reussie') {
                $role = $_SESSION['user']['role'];
                if ($role === 'administrateur') {
                    header('Location: /vite-gourmand/pages/espace_admin.php');
                } elseif ($role === 'employe') {
                    header('Location: /vite-gourmand/pages/espace_employe.php');
                } else {
                    header('Location: /vite-gourmand/pages/espace_connecte.php');
                }
                    exit;
                }
            }
        }

public function render(): void
{ 
?>
<main>
    <?php if ($this->mode === 'inscription'): ?>
        <h1>creer un compte</h1>
        <form method="POST" action="login.php?mode=inscription">
            <input type="text" name="nom" placeholder="Nom" required>
            <input type="text" name="prenom" placeholder="Prenom" required>
            <input type="text" name="telephone" placeholder="Telephone" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="adresse" placeholder="Adresse" required>
            <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
            <button type="submit">S'inscrire</button>
        </form>
        <?php if ($this->message !== null): ?>
            <p style="color: red;"><?php echo htmlspecialchars($this->message, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif ?>
        <a href="login.php?mode=connexion">Déjà un compte ? Connectez-vous</a>

    <?php else: ?> 
        <h1>Connexion</h1>
        <form method="POST" action="login.php">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
        <?php if ($this->message !== null): ?>
            <p style="color: red;"><?php echo htmlspecialchars($this->message, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <a href="login.php?mode=inscription">Pas encore de compte ? Inscrivez-vous</a>
    <?php endif; ?>
</main>
<?php    
}
} 

$mode = $_GET['mode'] ?? 'connexion';
$page = new LoginPage($pdo, is_string($mode) ? $mode : 'connexion');
$page->handleRequest();
$page->render(); ?>

<?php include 'includes/site_bottom.php'; ?>
