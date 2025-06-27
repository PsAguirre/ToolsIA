<?php
// dictionary.php

// Endpoint para traducción bidireccional (ES↔EN)
// Lectura de un JSON de traducciones.

date_default_timezone_set('America/Santiago');

// Permitir CORS para peticiones desde el frontend
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// Ruta al archivo JSON con pares de traducción
define('DICT_FILE', __DIR__ . '/data/translations.json');

// Obtener consulta
$word = isset($_GET['word']) ? strtolower(trim($_GET['word'])) : '';
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['es2en','en2es']) ? $_GET['lang'] : 'en2es';

$result = ['word' => $word, 'translation' => null];

if ($word !== '' && file_exists(DICT_FILE)) {
    $json = file_get_contents(DICT_FILE);
    $dict = json_decode($json, true);
    if ($lang === 'en2es') {
        // Inglés a Español
        if (isset($dict['en2es'][$word])) {
            $result['translation'] = $dict['en2es'][$word];
        }
    } else {
        // Español a Inglés: invertir índice
        if (isset($dict['es2en'][$word])) {
            $result['translation'] = $dict['es2en'][$word];
        }
    }
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
