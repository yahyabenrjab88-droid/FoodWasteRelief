<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ContactRequest.php';

class ContactController extends Controller
{
    public function index(): void
    {
        $this->render('home/contact', ['title' => 'Contactez-nous - FoodWasteRelief'], 'main');
    }

    public function submit(): void
    {
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/contact');
        }

        $name = $this->sanitize($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $subject = $this->sanitize($_POST['subject'] ?? '');
        $message = $this->sanitize($_POST['message'] ?? '');

        if (!$name || !$email || !$subject || !$message) {
            $this->flash('error', 'Veuillez remplir tous les champs correctement.');
            $this->redirect('/contact');
        }

        $senderId = null;
        $senderRole = 'guest';

        if (isset($_SESSION['user']['id'])) {
            $senderId = (int)$_SESSION['user']['id'];
            $senderRole = $_SESSION['user']['role'];
        }

        $body = "De: $name ($email)\n\n" . $message;

        (new ContactRequest())->create([
            'sender_id' => $senderId,
            'sender_role' => $senderRole,
            'subject' => $subject,
            'body' => $body
        ]);

        $this->flash('success', 'Votre message a été envoyé avec succès ! Nous vous contacterons bientôt.');
        $this->redirect('/contact');
    }

    public function manage(): void
    {
        $this->requireRole('admin');
        $requests = (new ContactRequest())->findAll();
        $this->render('contact_requests/index', ['title' => 'Demandes de Contact', 'requests' => $requests], 'dashboard');
    }

    public function markRead(string $id): void
    {
        $this->requireRole('admin');
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/admin/contact');
        }

        (new ContactRequest())->markRead((int)$id);
        $this->flash('success', 'Message marqué comme lu.');
        $this->redirect('/admin/contact');
    }
}
