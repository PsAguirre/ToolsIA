<?php
// convertir.php

date_default_timezone_set('America/Santiago');
// Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoload de Composer
require __DIR__ . '/vendor/autoload.php';
use Smalot\PdfParser\Parser;
set_time_limit(300);

// Directorio para audios e imágenes temporales
$audioDir = __DIR__ . '/audios';
if (!is_dir($audioDir)) mkdir($audioDir, 0777, true);

// Ejecutables (ajusta rutas según tu instalación)
define('BIN_ESPEAK', 'C:/Program Files/eSpeak NG/espeak-ng.exe');
define('BIN_FFMPEG', 'C:/ffmpeg/bin/ffmpeg.exe');
define('BIN_MAGICK', 'C:/Program Files/ImageMagick-7.1.0-Q16/magick.exe');

$error = '';
$audioUrl = '';
$downloadName = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Validar subida de PDF
    if (!isset($_FILES['pdfFile']) || $_FILES['pdfFile']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Error al subir el archivo.';
    } else {
        $tmpPath = $_FILES['pdfFile']['tmp_name'];
        $mime    = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tmpPath);
        if ($mime !== 'application/pdf') {
            $error = 'Formato no válido. Selecciona un PDF.';
        }
    }
    // 2) Extraer texto con PDFParser
    $text = '';
    if (!$error) {
        try {
            $parser = new Parser();
            $pdf    = $parser->parseFile($tmpPath);
            $text   = $pdf->getText();
        } catch (Exception $e) {
            $text = '';
        }
    }
    // 3) Fallback OCR si no hay texto
    if (!$error && trim($text) === '') {
        exec(escapeshellarg(BIN_MAGICK) . " -density 300 " . escapeshellarg($tmpPath) . " " . escapeshellarg("$audioDir/page_%d.png") . " 2>&1");
        $ocrText = '';
        foreach (glob("$audioDir/page_*.png") as $img) {
            exec("tesseract " . escapeshellarg($img) . " stdout -l spa 2>&1", $out, $code);
            if ($code === 0) $ocrText .= implode("\n", $out) . "\n";
            @unlink($img);
        }
        $text = $ocrText;
        if (trim($text) === '') $error = 'No se pudo extraer texto del PDF.';
    }
    // 4) Generar audio si hay texto
    if (!$error) {
        $baseRaw = pathinfo($_FILES['pdfFile']['name'], PATHINFO_FILENAME);
        $baseSafe = preg_replace('/[^A-Za-z0-9_-]/', '_', $baseRaw);
        $wav = "$audioDir/{$baseSafe}_" . uniqid() . ".wav";
        $mp3 = "$audioDir/{$baseSafe}_" . uniqid() . ".mp3";
        $txt = "$audioDir/{$baseSafe}_" . uniqid() . ".txt";
        file_put_contents($txt, $text);
        // 4a) eSpeak NG con prosodia
        $cmdEs = escapeshellarg(BIN_ESPEAK)
               . " -v es+f4 -p 50 -s 130 -a 110"
               . " -f " . escapeshellarg($txt)
               . " -w " . escapeshellarg($wav) . " 2>&1";
        exec($cmdEs, $_, $c1);
        @unlink($txt);
        if ($c1 !== 0) {
            $error = 'Error al generar audio WAV.';
        } else {
            // 4b) FFmpeg convierte WAV a MP3
            $cmdFm = escapeshellarg(BIN_FFMPEG)
                   . " -y -i " . escapeshellarg($wav)
                   . " " . escapeshellarg($mp3)
                   . " -hide_banner -loglevel error 2>&1";
            exec($cmdFm, $_, $c2);
            @unlink($wav);
            if ($c2 !== 0) {
                $error = 'Error al convertir a MP3.';
            } else {
                $audioUrl     = "audios/" . basename($mp3);
                $downloadName = basename($mp3);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" class="bg-gray-900 text-gray-200">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <title>AudioExtract Pro - Convertir PDF a Voz</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Inter', sans-serif; }
    #spinnerOverlay { @apply hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50; }
    nav.fixed { padding-bottom: env(safe-area-inset-bottom); }
  </style>
</head>
<body class="flex flex-col min-h-screen">
  <!-- Spinner -->
  <div id="spinnerOverlay">
    <div class="spinner-border animate-spin inline-block w-12 h-12 border-4 rounded-full border-t-transparent border-white"></div>
  </div>

<!-- Header -->
<header class="bg-gray-800 fixed top-0 inset-x-0 z-40">
    <div class="max-w-md mx-auto flex items-center justify-between px-4 py-3">
      <!-- Logo y título … -->
      <div class="flex space-x-4">
        <a href="index.php"       class="text-indigo-400 hover:text-indigo-200 font-medium">Panel</a>
        <a href="convertir.php"   class="text-gray-300 hover:text-white   font-medium">Convertir</a>
        <a href="pdfafoto.php"    class="text-gray-300 hover:text-white   font-medium">Imagen</a>
      </div>
    </div>
    <!-- dropdown … -->
  </header>

  <!-- Main -->
  <main class="flex-1 pt-20 pb-4 overflow-y-auto">
    <section id="home" class="px-4">
      <div class="max-w-md mx-auto bg-gray-800 p-6 rounded-xl shadow-lg">
        <h2 class="text-2xl font-semibold text-white mb-4 text-center">Convierta PDF a Audio Profesional</h2>
        <?php if ($error): ?>
          <p class="text-red-400 text-center mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" id="pdfForm" class="space-y-4">
          <input type="file" name="pdfFile" accept=".pdf" required
                 class="block w-full text-gray-200 bg-gray-700 border border-gray-600 rounded-lg p-3" />
          <button type="submit"
                  class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg shadow-lg">
            Convertir Ahora
          </button>
        </form>
        <?php if ($audioUrl): ?>
          <div class="mt-6 text-center">
            <audio controls src="<?= htmlspecialchars($audioUrl) ?>" class="w-full"></audio>
            <a href="<?= htmlspecialchars($audioUrl) ?>" download="<?= htmlspecialchars($downloadName) ?>"
               class="mt-4 inline-block text-indigo-400 hover:text-indigo-200 font-medium">📥 Descargar Audio</a>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <!-- Historial Panel -->
  <div id="historyPanel" class="fixed inset-0 bg-gray-900 bg-opacity-90 hidden flex flex-col z-50">
    <div class="flex justify-between items-center bg-gray-800 p-4">
      <h3 class="text-lg font-semibold text-white">Historial de Audios</h3>
      <button id="closeHistory" class="text-gray-300 text-xl">&times;</button>
    </div>
    <div class="overflow-y-auto p-4 space-y-4">
      <?php
      $files = glob(__DIR__ . '/audios/*.mp3');
      rsort($files);
      foreach ($files as $file):
        $name = basename($file);
        $date = date('d/m/Y H:i', filemtime($file));
      ?>
        <div class="bg-gray-800 p-4 rounded-lg shadow-md">
          <p class="font-medium text-gray-200"><?= htmlspecialchars($name) ?></p>
          <p class="text-xs text-gray-400 mb-2"><?= htmlspecialchars($date) ?></p>
          <audio controls src="audios/<?= htmlspecialchars($name) ?>" class="w-full mb-2"></audio>
          <a href="audios/<?= htmlspecialchars($name) ?>" download
             class="text-indigo-400 hover:text-indigo-200 font-medium">Descargar</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Bottom Nav -->
  <nav class="fixed bottom-0 inset-x-0 bg-gray-800 border-t border-gray-700 flex z-40"
       style="padding-bottom: env(safe-area-inset-bottom)">
      <a href="index.php" class="...">Panel</a>
      <a href="convertir.php" class="...">Voz</a>
      <a href="pdfafoto.php" class="...">Imagen</a>
      <a href="fotoapdf.php" class="...">PDF</a>
  </nav>

  <!-- Footer -->
  <footer class="bg-gray-900 text-center text-gray-600 py-3 mt-auto" style="padding-bottom: env(safe-area-inset-bottom)">
    &copy; <?= date('Y') ?> AudioExtract Pro
  </footer>

  <!-- Scripts -->
  <script>
    document.getElementById('pdfForm').addEventListener('submit', function() {
      document.getElementById('spinnerOverlay').classList.remove('hidden');
    });
  </script>
</body>
</html>
```
