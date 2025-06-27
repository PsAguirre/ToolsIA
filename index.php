<?php
// index.php

// Página de inicio del Panel de Herramientas con mascota interactiva y traducción IA
?>
<!DOCTYPE html>
<html lang="es" class="bg-gray-900 text-gray-100">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>AudioExtract Pro - Panel de Herramientas</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
    .card { @apply bg-gray-800 rounded-lg shadow p-4 transition transform hover:-translate-y-1 hover:shadow-lg; }
    /* Mascota flotante */
    #mascota {
      position: fixed; bottom: 20px; right: 20px;
      width: 60px; height: 60px; cursor: pointer; z-index: 1000;
    }
    /* Chat box */
    #chatBox {
      position: fixed; bottom: 90px; right: 20px;
      width: 280px; max-height: 400px;
      background: #1F2937; border: 1px solid #374151;
      border-radius: 8px; display: none; flex-direction: column;
      overflow: hidden; z-index: 1000; box-shadow: 0 4px 16px rgba(0,0,0,0.5);
    }
    #chatHeader {
      background: #374151; padding: 6px; display: flex;
      align-items: center; justify-content: space-between;
      font-weight: 600; font-size: 0.875rem; color: #F3F4F6;
    }
    #chatHeader select {
      background: #4B5563; color: #F3F4F6;
      border: none; border-radius: 4px;
      padding: 2px 4px; font-size: 0.75rem;
    }
    #chatLog {
      flex: 1; padding: 6px; overflow-y: auto;
      font-size: 0.875rem; color: #E5E7EB;
    }
    #chatInput {
      display: flex; border-top: 1px solid #374151;
    }
    #chatInput input {
      flex: 1; padding: 6px; background: #1F2937;
      color: #F9FAFB; border: none; outline: none;
      font-size: 0.875rem;
    }
    #chatInput button {
      padding: 6px 10px; background: #4F46E5;
      color: white; border: none; cursor: pointer;
      font-size: 0.875rem;
    }
    nav.fixed { padding-bottom: env(safe-area-inset-bottom); }
  </style>
</head>
<body class="flex flex-col min-h-screen">
  <!-- Header -->
  <header class="bg-gray-800 text-gray-100 shadow-md fixed inset-x-0 top-0 z-50">
    <div class="max-w-5xl mx-auto flex items-center justify-between px-4 py-3">
      <div class="flex items-center space-x-2">
        <div class="text-indigo-400 font-bold text-xl">AE</div>
        <h1 class="text-lg font-semibold">AudioExtract Pro</h1>
      </div>
      <nav class="hidden md:flex space-x-6">
        <a href="convertir.php" class="hover:text-indigo-400">Voz</a>
        <a href="pdfafoto.php" class="hover:text-indigo-400">Imagen</a>
        <a href="fotoapdf.php" class="hover:text-indigo-400">PDF</a>
      </nav>
      <button id="mobileMenuBtn" class="md:hidden focus:outline-none">
        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>
    <nav id="mobileMenu" class="hidden bg-gray-800 border-t border-gray-700">
      <a href="convertir.php" class="block px-4 py-2 hover:bg-gray-700">Convertir Voz</a>
      <a href="pdfafoto.php" class="block px-4 py-2 hover:bg-gray-700">PDF a Imagen</a>
      <a href="fotoapdf.php" class="block px-4 py-2 hover:bg-gray-700">Imagen a PDF</a>
    </nav>
  </header>

  <!-- Main Content -->
  <main class="flex-1 pt-20 pb-16 px-4 max-w-5xl mx-auto">
    <h2 class="text-2xl font-semibold text-center mb-6">Panel de Herramientas</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Card: Convertir PDF a Voz -->
      <a href="convertir.php" class="card flex flex-col items-center text-center">
        <div class="bg-indigo-700 p-3 rounded-full mb-3">
          <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 17v-5l-2 2m0-4v6l2-2m4 3v-5l-2 2m0-4v6l2-2m4 1v-6l-2 2m0-4v6l2-2" />
          </svg>
        </div>
        <h3 class="font-semibold mb-1">Convertir PDF a Voz</h3>
        <p class="text-gray-400 text-sm">Genera audio en español desde PDF.</p>
      </a>

      <!-- Card: Convertir PDF a Imagen -->
      <a href="pdfafoto.php" class="card flex flex-col items-center text-center">
        <div class="bg-indigo-700 p-3 rounded-full mb-3">
          <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4h16v16H4V4z M4 8h16M8 4v16" />
          </svg>
        </div>
        <h3 class="font-semibold mb-1">Convertir PDF a Imagen</h3>
        <p class="text-gray-400 text-sm">Cada página de tu PDF como imagen.</p>
      </a>

      <!-- Card: Convertir Imagen a PDF -->
      <a href="fotoapdf.php" class="card flex flex-col items-center text-center">
        <div class="bg-indigo-700 p-3 rounded-full mb-3">
          <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4h16v16H4V4z M4 9h16M9 4v16" />
          </svg>
        </div>
        <h3 class="font-semibold mb-1">Convertir Imagen a PDF</h3>
        <p class="text-gray-400 text-sm">Combina imágenes en un PDF.</p>
      </a>

      <!-- Placeholder: Próxima Herramienta -->
      <div class="card opacity-50 cursor-not-allowed flex flex-col items-center text-center">
        <div class="bg-gray-700 p-3 rounded-full mb-3">
          <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 8h18M3 12h18M3 16h18" />
          </svg>
        </div>
        <h3 class="font-semibold mb-1 text-gray-400">Próxima Herramienta</h3>
        <p class="text-gray-500 text-sm">Disponible pronto.</p>
      </div>
    </div>
  </main>

  <!-- Mascota -->
  <img id="mascota" src="mascota.png" alt="Mascota" title="Hazme una traducción inglés↔español">

  <!-- Chat Box -->
  <div id="chatBox" class="flex flex-col">
    <div id="chatHeader">
      Diccionario
      <select id="langSelect">
        <option value="en2es">EN→ES</option>
        <option value="es2en">ES→EN</option>
      </select>
    </div>
    <div id="chatLog"></div>
    <div id="chatInput">
      <input id="chatText" type="text" placeholder="Escribe palabra...">
      <button id="chatSend">Enviar</button>
    </div>
  </div>

  <!-- Bottom Nav -->
  <nav class="fixed bottom-0 inset-x-0 bg-gray-800 border-t flex z-50">
    <a href="convertir.php" class="flex-1 py-3 text-center hover:bg-gray-700">Voz</a>
    <a href="pdfafoto.php" class="flex-1 py-3 text-center hover:bg-gray-700">Imagen</a>
    <a href="fotoapdf.php" class="flex-1 py-3 text-center hover:bg-gray-700">PDF</a>
  </nav>

  <footer class="mt-auto text-center text-gray-500 text-xs py-2">&copy; <?= date('Y') ?> AudioExtract Pro</footer>

  <script>
    // Toggle mobile menu
    document.getElementById('mobileMenuBtn').addEventListener('click', () => {
      document.getElementById('mobileMenu').classList.toggle('hidden');
    });
    // Mascota & Chat toggle
    const mascota = document.getElementById('mascota');
    const chatBox = document.getElementById('chatBox');
    const chatLog = document.getElementById('chatLog');
    const chatText = document.getElementById('chatText');
    const chatSend = document.getElementById('chatSend');
    const langSelect = document.getElementById('langSelect');
    chatBox.style.display = 'none';
    mascota.addEventListener('click', () => {
      chatBox.style.display = (chatBox.style.display === 'none') ? 'flex' : 'none';
      chatText.focus();
    });
    chatSend.addEventListener('click', () => {
      const word = chatText.value.trim(); if (!word) return;
      const lang = langSelect.value;
      fetch(`dictionary.php?word=${encodeURIComponent(word)}&lang=${lang}`)
        .then(res => res.json())
        .then(data => {
          const entry = document.createElement('div');
          const trans = data.translation || '⚠️ No encontrado';
          entry.innerHTML = `<span class="block text-indigo-300"><strong>${data.word}</strong> → ${trans}</span>`;
          chatLog.appendChild(entry);
          chatLog.scrollTop = chatLog.scrollHeight;
          chatText.value = '';
          chatText.focus();
        });
    });
    chatText.addEventListener('keyup', e => { if (e.key === 'Enter') chatSend.click(); });
  </script>
</body>
</html>
