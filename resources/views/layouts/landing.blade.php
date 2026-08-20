<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/png" href="{{ asset('images/bp.png') }}">
  <title>{{ config('app.name', 'Purchase Manager') }} — Copperbelt University</title>
  <meta name="description" content="The internal procurement system for Copperbelt University — requisitions, approvals, purchase orders, and budgets.">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles

  <script>
    const html = document.querySelector("html");
    const isLight = localStorage.getItem("hs_theme") === "light" ||
      localStorage.getItem("hs_theme") === "default" ||
      !localStorage.getItem("hs_theme");
    const isDark = localStorage.getItem("hs_theme") === "dark";
    if (isLight && html.classList.contains("dark")) html.classList.remove("dark");
    else if (isDark && html.classList.contains("light")) html.classList.remove("light");
    else if (isDark && !html.classList.contains("dark")) html.classList.add("dark");
    else if (isLight && !html.classList.contains("light")) html.classList.add("light");
  </script>

</head>

<body class="antialiased bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100">
  {{ $slot }}
  @livewireScripts
</body>

</html>
