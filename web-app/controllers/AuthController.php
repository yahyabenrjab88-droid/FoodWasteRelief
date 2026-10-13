<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';

class AuthController extends Controller
{
    public function login(): void
    {
        $this->render('auth/login', ['title' => 'Log In - FoodWasteRelief'], 'main');
    }

    public function loginPost(): void
    {
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/login');
        }

        $email = filter_var($this->sanitize($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');
        
        if (!$email || empty($password)) {
            $this->flash('error', 'Please provide both email and password.');
            $this->redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        $isValid = false;
        if ($user) {
            $storedPassword = (string)($user['password'] ?? '');
            $isBcrypt = str_starts_with($storedPassword, '$2y$');

            if ($isBcrypt) {
                $isValid = password_verify($password, $storedPassword);
            } else {
                $isValid = hash_equals($storedPassword, $password);
            }
        }

        if ($isValid && $user) {
            if ($user['status'] !== 'actif') {
                $this->flash('warning', 'Your account is currently ' . $user['status'] . '.');
                $this->redirect('/login');
            }

            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom']
            ];
            
            if ($user['role'] === 'business') {
                $biz = (new Business())->findByUserId((int)$user['id']);
                if ($biz) $_SESSION['business_id'] = $biz['id'];
            } elseif ($user['role'] === 'association') {
                $assoc = (new Association())->findByUserId((int)$user['id']);
                if ($assoc) $_SESSION['association_id'] = $assoc['id'];
            }
            
            try {
                (new ActivityLog())->log((int)$user['id'], 's\'est connecté', 'user', (int)$user['id']);
            } catch (\Exception $e) {
                error_log('ActivityLog error: ' . $e->getMessage());
            }

            if ($user['role'] === 'client') {
                $this->redirect('/products');
            } elseif ($user['role'] === 'admin') {
                $this->redirect('/admin/dashboard');
            } else {
                $this->redirect('/dashboard');
            }
        }

        $this->flash('error', 'E-mail ou mot de passe invalide.');
        $this->redirect('/login');
    }

    public function register(): void
    {
        $this->render('auth/register', ['title' => 'Create Account - FoodWasteRelief'], 'main');
    }

    public function registerPost(): void
    {
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/register');
        }

        $role = $this->sanitize($_POST['role'] ?? 'client');
        if (!in_array($role, ['client', 'business', 'association'])) {
            $this->flash('error', 'Invalid role selected.');
            $this->redirect('/register');
        }

        $email = filter_var($this->sanitize($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
        
        if (!$email || strlen($password) < 8 || $password !== $passwordConfirm) {
            $this->flash('error', 'Veuillez fournir un email valide et des mots de passe correspondants (min 8 caractères).');
            $this->redirect('/register');
        }

        if (!str_ends_with(strtolower($email), '@gmail.com')) {
            $this->flash('error', 'Désolé, seules les adresses @gmail.com sont autorisées.');
            $this->redirect('/register');
        }

        $data = [
            'nom' => $this->sanitize($_POST['nom'] ?? ''),
            'prenom' => $this->sanitize($_POST['prenom'] ?? ''),
            'email' => $email,
            'role' => $role,
            'telephone' => $this->sanitize($_POST['telephone'] ?? ''),
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'status' => $role === 'client' ? 'actif' : 'en_attente'
        ];

        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            
            $userId = (new User())->create($data);

            if ($role === 'business') {
                (new Business())->create([
                    'user_id' => $userId,
                    'nom_business' => $this->sanitize($_POST['nom_business'] ?? ''),
                    'localisation' => $this->sanitize($_POST['localisation'] ?? ''),
                    'ville' => $this->sanitize($_POST['ville'] ?? ''),
                    'patente' => $this->sanitize($_POST['patente'] ?? ''),
                    'status' => 'en_attente'
                ]);
            } elseif ($role === 'association') {
                (new Association())->create([
                    'user_id' => $userId,
                    'nom' => $this->sanitize($_POST['nom_association'] ?? ''),
                    'ville' => $this->sanitize($_POST['ville_association'] ?? ''),
                    'telephone' => $data['telephone'],
                    'status' => 'en_attente'
                ]);
            }

            $db->commit();
            $this->flash('success', 'Account created successfully! ' . ($role !== 'client' ? 'Please wait for admin approval.' : 'You can now log in.'));
            $this->redirect('/login');
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('Registration Error: ' . $e->getMessage());
            $this->flash('error', 'Registration failed: ' . $e->getMessage());
            $this->redirect('/register');
        }
    }

    public function logout(): void
    {
        if (isset($_SESSION['user']['id'])) {
            try {
                (new ActivityLog())->log((int)$_SESSION['user']['id'], 's\'est déconnecté', 'user', (int)$_SESSION['user']['id']);
            } catch (\Exception $e) {
                error_log('ActivityLog error: ' . $e->getMessage());
            }
        }
        
        $_SESSION = [];
        session_destroy();
        session_start();
        $this->flash('success', 'Déconnexion réussie.');
        $this->redirect('/login');
    }
}
