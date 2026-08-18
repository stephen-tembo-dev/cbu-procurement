<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="theme-moon">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="{{ asset('images/bp.png') }}">
  <title>{{ env('APP_NAME', 'CBU Atlas') }} {{ !empty($title) ? '| ' . $title : '' }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
  @filamentStyles

  <script>
    const html = document.querySelector("html");

    const isLightOrAuto =
      localStorage.getItem("hs_theme") === "light" ||
      localStorage.getItem("hs_theme") === "default" ||
      !localStorage.getItem("hs_theme");

    const isDarkOrAuto = localStorage.getItem("hs_theme") === "dark";

    if (isLightOrAuto && html.classList.contains("dark"))
      html.classList.remove("dark");
    else if (isDarkOrAuto && html.classList.contains("light"))
      html.classList.remove("light");
    else if (isDarkOrAuto && !html.classList.contains("dark"))
      html.classList.add("dark");
    else if (isLightOrAuto && !html.classList.contains("light"))
      html.classList.add("light");
  </script>

  <style>
    .login-background-container {
      background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120"><g stroke="rgba(0, 132, 214, 0.05)" stroke-width="1.5" fill="none" stroke-linecap="square" stroke-linejoin="miter"><path d="M20 20h20v20h20"/><path d="M80 80h20v20"/><path d="M60 40v20h-20v20"/><path d="M100 20h-20v40h20"/><path d="M0 60h20v-20" opacity="0.5"/><path d="M60 120v-20h20" opacity="0.5"/><circle cx="60" cy="40" r="2.5" fill="rgba(0, 132, 214, 0.05)"/><circle cx="80" cy="80" r="2.5" fill="rgba(0, 132, 214, 0.05)"/><circle cx="20" cy="20" r="2.5" fill="rgba(0, 132, 214, 0.05)"/><circle cx="40" cy="80" r="2.5" fill="rgba(0, 132, 214, 0.05)"/><circle cx="80" cy="20" r="2.5" fill="rgba(0, 132, 214, 0.05)"/><circle cx="100" cy="60" r="2.5" fill="rgba(0, 132, 214, 0.05)"/></g></svg>');
      background-repeat: repeat;
      background-position: center;
    }
  </style>
</head>

<body class="bg-gray-50 dark:bg-neutral-900 h-screen overflow-x-hidden">

  {{-- Main Start --}}
  <main class="w-full h-full">
    <div class="max-w-7xl m-auto relative h-full">
      {{ $slot }}
    </div>
  </main>
  {{-- Main End --}}

  <x-ui.toast position="top-right" maxToasts="5" />

  @livewireScripts
  @livewire('notifications')
  @filamentScripts
</body>

</html>
