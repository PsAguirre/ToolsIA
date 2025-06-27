<?php
// fotoapdf.php

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

// Directorios de subida y salida
$uploadDir = __DIR__ . '/uploads';
$pdfDir    = __DIR__ . '/pdfs';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
if (!is_dir($pdfDir))    mkdir($pdfDir,    0777, true);

// Ejecutable de ImageMagick
define('MAGICK_CMD', 'C:/Program Files/ImageMagick-7.1.1-Q16-HDRI/magick.exe');

$error   = '';
$pdfFile = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar imágenes
    if (empty($_FILES['images']) || $_FILES['images']['error'][0] !== UPLOAD_ERR_OK) {
        $error = 'Selecciona al menos una imagen válida.';
    } else {
        $files = $_FILES['images'];
        $imgs  = [];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            $tmp   = $files['tmp_name'][$i];
            $fname = basename($files['name'][$i]);
            $safe  = preg_replace('/[^A-Za-z0-9_.-]/', '_', $fname);
            $dest  = "$uploadDir/" . uniqid() . "_" . $safe;
            if (move_uploaded_file($tmp, $dest)) {
                $imgs[] = $dest;
            }
        }
        if (empty($imgs)) {
            $error = 'No se subieron imágenes válidas.';
        }
    }

    // Crear PDF si no hay error
    if (!$error) {
        $baseName = 'images_' . date('Ymd_His') . '.pdf';
        $pdfFile  = "$pdfDir/" . $baseName;

        // Comando: magick convert img1 img2 ... output.pdf
        $cmd = escapeshellarg(MAGICK_CMD) . ' convert ';
        foreach ($imgs as $im) {
            $cmd .= escapeshellarg($im) . ' ';
        }
        $cmd .= escapeshellarg($pdfFile) . ' 2>&1';

        exec($cmd, $out, $code);
        foreach ($imgs as $im) @unlink($im);

        if ($code !== 0) {
            $error = 'Error generando PDF: ' . implode(' ', $out);
            $pdfFile = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="bg-gray-900 text-gray-200">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <title>AudioExtract Pro - Imagen a PDF</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>body{font-family:'Inter',sans-serif;}nav.fixed{padding-bottom:env(safe-area-inset-bottom)}</style>
</head>
<body class="flex flex-col min-h-screen">
  <!-- Header -->
  <header class="bg-gray-800 fixed top-0 inset-x-0 shadow z-40">
    <div class="max-w-md mx-auto flex items-center justify-between px-4 py-3">
      <h1 class="text-lg font-semibold text-gray-200">AudioExtract Pro</h1>
      <nav class="flex space-x-3">
        <a href="index.php" class="text-gray-400 hover:text-gray-200">Panel</a>
        <a href="convertir.php" class="text-gray-400 hover:text-gray-200">Voz</a>
        <a href="pdfafoto.php" class="text-gray-400 hover:text-gray-200">Imagen</a>
        <a href="fotoapdf.php" class="text-gray-100 font-semibold">PDF</a>
      </nav>
    </div>
  </header>

  <!-- Main -->
  <main class="flex-1 pt-20 pb-16 px-4 overflow-y-auto">
    <div class="max-w-md mx-auto bg-gray-800 p-6 rounded-xl shadow-lg">
      <h2 class="text-2xl font-semibold text-gray-200 mb-4 text-center">Convertir Imágenes a PDF</h2>

      <?php if ($error): ?>
        <div class="bg-red-700 text-white p-3 rounded mb-4 text-center">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php elseif ($pdfFile): ?>
        <div class="bg-gray-700 text-gray-100 p-3 rounded mb-4 text-center">
          PDF generado con éxito:
          <a href="<?= str_replace(__DIR__, '.', $pdfFile) ?>" download class="underline font-semibold">
            Descargar archivo
          </a>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" id="pdfForm" class="space-y-4">
        <input type="file" name="images[]" accept="image/*" multiple required
               class="block w-full bg-gray-700 text-gray-200 rounded p-2 border border-gray-600" />
        <button type="submit"
                class="w-full bg-gray-700 hover:bg-gray-600 text-gray-200 font-semibold py-3 rounded transition">
          Generar PDF
        </button>
      </form>
    </div>
  </main>

  <!-- Bottom Nav -->
  <nav class="fixed bottom-0 inset-x-0 bg-gray-800 border-t flex z-40" style="padding-bottom:env(safe-area-inset-bottom)">
    <a href="index.php" class="flex-1 text-center py-3 text-gray-400 hover:text-gray-200">Panel</a>
    <a href="convertir.php" class="flex-1 text-center py-3 text-gray-400 hover:text-gray-200">Voz</a>
    <a href="pdfafoto.php" class="flex-1 text-center py-3 text-gray-400 hover:text-gray-200">Imagen</a>
    <a href="fotoapdf.php" class="flex-1 text-center py-3 text-gray-400 hover:text-gray-200">PDF</a>
  </nav>

  <!-- Footer -->
  <footer class="bg-gray-900 text-center text-gray-600 py-3 mt-auto" style="padding-bottom:env(safe-area-inset-bottom)">
    &copy; <?= date('Y') ?> AudioExtract Pro
  </footer>

  <script>
    document.getElementById('pdfForm').addEventListener('submit', function() {
      // Aquí podrías mostrar un spinner si lo deseas
    });
  </script>
</body>
</html>
