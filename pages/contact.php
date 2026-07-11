<?php include 'includes/site_top.php'; ?>

<main>
<?php
class pageContact
{
	public function render()
	{
		$errors = [];
		$success = ''; 
// Envoie le formulaire de contact à l’entreprise et répond à l’utilisateur via l’adresse fournie
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$title = isset($_POST['title']) ? trim($_POST['title']) : '';
			$message = isset($_POST['message']) ? trim($_POST['message']) : '';
			$reply_to = isset($_POST['reply_to']) ? trim($_POST['reply_to']) : '';

			if ($title === '') $errors[] = 'Le titre est requis.';
			if ($message === '') $errors[] = 'La description est requise.';
			if ($reply_to === '' || !filter_var($reply_to, FILTER_VALIDATE_EMAIL)) $errors[] = 'Un e-mail de réponse valide est requis.';

			if (empty($errors)) {
				$to = 'viteetgourmand@hotmail.fr';
				$subject = '[Contact] ' . substr($title, 0, 100);
				$body = "Titre: $title\n\nDescription:\n$message\n\nAdresse de réponse: $reply_to\n";
				$headers = "From: no-reply@" . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n";
				$headers .= "Reply-To: $reply_to\r\n";
				$headers .= "Content-Type: text/plain; charset=utf-8\r\n";

				if (mail($to, $subject, $body, $headers)) {
					$success = 'Votre message a été envoyé avec succès.';
					$title = $message = $reply_to = '';
				} else {
					$errors[] = "Échec de l'envoi. Veuillez réessayer plus tard.";
				}
			}
		} else {
			$title = $message = $reply_to = '';
			if (isset($_SESSION['user_email'])) {
				$reply_to = $_SESSION['user_email'];
			}
		}

		?>
		<section class="contact-form">
			<h1>Vous avez des questions? Contactez-nous</h1>
			<?php if (!empty($errors)): ?>
				<div class="errors"><ul><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div>
			<?php endif; ?>
			<?php if ($success): ?>
				<div class="success"><?php echo htmlspecialchars($success); ?></div>
				<p>Merci pour votre intérêt! Nous répondrons au plus vite à votre demande.</p>
			<?php else: ?>
				<form method="post" action="">
					<label for="title">Sujet:</label><br>
					<input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required><br>

					<label for="message">Description:</label><br>
					<textarea id="message" name="message" rows="6" required><?php echo htmlspecialchars($message); ?></textarea><br>

					<label for="reply_to">E-mail pour etre contacte</label><br>
					<input type="email" id="reply_to" name="reply_to" value="<?php echo htmlspecialchars($reply_to); ?>" required><br>

					<button type="submit">Envoyer</button>
				</form>
			<?php endif; ?>
		</section>
		<?php
	}
}

$page = new pageContact();
$page->render();
?>

<?php include 'includes/site_bottom.php'; ?>