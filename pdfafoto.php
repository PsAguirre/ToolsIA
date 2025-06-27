<?php
// pdfafoto.php

// Zona horaria
date_default_timezone_set('America/Santiago');
// Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoload de Composer
require __DIR__ . '/vendor/autoload.php';

// Extender tiempo de ejecución
set_time_limit(300);

// Directorios para subida y salida de imágenes
$uploadDir = __DIR__ . '/uploads';
$imgDir    = __DIR__ . '/imagenes';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
if (!is_dir($imgDir))    mkdir($imgDir,    0777, true);

// Ejecutable de ImageMagick en tu ruta instalada
define('BIN_MAGICK', 'C:/Program Files/ImageMagick-7.1.1-Q16-HDRI/magick.exe');

// Detectar fallback a 'magick' en PATH
if (!file_exists(BIN_MAGICK)) {
    exec('where magick', $whichOut, $which);
    if ($which === 0 && isset($whichOut[0]) && file_exists(trim($whichOut[0]))) {
        define('MAGICK_CMD', 'magick');
    } else {
        die("No se encontró ImageMagick en '" . BIN_MAGICK . "' ni el comando 'magick'. Ajusta BIN_MAGICK o agrégalo al PATH.");
    }
} else {
    define('MAGICK_CMD', BIN_MAGICK);
}

$error  = '';
$images = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Validar subida de PDF
    if (!isset($_FILES['pdfFile']) || $_FILES['pdfFile']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Error al subir el archivo.';
    } else {
        $tmpPath = $_FILES['pdfFile']['tmp_name'];
        $mime    = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tmpPath);
        if ($mime !== 'application/pdf') {
            $error = 'Selecciona un PDF válido.';
        }
    }

    // 2) Procesar conversión si no hay error
    if (!$error) {
        // Formato deseado
        $format  = strtolower($_POST['format'] ?? 'png');
        $allowed = ['png','jpg','jpeg','webp'];
        if (!in_array($format, $allowed)) $format = 'png';

        // Nombre base seguro
        $baseRaw  = pathinfo($_FILES['pdfFile']['name'], PATHINFO_FILENAME);
        $baseSafe = preg_replace('/[^A-Za-z0-9_-]/', '_', $baseRaw);

        // Guardar PDF temporal
        $pdfDest = "$uploadDir/" . uniqid("{$baseSafe}_") . '.pdf';
        move_uploaded_file($tmpPath, $pdfDest);

        // Comando ImageMagick para exportar cada página
        $pattern = "$imgDir/{$baseSafe}_page_%d.$format";
        $cmd     = escapeshellarg(MAGICK_CMD)
                 . ' -density 150 ' . escapeshellarg($pdfDest)
                 . ' -quality 90 '  . escapeshellarg($pattern)
                 . ' 2>&1';
        exec($cmd, $output, $code);
        @unlink($pdfDest);

        if ($code !== 0) {
            $error = 'Error al convertir PDF → imagen: ' . implode(' ', $output);
        } else {
            // Recoger imágenes generadas
            $images = glob("$imgDir/{$baseSafe}_page_*.{$format}");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="bg-gray-900 text-gray-200">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <title>AudioExtract Pro - PDF a Imagen</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Inter', sans-serif; }
    #spinner { @apply hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50; }
    nav.fixed { padding-bottom: env(safe-area-inset-bottom); }
  </style>
</head>
<body class="flex flex-col min-h-screen">
  <!-- Spinner -->
  <div id="spinner">
    <div class="animate-spin h-12 w-12 border-4 border-t-transparent rounded-full border-white"></div>
  </div>

  <!-- Header -->
  <header class="bg-gray-800 fixed top-0 inset-x-0 shadow z-40">
    <div class="max-w-md mx-auto flex items-center justify-between px-4 py-3">
      <h1 class="text-lg font-semibold text-indigo-400">AudioExtract Pro</h1>
      <div class="flex space-x-3">
        <a href="index.php" class="text-gray-300 hover:text-white">Panel</a>
        <a href="convertir.php" class="text-gray-300 hover:text-white">Voz</a>
        <a href="pdfafoto.php" class="text-indigo-400">Imagen</a>
      </div>
    </div>
  </header>

  <!-- Main -->
  <main class="flex-1 pt-20 pb-16 px-4 overflow-y-auto">
    <div class="max-w-md mx-auto bg-gray-800 p-6 rounded-xl shadow-lg">
      <h2 class="text-2xl font-semibold text-white mb-4 text-center">Convertir PDF a Imágenes</h2>
      <?php if ($error): ?>
        <p class="text-red-400 text-center mb-4"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
      <form method="POST" enctype="multipart/form-data" id="imgForm" class="space-y-4">
        <input type="file" name="pdfFile" accept=".pdf" required
               class="block w-full text-gray-200 bg-gray-700 border border-gray-600 rounded-lg p-3" />
        <select name="format" class="block w-full bg-gray-700 text-gray-200 rounded-lg p-3">
          <option value="png">PNG</option>
          <option value="jpg">JPG</option>
          <option value="webp">WEBP</option>
        </select>
        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg shadow-lg">
          Generar Imágenes
        </button>
      </form>
      <?php if ($images): ?>
        <div class="mt-6 space-y-4">
          <?php foreach ($images as $img): ?>
            <div class="bg-gray-700 p-2 rounded-lg">
              <img src="<?= str_replace(__DIR__, '.', $img) ?>" alt="Imagen PDF" class="w-full rounded" />
              <a href="<?= str_replace(__DIR__, '.', $img) ?>" download
                 class="inline-block mt-2 text-indigo-400 hover:text-indigo-200 font-medium">Descargar</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <!-- Bottom Nav -->
  <nav class="fixed bottom-0 inset-x-0 bg-gray-800 border-t border-gray-700 flex z-40"
       style="padding-bottom: env(safe-area-inset-bottom)">
    <a href="index.php" class="flex-1 text-center py-3 text-indigo-400 font-medium">Panel</a>
    <a href="convertir.php" class="flex-1 text-center py-3 text-gray-400 font-medium">Voz</a>
    <a href="pdfafoto.php" class="flex-1 text-center py-3 text-gray-400 font-medium">Imagen</a>
  </nav>

  <!-- Footer -->
  <footer class="bg-gray-900 text-center text-gray-600 py-3 mt-auto"
          style="padding-bottom: env(safe-area-inset-bottom)">
    &copy; <?= date('Y') ?> AudioExtract Pro
  </footer>

  <script>
    document.getElementById('imgForm').addEventListener('submit', function() {
      document.getElementById('spinner').classList.remove('hidden');
    });
  </script>
</body>
</html>
