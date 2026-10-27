<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$languages = [
    'en' => 'English',
    'lv' => 'Latviešu',
];

if (isset($_GET['lang']) && isset($languages[$_GET['lang']])) {
    $_SESSION['lang'] = $_GET['lang'];
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

$lang = $_SESSION['lang'] ?? 'en';
if (!isset($languages[$lang])) $lang = 'en';

$translations = [
    'en' => [
        'settings'     => 'Settings',
        'account'      => 'Account',
        'liked'        => 'Liked quizzes',
        'leaderboard'  => 'Leaderboard',
        'leaderboards' => 'Leaderboards',
        'quizzes'      => 'Quizzes',
        'search'       => 'Search quizzes...',
        'admin'        => 'Admin',
        'logout'       => 'Logout',
        'login'        => 'Login',
        'register'     => 'Register',
        'menu'         => 'Menu',
        'language'     => 'Language',
        'theme'        => 'Toggle theme',
        'faq'          => 'FAQ',
        'rights'       => 'All rights reserved.',
    ],
    'lv' => [
        'settings'     => 'Iestatījumi',
        'account'      => 'Konts',
        'liked'        => 'Iecienītās viktorīnas',
        'leaderboard'  => 'Līderu saraksts',
        'leaderboards' => 'Līderu saraksti',
        'quizzes'      => 'Viktorīnas',
        'search'       => 'Meklēt viktorīnas...',
        'admin'        => 'Admins',
        'logout'       => 'Iziet',
        'login'        => 'Ielogoties',
        'register'     => 'Reģistrēties',
        'menu'         => 'Izvēlne',
        'language'     => 'Valoda',
        'theme'        => 'Mainīt motīvu',
        'faq'          => 'BUJ',
        'rights'       => 'Visas tiesības aizsargātas.',
    ],
];

function t(string $key): string {
    global $lang, $translations;
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}
function tr(array $row, string $field): string {
    global $lang;
    if ($lang !== 'en') {
        $value = $row[$field . '_' . $lang] ?? '';
        if ($value !== '') return $value;
    }
    return $row[$field] ?? '';
}