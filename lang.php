<?php

if (session_status() === PHP_SESSION_NONE) session_start();

$languages = [
    'en' => 'English',
    'lv' => 'Latviešu',
];

$defaultLang = 'en';

$lang = $defaultLang;

if (isset($_GET['lang']) && isset($languages[$_GET['lang']])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;

    if (!headers_sent()) {
        setcookie('lang', $lang, [
            'expires'  => time() + 60 * 60 * 24 * 365,
            'path'     => '/',
            'samesite' => 'Lax',
        ]);
    }
} elseif (isset($_SESSION['lang']) && isset($languages[$_SESSION['lang']])) {
    $lang = $_SESSION['lang'];
} elseif (isset($_COOKIE['lang']) && isset($languages[$_COOKIE['lang']])) {
    $lang = $_COOKIE['lang'];
    $_SESSION['lang'] = $lang;
}

$translations = [
    'en' => [
        'menu'         => 'Menu',
        'settings'     => 'Settings',
        'account'      => 'Account',
        'liked'        => 'Liked',
        'leaderboard'  => 'Leaderboard',
        'leaderboards' => 'Leaderboards',
        'search'       => 'Search quizzes...',
        'language'     => 'Language',
        'quizzes'      => 'Quizzes',
        'admin'        => 'Admin',
        'login'        => 'Login',
        'register'     => 'Sign up',
        'logout'       => 'Logout',
        'faq'          => 'FAQ',
        'rights'       => 'All rights reserved.',
    ],
    'lv' => [
        'menu'         => 'Izvēlne',
        'settings'     => 'Iestatījumi',
        'account'      => 'Konts',
        'liked'        => 'Patīk',
        'leaderboard'  => 'Līderu saraksts',
        'leaderboards' => 'Līderu saraksti',
        'search'       => 'Meklēt viktorīnas...',
        'language'     => 'Valoda',
        'quizzes'      => 'Viktorīnas',
        'admin'        => 'Administrators',
        'login'        => 'Pieslēgties',
        'register'     => 'Reģistrēties',
        'logout'       => 'Iziet',
        'faq'          => 'Biežāk uzdotie jautājumi',
        'rights'       => 'Visas tiesības aizsargātas.',
    ],
];

function t(string $key): string
{
    global $translations, $lang;
    $text = $translations[$lang][$key]
         ?? $translations['en'][$key]
         ?? $key;
    return htmlspecialchars($text);
}

function tr(array $row, string $field): string
{
    global $lang;
    foreach ([$field . '_' . $lang, $field . '_en', $field] as $col) {
        if (!empty($row[$col])) {
            return (string)$row[$col];
        }
    }
    return '';
}