<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';

class PasswordResetController extends Controller
{
    
    public function showForgot(): void
    {
        $this->render('auth/forgot', ['title' => 'Mot de passe oublié'], 'main');
    }

    public function processForgot(): void
    {
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/forgot-password');
        }

        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $this->flash('error', 'Adresse e-mail invalide.');
            $this->redirect('/forgot-password');
        }

        $db   = Database::getConnection();
        $user = (new User())->findByEmail($email);

        $this->flash('success', 'Si cette adresse est enregistrée, vous recevrez un e-mail avec les instructions.');

        if ($user) {
            
            $db->prepare('DELETE FROM password_resets WHERE email = :email')
               ->execute(['email' => $email]);

            $token     = bin2hex(random_bytes(32));          
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); 

            $db->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)')
               ->execute(['email' => $email, 'token' => $token, 'expires' => $expiresAt]);

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $resetLink = $scheme . '://' . $host . BASE_URL . '/reset-password/' . $token;

            $html = "
            <div style='font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:32px;border-radius:12px;background:#f9f9f9;border:1px solid #e5e7eb;'>
                <div style='text-align:center;margin-bottom:24px;'>
                    <span style='font-size:32px;'>🌿</span>
                    <h2 style='color:#2d6a4f;margin:8px 0 4px;'>FoodWasteRelief</h2>
                    <p style='color:#6b7280;font-size:14px;margin:0;'>Réinitialisation de mot de passe</p>
                </div>
                <p style='color:#374151;'>Bonjour,</p>
                <p style='color:#374151;'>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le bouton ci-dessous. Ce lien expirera dans <strong>1 heure</strong>.</p>
                <div style='text-align:center;margin:32px 0;'>
                    <a href='$resetLink' style='background:#2d6a4f;color:#fff;padding:14px 32px;border-radius:50px;text-decoration:none;font-weight:bold;font-size:16px;'>
                        Réinitialiser mon mot de passe
                    </a>
                </div>
                <p style='color:#6b7280;font-size:13px;'>Si vous n'avez pas fait cette demande, ignorez cet e-mail. Votre mot de passe restera inchangé.</p>
                <hr style='border:none;border-top:1px solid #e5e7eb;margin:24px 0;'>
                <p style='color:#9ca3af;font-size:12px;text-align:center;'>FoodWasteRelief · Ne pas répondre à cet e-mail</p>
            </div>";

            $sent = (new Mailer())->send(
                $email,
                'Réinitialisation de votre mot de passe — FoodWasteRelief',
                $html
            );

            if (!$sent) {
                error_log("[PasswordReset] Email failed for $email. Manual link: $resetLink");
            }
            
        }

        $this->redirect('/forgot-password');
    }

    public function showReset(string $token): void
    {
        $reset = $this->findValidToken($token);
        if (!$reset) {
            $this->flash('error', 'Ce lien est invalide ou a expiré. Veuillez recommencer.');
            $this->redirect('/forgot-password');
        }

        $this->render('auth/reset', [
            'title' => 'Réinitialiser le mot de passe',
            'token' => $token,
        ], 'main');
    }

    public function processReset(string $token): void
    {
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/reset-password/' . $token);
        }

        $reset = $this->findValidToken($token);
        if (!$reset) {
            $this->flash('error', 'Ce lien est invalide ou a expiré. Veuillez recommencer.');
            $this->redirect('/forgot-password');
        }

        $password        = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if (strlen($password) < 8) {
            $this->flash('error', 'Le mot de passe doit contenir au moins 8 caractères.');
            $this->redirect('/reset-password/' . $token);
        }

        if ($password !== $passwordConfirm) {
            $this->flash('error', 'Les mots de passe ne correspondent pas.');
            $this->redirect('/reset-password/' . $token);
        }

        $db   = Database::getConnection();
        $user = (new User())->findByEmail($reset['email']);
        if (!$user) {
            $this->flash('error', 'Utilisateur introuvable.');
            $this->redirect('/forgot-password');
        }

        (new User())->updatePasswordHash((int)$user['id'], $password);

        $db->prepare('UPDATE password_resets SET used = 1 WHERE token = :token')
           ->execute(['token' => $token]);

        try {
            (new ActivityLog())->log((int)$user['id'], 'réinitialisé le mot de passe', 'user', (int)$user['id']);
        } catch (\Exception $e) {}

        $this->flash('success', 'Mot de passe réinitialisé avec succès ! Vous pouvez maintenant vous connecter.');
        $this->redirect('/login');
    }

    private function findValidToken(string $token): ?array
    {
        try {
            $db   = Database::getConnection();
            $stmt = $db->prepare(
                'SELECT * FROM password_resets
                 WHERE token = :token AND used = 0 AND expires_at > NOW()'
            );
            $stmt->execute(['token' => $token]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\PDOException $e) {
            error_log('PasswordReset->findValidToken error: ' . $e->getMessage());
            return null;
        }
    }
}
