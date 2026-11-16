<?php

function publicFormToken($formName) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['public_form_tokens'][$formName])) {
        $_SESSION['public_form_tokens'][$formName] = bin2hex(random_bytes(32));
        $_SESSION['public_form_issued'][$formName] = time();
    }

    return $_SESSION['public_form_tokens'][$formName];
}

function validatePublicForm($formName) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $submittedToken = $_POST['csrf_token'] ?? '';
    $expectedToken = $_SESSION['public_form_tokens'][$formName] ?? '';
    if (!is_string($submittedToken) || $expectedToken === '' || !hash_equals($expectedToken, $submittedToken)) {
        return 'csrf';
    }

    if (!empty($_POST['website'])) {
        return 'spam';
    }

    $issuedAt = (int)($_SESSION['public_form_issued'][$formName] ?? 0);
    if ($issuedAt === 0 || time() - $issuedAt < 2) {
        return 'spam';
    }

    $now = time();
    $recentSubmissions = array_filter(
        $_SESSION['public_form_submissions'][$formName] ?? [],
        static function ($submittedAt) use ($now) {
            return $submittedAt > $now - 900;
        }
    );
    if (count($recentSubmissions) >= 3) {
        return 'rate';
    }

    $recentSubmissions[] = $now;
    $_SESSION['public_form_submissions'][$formName] = array_values($recentSubmissions);
    unset($_SESSION['public_form_tokens'][$formName], $_SESSION['public_form_issued'][$formName]);

    return '';
}
