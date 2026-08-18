<div class="min-h-screen bg-primary-50 dark:bg-primary-950">

  <style>
    ::selection { background-color: var(--color-primary-300); color: var(--color-primary-950); }
    .dark ::selection { background-color: var(--color-primary-700); color: var(--color-primary-50); }
    a, button { text-underline-offset: 3px; }
    :focus-visible { outline: 2px solid var(--color-primary-500); outline-offset: 2px; }
  </style>

  {{-- ================================================================ --}}
  {{-- NAVIGATION --}}
  {{-- ================================================================ --}}
  <nav
    x-data="{
      mobileOpen: false,
      dark: document.documentElement.classList.contains('dark'),
      toggleDark() {
        this.dark = !this.dark;
        const h = document.documentElement;
        h.classList.toggle('dark', this.dark);
        h.classList.toggle('light', !this.dark);
        localStorage.setItem('hs_theme', this.dark ? 'dark' : 'light');
      }
    }"
    class="fixed top-0 inset-x-0 z-50 backdrop-blur-xl bg-primary-50/90 dark:bg-primary-950/90 border-b border-primary-200/60 dark:border-primary-800/40"
  >
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">

        {{-- Mark --}}
        <a href="{{ url('/') }}" class="flex items-center gap-3">
          <img
            src="{{ asset('images/bp.png') }}"
            alt="Copperbelt University crest"
            class="h-9 w-auto"
          >
          <div class="hidden sm:block leading-tight">
            <div class="font-bold text-sm text-primary-950 dark:text-primary-50 tracking-tight">Copperbelt University</div>
            <div class="text-[11px] text-primary-600 dark:text-primary-400 tracking-wide">Procurement System</div>
          </div>
        </a>

        {{-- Desktop actions --}}
        <div class="hidden sm:flex items-center gap-2">
          <button
            type="button"
            @click="toggleDark()"
            class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-primary-500 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900 transition-colors"
            aria-label="Toggle dark mode"
          >
            <ph-moon weight="fill" class="text-lg" x-show="!dark"></ph-moon>
            <ph-sun weight="fill" class="text-lg" x-show="dark"></ph-sun>
          </button>

          <a
            href="{{ route('login') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary-700 hover:bg-primary-800 text-white text-sm font-semibold transition-colors"
          >
            Sign In
          </a>
        </div>

        {{-- Mobile menu button --}}
        <button
          type="button"
          @click="mobileOpen = !mobileOpen"
          class="sm:hidden w-9 h-9 inline-flex items-center justify-center rounded-lg text-primary-700 dark:text-primary-300 hover:bg-primary-100 dark:hover:bg-primary-900 transition-colors"
          aria-label="Open menu"
        >
          <ph-list weight="bold" class="text-xl" x-show="!mobileOpen"></ph-list>
          <ph-x weight="bold" class="text-xl" x-show="mobileOpen"></ph-x>
        </button>

      </div>
    </div>

    {{-- Mobile menu --}}
    <div
      x-show="mobileOpen"
      x-cloak
      class="sm:hidden border-t border-primary-200 dark:border-primary-800 bg-primary-50 dark:bg-primary-950 px-4 py-4 space-y-3"
    >
      <div class="flex items-center justify-between pb-3 border-b border-primary-100 dark:border-primary-900">
        <span class="text-sm text-primary-600 dark:text-primary-400 font-medium">Toggle theme</span>
        <button
          type="button"
          @click="toggleDark()"
          class="w-9 h-9 inline-flex items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300"
        >
          <ph-moon weight="fill" class="text-base" x-show="!dark"></ph-moon>
          <ph-sun weight="fill" class="text-base" x-show="dark"></ph-sun>
        </button>
      </div>
      <a
        href="{{ route('login') }}"
        class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-semibold transition-colors"
      >
        Sign In
      </a>
    </div>
  </nav>

  {{-- ================================================================ --}}
  {{-- HERO --}}
  {{-- ================================================================ --}}
  <section class="relative flex items-center justify-center overflow-hidden pt-16 min-h-[92vh] bg-primary-950">

    {{-- Quiet vignette, no ornament --}}
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--color-primary-900)_0%,_var(--color-primary-950)_70%)]"></div>

    <div
      x-data="{ shown: false }"
      x-init="requestAnimationFrame(() => shown = true)"
      :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-3'"
      class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center py-24 transition-all duration-700 ease-out"
    >
      <img
        src="{{ asset('images/bp.png') }}"
        alt="Copperbelt University crest"
        class="h-24 w-auto mx-auto mb-10"
      >

      <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-[1.15] mb-6 tracking-tight text-balance">
        The procurement system for Copperbelt University
      </h1>

      <p class="text-lg text-primary-200 max-w-xl mx-auto mb-10 leading-relaxed text-balance">
        Requisitions, approvals, purchase orders, and budgets — one internal system for every department on campus.
      </p>

      <a
        href="{{ route('login') }}"
        class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-white hover:bg-primary-100 text-primary-900 font-semibold text-base transition-colors"
      >
        <ph-sign-in weight="bold"></ph-sign-in>
        Sign In
      </a>
    </div>
  </section>

  {{-- ================================================================ --}}
  {{-- WHAT IT HANDLES --}}
  {{-- ================================================================ --}}
  <section class="py-20 sm:py-28">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

      <h2 class="text-2xl sm:text-3xl font-bold text-primary-950 dark:text-primary-50 mb-12 tracking-tight">
        What the system handles
      </h2>

      <dl class="divide-y divide-primary-200 dark:divide-primary-800">

        <div class="grid grid-cols-1 sm:grid-cols-[10rem_1fr] gap-x-8 gap-y-2 py-6">
          <dt class="font-semibold text-primary-900 dark:text-primary-100">Requisitions</dt>
          <dd class="text-primary-600 dark:text-primary-400 leading-relaxed">Staff submit purchase requests with department, budget code, and justification, routed automatically to the right approver.</dd>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-[10rem_1fr] gap-x-8 gap-y-2 py-6">
          <dt class="font-semibold text-primary-900 dark:text-primary-100">Approvals</dt>
          <dd class="text-primary-600 dark:text-primary-400 leading-relaxed">Multi-level approval chains by department, role, and spend threshold, with notifications at every step.</dd>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-[10rem_1fr] gap-x-8 gap-y-2 py-6">
          <dt class="font-semibold text-primary-900 dark:text-primary-100">Purchase orders</dt>
          <dd class="text-primary-600 dark:text-primary-400 leading-relaxed">Generated from approved requisitions and tracked through to delivery.</dd>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-[10rem_1fr] gap-x-8 gap-y-2 py-6">
          <dt class="font-semibold text-primary-900 dark:text-primary-100">Budgets</dt>
          <dd class="text-primary-600 dark:text-primary-400 leading-relaxed">Real-time commitment tracking against departmental cost centres, so overspending is caught before it happens.</dd>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-[10rem_1fr] gap-x-8 gap-y-2 py-6">
          <dt class="font-semibold text-primary-900 dark:text-primary-100">Suppliers</dt>
          <dd class="text-primary-600 dark:text-primary-400 leading-relaxed">One directory for vendor details, quotes, and contract history.</dd>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-[10rem_1fr] gap-x-8 gap-y-2 py-6">
          <dt class="font-semibold text-primary-900 dark:text-primary-100">Audit trail</dt>
          <dd class="text-primary-600 dark:text-primary-400 leading-relaxed">Every action logged with user, timestamp, and change history.</dd>
        </div>

      </dl>
    </div>
  </section>

  {{-- ================================================================ --}}
  {{-- FOOTER --}}
  {{-- ================================================================ --}}
  <footer class="border-t border-primary-200 dark:border-primary-800">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-6">

        <div class="flex items-center gap-3">
          <img src="{{ asset('images/bp.png') }}" alt="Copperbelt University crest" class="h-8 w-auto">
          <div class="leading-tight">
            <div class="font-semibold text-primary-950 dark:text-primary-50 text-sm">Copperbelt University</div>
            <div class="text-primary-500 dark:text-primary-500 text-xs">Procurement System</div>
          </div>
        </div>

        <a href="{{ route('login') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-200 transition-colors">Sign In</a>

        <p class="text-primary-500 text-xs">
          &copy; {{ date('Y') }} Copperbelt University
        </p>

      </div>
    </div>
  </footer>

</div>
