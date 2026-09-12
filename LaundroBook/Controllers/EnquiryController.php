<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class enquiryController
{
    private EnquiryRepoInterface $enquiryRepo;
    private array $data = [];
    private array $errors = [];

    public function __construct(EnquiryRepoInterface $enquiryRepo)
    {
        $this->enquiryRepo = $enquiryRepo;
    }

    public function validate_input(): bool
    {
        $name = trim($_POST['full_name'] ?? '');
        if ($name === '') {
            $this->errors[] = 'Full name is required.';
        } elseif (strlen($name) < 2) {
            $this->errors[] = 'Full name must be at least 2 characters.';
        }

        $email = trim($_POST['email_address'] ?? '');
        if ($email === '') {
            $this->errors[] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Please enter a valid email address.';
        }

        $subject = trim($_POST['message_subject'] ?? '');
        if ($subject === '') {
            $this->errors[] = 'Subject is required.';
        }

        $message = trim($_POST['customer_message'] ?? '');
        if ($message === '') {
            $this->errors[] = 'Message is required.';
        }

        if (!empty($this->errors)) {
            return false;
        }

        $this->data = [
            'name' => $name,
            'email' => $email,
            // enquiry table has no separate subject column - combined
            // into one message value here, right before storage, so
            // the subject isn't silently dropped.
            'message' => "Subject: {$subject}\n\n{$message}",
        ];

        return true;
    }

    public function submitEnquiry(): void
    {
        if (!$this->validate_input()) {
            $_SESSION['enquiry_errors'] = $this->errors;
            header('Location: ../Views/contact.php');
            exit;
        }

        try {
            $this->enquiryRepo->insert(
                $this->data['name'],
                $this->data['email'],
                $this->data['message']
            );
        } catch (Exception $e) {
            // enquiry.email has a UNIQUE constraint, so a repeat sender
            // hits MySQL error 1062 specifically - give them an honest
            // reason rather than the generic message below, since
            // "try again" would fail identically every time for them.
            if ((int)$e->getCode() === 1062) {
                $_SESSION['enquiry_errors'] = ['An enquiry with this email address already exists. Please contact us directly if you need to follow up.'];
            } else {
                // A genuine database failure (connection issue, etc.) -
                // different from a validation error above, but the customer
                // still needs a clear message rather than a blank/broken page.
                $_SESSION['enquiry_errors'] = ['Your message could not be sent, please try again.'];
            }
            header('Location: ../Views/contact.php');
            exit;
        }

        $_SESSION['enquiry_success'] = true;
        header('Location: ../Views/contact.php');
        exit;
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && basename($_SERVER['SCRIPT_NAME']) === 'enquiryController.php') {
    require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';
    require_once __DIR__ . '/../Repositories/EnquiryRepo.php';

    $controller = new enquiryController(new EnquiryRepo());
    $controller->submitEnquiry();
}