<?php
declare(strict_types=1);

require_once '../config/database.php';

class UserRegistration
{
    private PDO $pdo;
    private array $errors = [];
    private ?string $message = null;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
// Cette méthode contrôle le processus complet d'inscription
    public function handle(array $post): void
    {
        if (!isset($post['submit'])) {
            return;
        }

        $username = trim($post['username'] ?? '');
        $email = trim($post['email'] ?? '');
        $password = $post['password'] ?? '';

        if ($username === '' || $email === '' || $password === '') {
            $this->errors[] = 'Tous les champs sont requis.';

            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Adresse e-mail invalide.';

            return;
        }

        if ($this->userExists($email)) {
            $this->errors[] = 'L\'utilisateur existe déjà.';

            return;
        }

        if ($this->registerUser($username, $email, $password)) {
            $this->message = 'Compte créé avec succès.';
            mail(
                $email,
                'Bienvenue sur notre site',
                'Merci de vous être inscrit sur notre site.',
                'From: no-reply@viteetgourmand.com'
            );
        } else {
            $this->errors[] = 'Erreur lors de la création du compte.';
        }
    }

    private function userExists(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM utilisateur WHERE email = ?');
        $stmt->execute([$email]);

        return (bool) $stmt->fetch();
    }

    private function registerUser(string $username, string $email, string $password): bool
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare('INSERT INTO utilisateur (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)');

        return $stmt->execute([$username, $email, $hashedPassword, 'utilisateur']);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}

class RegistrationPage
{
    private UserRegistration $registration;

    public function __construct(PDO $pdo)
    {
        $this->registration = new UserRegistration($pdo);
    }

    public function handleRequest(): void
    {
        $this->registration->handle($_POST);
    }

    public function render(): void
    {
        include 'includes/site_top.php';
        ?>
        <main>
            <?php if ($this->registration->getMessage()): ?>
                <p><?= htmlspecialchars((string) $this->registration->getMessage(), ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php foreach ($this->registration->getErrors() as $error): ?>
                <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
        </main>
        <?php
        include 'includes/site_bottom.php';
    }
}

$page = new RegistrationPage($pdo);
$page->handleRequest();
$page->render();