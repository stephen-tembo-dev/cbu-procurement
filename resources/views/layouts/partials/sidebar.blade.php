@php $user = auth()->user(); @endphp

<aside id="hs-pro-sidebar"
  class="hs-overlay [--auto-close:lg]
    hs-overlay-open:translate-x-0
    -translate-x-full transition-all duration-300 transform
    w-65 h-full
    hidden
    fixed inset-y-0 inset-s-0 z-35
    bg-white border-e border-gray-200
    lg:block lg:translate-x-0 lg:end-auto lg:bottom-0
    dark:bg-neutral-800 dark:border-neutral-700"
  tabindex="-1" aria-label="Sidebar">
  <div class="relative flex flex-col h-full max-h-full pt-3">
    <header class="h-11.5 ps-5 pe-2 lg:ps-8 flex items-center gap-x-1 mb-4">
      <a class="flex-none rounded-md flex items-center gap-2 font-semibold focus:outline-hidden focus:opacity-80"
        href="{{ route('dashboard') }}" aria-label="Purchase Manager">
        <img class="w-8 h-auto" src="{{ asset('images/bp.png') }}" alt="">
        <span class="text-md text-gray-800 dark:text-neutral-300">Purchase Manager</span>
      </a>

      <div class="lg:hidden ms-auto">
        <button type="button"
          class="size-8 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 focus:outline-hidden focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
          data-hs-overlay="#hs-pro-sidebar" aria-expanded="false">
          <x-ui.icon name="x" />
        </button>
      </div>
    </header>

    <div class="mt-1.5 h-full overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">
      <nav class="hs-accordion-group pb-3 w-full flex flex-col flex-wrap" data-hs-accordion-always-open="">
        <ul class="flex flex-col gap-y-1 px-3">

          {{-- Dashboard --}}
          <x-ui.sidebar.link title="Dashboard" href="{{ route('dashboard') }}" icon="house" :active="request()->routeIs('dashboard')" :visible="true" />

          {{-- My Requisitions --}}
          @can('pr.view_own')
          <x-ui.sidebar.link title="My Requisitions" href="{{ route('requisitions.index') }}" icon="clipboard-text" :active="request()->routeIs('requisitions.index') && request()->query('scope') !== 'all'" :visible="true" />
          @endcan

          {{-- New Requisition --}}
          @can('pr.create')
          <x-ui.sidebar.link title="New Requisition" href="{{ route('requisitions.create') }}" icon="plus-circle" :active="request()->routeIs('requisitions.create')" :visible="true" />
          @endcan

          {{-- Approvals section --}}
          @if($user->hasAnyRole(['hod', 'stores', 'bursar', 'vc', 'procurement', 'auditor', 'admin']))
          <li class="pt-3 pb-1 px-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-neutral-500">Approvals</span>
          </li>

          @can('pr.view_all')
          <x-ui.sidebar.link title="All Requisitions" href="{{ route('requisitions.index') }}?scope=all" icon="list-bullets" :active="request()->routeIs('requisitions.index') && request()->query('scope') === 'all'" :visible="true" />
          @endcan

          @if($user->hasAnyRole(['procurement', 'stores', 'bursar', 'vc', 'auditor', 'admin']))
          <x-ui.sidebar.link title="Purchase Orders" href="{{ route('po.index') }}" icon="receipt" :active="request()->routeIs('po.*')" :visible="true" />
          @endif

          @can('reports.view')
          <x-ui.sidebar.link title="Reports" href="{{ route('reports') }}" icon="chart-bar" :active="request()->routeIs('reports')" :visible="true" />
          @endcan

          @if($user->hasAnyRole(['admin', 'bursar', 'vc', 'procurement', 'auditor']))
          <x-ui.sidebar.link title="Budget Dashboard" href="{{ route('budget') }}" icon="currency-circle-dollar" :active="request()->routeIs('budget')" :visible="true" />
          @endif
          @endif

          {{-- Management section (stores, procurement, admin) --}}
          @if($user->can('stock.manage') || $user->can('suppliers.manage'))
          <li class="pt-3 pb-1 px-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-neutral-500">Management</span>
          </li>

          @can('stock.manage')
          <x-ui.sidebar.link title="Stock Items" href="{{ route('admin.stock-items') }}" icon="package" :active="request()->routeIs('admin.stock-items')" :visible="true" />
          @endcan

          @can('suppliers.manage')
          <x-ui.sidebar.link title="Suppliers" href="{{ route('admin.suppliers') }}" icon="truck" :active="request()->routeIs('admin.suppliers')" :visible="true" />
          @endcan
          @endif

          {{-- Admin section --}}
          @if($user->hasRole('admin'))
          <li class="pt-3 pb-1 px-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-neutral-500">Admin</span>
          </li>

          <x-ui.sidebar.link title="Users" href="{{ route('admin.users') }}" icon="users" :active="request()->routeIs('admin.users')" :visible="true" />
          <x-ui.sidebar.link title="Departments" href="{{ route('admin.departments') }}" icon="buildings" :active="request()->routeIs('admin.departments')" :visible="true" />
          <x-ui.sidebar.link title="Budget" href="{{ route('admin.budget') }}" icon="currency-dollar" :active="request()->routeIs('admin.budget')" :visible="true" />
          <x-ui.sidebar.link title="HOD Delegations" href="{{ route('admin.hod-delegations') }}" icon="user-switch" :active="request()->routeIs('admin.hod-delegations')" :visible="true" />
          <x-ui.sidebar.link title="Catalog" href="{{ route('admin.catalog') }}" icon="tag" :active="request()->routeIs('admin.catalog')" :visible="true" />
          @endif

        </ul>
      </nav>
    </div>

    {{-- User footer --}}
    <div class="border-t border-gray-200 dark:border-neutral-700 p-4">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center flex-shrink-0">
          <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ Str::upper(Str::substr($user->name, 0, 1)) }}</span>
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-sm font-medium text-gray-800 dark:text-neutral-200 truncate">{{ $user->name }}</p>
          <p class="text-xs text-gray-500 dark:text-neutral-400 truncate">{{ $user->getRoleNames()->first() ?? 'Staff' }}</p>
        </div>
        <a href="{{ route('logout') }}"
          onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-200 transition-colors"
          title="Sign out">
          <ph-sign-out class="text-lg"></ph-sign-out>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
          @csrf
        </form>
      </div>
    </div>
  </div>
</aside>
