<?php
declare(strict_types=1);

namespace App\Core;

use Exception;

/**
 * Authentication controller.
 */
class AuthController extends Controller
{
    public function showLogin(): void
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        $this->renderAuth('login', [
            'title' => 'Login',
            'errors' => [],
            'username' => '',
        ]);
    }

    public function login(): void
    {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (!$this->validateCsrf()) {
            $this->renderAuth('login', [
                'title' => 'Login',
                'errors' => ['Invalid security token. Please try again.'],
                'username' => $username,
            ]);
            return;
        }

        if ($username === '' || $password === '') {
            $this->renderAuth('login', [
                'title' => 'Login',
                'errors' => ['Username/email and password are required.'],
                'username' => $username,
            ]);
            return;
        }

        if ($this->auth->isRateLimited()) {
            $this->renderAuth('login', [
                'title' => 'Login',
                'errors' => ['Too many failed login attempts. Please try again later.'],
                'username' => $username,
            ]);
            return;
        }

        try {
            $user = $this->auth->authenticate($username, $password);
        } catch (Exception $e) {
            $this->logger->error('Login failed due to system error', ['error' => $e->getMessage()]);
            $this->renderAuth('login', [
                'title' => 'Login',
                'errors' => ['Unable to process login at this time.'],
                'username' => $username,
            ]);
            return;
        }

        if (!$user) {
            $this->renderAuth('login', [
                'title' => 'Login',
                'errors' => ['Invalid username/email or password.'],
                'username' => $username,
            ]);
            return;
        }

        $this->auth->login($user);
        $this->logger->info('User logged in', ['user_id' => $user['id']]);
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        $user = $this->auth->user();
        $this->auth->logout();

        if ($user) {
            $this->logger->info('User logged out', ['user_id' => $user['id']]);
        }

        Response::redirect('/login', 'You have been logged out.');
    }

    public function showForgotPassword(): void
    {
        $this->renderAuth('forgot_password', [
            'title' => 'Forgot Password',
            'errors' => [],
            'email' => '',
        ]);
    }

    public function forgotPassword(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));

        if (!$this->validateCsrf()) {
            $this->renderAuth('forgot_password', [
                'title' => 'Forgot Password',
                'errors' => ['Invalid security token. Please try again.'],
                'email' => $email,
            ]);
            return;
        }

        $this->logger->info('Password reset requested', ['email' => $email]);
        Response::flash('Password reset is not configured yet. Please contact your administrator.', 'info');
        $this->redirect('/login');
    }

    public function showResetPassword(string $token = ''): void
    {
        $this->renderAuth('reset_password', [
            'title' => 'Reset Password',
            'errors' => ['Password reset token handling is not configured yet.'],
            'token' => $token,
        ]);
    }

    public function resetPassword(): void
    {
        Response::flash('Password reset is not configured yet. Please contact your administrator.', 'info');
        $this->redirect('/login');
    }

    private function renderAuth(string $view, array $data = []): void
    {
        $viewFile = APP_ROOT . '/templates/auth/' . $view . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(404);
            require_once APP_ROOT . '/404.php';
            exit;
        }

        extract($data);
        require $viewFile;
    }
}