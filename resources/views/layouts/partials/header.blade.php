
<header
  class="@auth lg:ms-65 @endauth fixed top-0 inset-x-0 flex flex-wrap md:justify-start md:flex-nowrap z-35 bg-white border-b border-gray-200 dark:bg-neutral-800 dark:border-neutral-700">
  <div class="flex justify-between xl:grid xl:grid-cols-3 basis-full items-center w-full py-2.5 px-2 sm:px-5">
    <div class="xl:col-span-1 flex items-center md:gap-x-3">
      @auth
        <div class="lg:hidden">
          <!-- Sidebar Toggle -->
          <button type="button"
            class="me-4 size-8 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-2xs hover:bg-gray-50 focus:outline-hidden focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
            aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-pro-sidebar" aria-label="Toggle navigation"
            data-hs-overlay="#hs-pro-sidebar">
            <x-ui.icon name="list" />
          </button>
          <!-- End Sidebar Toggle -->
        </div>
      @endauth

      @guest
        <a class="flex-none text-xl font-semibold dark:text-white" href="{{ route('home') }}" aria-label="Brand">
          <div class="flex items-center gap-x-2">
            <img src="{{ asset('images/bp.png') }}" class="h-10 w-auto" alt="Logo">
            <span class="text-black dark:text-blue-500"></span>
          </div>
        </a>
      @endguest

      @auth
        <span class="hidden lg:block text-sm font-semibold text-gray-700 dark:text-neutral-300">Purchase Manager</span>
      @endauth

    </div>

    <div class="xl:col-span-2 flex justify-end items-center gap-x-2">
      @guest
        {{-- <a class="inline-flex items-center text-gray-500 py-2 lg:px-2 text-sm font-medium rounded-lg hover:text-primary-600 focus:outline-hidden focus:text-primary-600 dark:text-neutral-200 dark:hover:text-neutral-400 dark:focus:text-neutral-400"
          href="{{ route('applicants.login') }}">
          <x-ui.icon name="user" />
          Apply Now
        </a> --}}

        <x-ui.button icon="user" href="#">Sign up</x-ui.button>
      @endguest

      {{-- Theme Switcher --}}
      <div class="hs-dropdown [--auto-close:inside] [--placement:bottom-right] relative inline-flex">
        <div class="hs-tooltip [--placement:bottom] inline-block">
          <button type="button"
            class="hs-dark-mode-active:hidden block hs-dark-mode hs-tooltip-toggle relative size-9.5 justify-center items-center gap-x-2 rounded-full border border-transparent text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
            data-hs-theme-click-value="dark">
            <span class="group inline-flex shrink-0 justify-center items-center size-7">
              <x-ui.icon name="sun" />
            </span>
          </button>

          <button type="button"
            class="hs-dark-mode-active:block hidden hs-dark-mode hs-tooltip-toggle relative size-9.5 justify-center items-center gap-x-2 rounded-full border border-transparent text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
            data-hs-theme-click-value="light">
            <span class="group inline-flex shrink-0 justify-center items-center size-7">
              <x-ui.icon name="moon" />
            </span>
          </button>

          <span
            class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 inline-block absolute invisible z-20 py-1.5 px-2.5 bg-gray-900 text-xs text-white rounded-lg dark:bg-neutral-700"
            role="tooltip" style="position: fixed; left: 1235.12px; top: 53px;" data-placement="bottom">
            Theme
          </span>
        </div>

        {{-- Theme Switcher End --}}

        @auth

            <!-- Notifications Button Icon -->
            <div class="hs-dropdown [--auto-close:inside] [--placement:bottom-right] relative inline-flex">
              <div class="hs-tooltip [--placement:bottom] inline-block">
                <button id="hs-pro-dnnd" type="button"
                  class="hs-tooltip-toggle relative size-9.5 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                  aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                  <x-ui.icon name="bell" />
                  <span class="flex absolute top-0 end-0 z-10 -mt-0.5 -me-0.5">
                    <span
                      class="animate-ping absolute inline-flex size-full rounded-full bg-red-400 opacity-75 dark:bg-red-600"></span>
                    <span
                      class="relative min-w-4.5 min-h-4.5 inline-flex justify-center items-center text-[10px] bg-red-500 text-white rounded-full px-1">
                      5
                    </span>
                  </span>
                </button>
                <span
                  class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 inline-block absolute invisible z-20 py-1.5 px-2.5 bg-gray-900 text-xs text-white rounded-lg dark:bg-neutral-700"
                  role="tooltip" style="position: fixed; left: 1235.12px; top: 53px;" data-placement="bottom">
                  Notifications
                </span>
              </div>
              <!-- End Notifications Button Icon -->

              <!-- Notifications Dropdown -->
              <div
                class="hs-dropdown-menu hs-dropdown-open:opacity-100 w-full sm:w-96 transition-[opacity,margin] duration opacity-0 hidden z-10 bg-white sm:border-t-0 sm:rounded-lg shadow-md sm:shadow-xl dark:bg-neutral-900 border border-neutral-100 dark:border-neutral-700"
                role="menu" aria-orientation="vertical" aria-labelledby="hs-pro-dnnd" tabindex="-1">
                <!-- Header -->
                <div class="px-5 pt-3 flex justify-between items-center border-b border-gray-200 dark:border-neutral-700">
                  <!-- Nav Tab -->
                  <nav class="flex gap-1" aria-label="Tabs" role="tablist" aria-orientation="horizontal">
                    <button type="button"
                      class="hs-tab-active:after:bg-gray-800 hs-tab-active:text-gray-800 px-2 py-1.5 mb-2 relative inline-flex justify-center items-center gap-x-2 hover:bg-gray-100 text-gray-500 hover:text-gray-800 text-sm rounded-lg disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 after:absolute after:-bottom-2 after:inset-x-2 after:z-10 after:h-0.5 after:pointer-events-none dark:hs-tab-active:text-neutral-200 dark:hs-tab-active:after:bg-neutral-400 dark:text-neutral-500 dark:hover:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden active"
                      id="hs-pro-tabs-dnn-item-all" aria-selected="true" data-hs-tab="#hs-pro-tabs-dnn-all"
                      aria-controls="hs-pro-tabs-dnn-all" role="tab">
                      New
                    </button>
                    <button type="button"
                      class="hs-tab-active:after:bg-gray-800 hs-tab-active:text-gray-800 px-2 py-1.5 mb-2 relative inline-flex justify-center items-center gap-x-2 hover:bg-gray-100 text-gray-500 hover:text-gray-800 text-sm rounded-lg disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 after:absolute after:-bottom-2 after:inset-x-2 after:z-10 after:h-0.5 after:pointer-events-none dark:hs-tab-active:text-neutral-200 dark:hs-tab-active:after:bg-neutral-400 dark:text-neutral-500 dark:hover:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden "
                      id="hs-pro-tabs-dnn-item-archived" aria-selected="false" data-hs-tab="#hs-pro-tabs-dnn-archived"
                      aria-controls="hs-pro-tabs-dnn-archived" role="tab">
                      Dismissed
                    </button>
                  </nav>
                  <!-- End Nav Tab -->

                  <!-- Notifications Button Icon -->
                  <div class="hs-tooltip relative inline-block">
                    <a class="hs-tooltip-toggle size-7 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800"
                      href="../../pro/dashboard/account-profile.html">
                      <x-ui.icon name="gear" />
                    </a>
                    <span
                      class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 inline-block absolute invisible z-20 py-1.5 px-2.5 bg-gray-900 text-xs text-white rounded-lg dark:bg-neutral-700"
                      role="tooltip" style="position: fixed; left: 0px; top: 5px;" data-placement="bottom-end">
                      Preferences
                    </span>
                  </div>
                  <!-- End Notifications Button Icon -->
                </div>
                <!-- End Header -->

                <!-- Tab Content -->
                <div id="hs-pro-tabs-dnn-all" role="tabpanel" aria-labelledby="hs-pro-tabs-dnn-item-all">
                  <div
                    class="h-120 overflow-y-auto overflow-hidden [&amp;::-webkit-scrollbar]:w-2 [&amp;::-webkit-scrollbar-thumb]:rounded-full [&amp;::-webkit-scrollbar-track]:bg-gray-100 [&amp;::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&amp;::-webkit-scrollbar-track]:bg-neutral-700 dark:[&amp;::-webkit-scrollbar-thumb]:bg-neutral-500">
                    <ul class="divide-y divide-gray-200 dark:divide-neutral-800">
                      <!-- List Item -->
                      <li class="relative group w-full flex gap-x-5 text-start bg-gray-100 dark:bg-neutral-800 p-5">
                        <div class="relative shrink-0">
                          <img class="shrink-0 h-9 w-auto rounded-full" src="{{ asset('images/bp.png') }}"
                            alt="Avatar">
                          <span class="absolute top-4 -start-3 size-2 bg-blue-600 rounded-full dark:bg-blue-500"></span>
                        </div>
                        <div class="grow">
                          <p class="text-xs text-gray-500 dark:text-neutral-500">
                            2 hours ago
                          </p>

                          <span class="block text-sm font-medium text-gray-800 dark:text-neutral-300">
                            Stephen Tembo <span class="font-normal">to</span> Staff
                          </span>
                          <p class="text-sm text-gray-500 dark:text-neutral-500">
                            Atlas is coming!
                          </p>
                        </div>

                        <div>
                          <div class="sm:group-hover:opacity-100 sm:opacity-0 sm:absolute sm:top-5 sm:end-5">
                            <!-- Segment Button Group -->
                            <div
                              class="inline-block p-0.5 bg-white border border-gray-200 rounded-lg shadow-2xs transition ease-out dark:bg-neutral-800 dark:border-neutral-700">
                              <div class="flex items-center">
                                <div class="hs-tooltip relative inline-block">
                                  <button type="button"
                                    class="hs-tooltip-toggle size-7 flex shrink-0 justify-center items-center text-gray-500 hover:bg-gray-100 hover:text-gray-800 rounded-sm disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:hover:bg-neutral-700 dark:text-neutral-400 dark:focus:bg-neutral-700">
                                    <x-ui.icon name="checks" />
                                  </button>
                                  <span
                                    class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 inline-block absolute invisible z-20 py-1.5 px-2.5 bg-gray-900 text-xs text-white rounded-lg dark:bg-neutral-700"
                                    role="tooltip" style="position: fixed; left: 0px; top: 5px;"
                                    daxplacement="bottom-end">
                                    Mark this notification as read
                                  </span>
                                </div>
                              </div>
                            </div>
                            <!-- End Segment Button Group -->
                          </div>
                        </div>
                      </li>
                      <!-- End List Item -->

                    </ul>
                    <!-- End List Group -->
                  </div>

                  <!-- Footer -->
                  <div class="text-center border-t border-gray-200 dark:border-neutral-800">
                    <a class="p-4 flex justify-center items-center gap-x-2 text-sm text-gray-500 font-medium sm:rounded-b-lg hover:text-blue-600 focus:outline-hidden focus:text-blue-600 dark:text-neutral-400 dark:hover:text-neutral-300 dark:focus:text-neutral-300"
                      href="../../docs/index.html">
                      <x-ui.icon name="checks" />
                      Mark all as read
                    </a>
                  </div>
                  <!-- End Footer -->
                </div>
                <!-- End Tab Content -->

                <!-- Tab Content -->
                <div id="hs-pro-tabs-dnn-archived" class="hidden" role="tabpanel"
                  aria-labelledby="hs-pro-tabs-dnn-item-archived">
                  <!-- Empty State -->
                  <div class="p-5 min-h-[533px] flex flex-col justify-center items-center text-center">
                    <x-ui.empty>
                      <x-ui.empty.media>
                        <x-ui.icon name="bell-slash" class="w-12 h-12 text-neutral-400" />
                      </x-ui.empty.media>
                      <x-ui.empty.contents>
                        <x-ui.heading size="sm">No archived notifications</x-ui.heading>
                        <x-ui.text size="sm" class="text-gray-500 dark:text-neutral-500">No data here yet. We will notify you when there's an update.</x-ui.text>
                      </x-ui.empty.contents>
                    </x-ui.empty>
                  </div>
                  <!-- End Empty State -->
                </div>
                <!-- End Tab Content -->
              </div>
            </div>
            <!-- End Notifications Dropdown -->

        @endauth

        @auth
          <div class="h-9.5 ml-3 flex items-center">
            <!-- Account Dropdown -->
            <div
              class="hs-dropdown inline-flex   [--strategy:absolute] [--auto-close:inside] [--placement:bottom-right] relative text-start">
              <button id="hs-dnad" type="button"
                class="inline-flex shrink-0 items-center gap-x-3 text-start rounded-full focus:outline-hidden"
                aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                <x-ui.avatar :name="auth()->user()->name" class="size-10" circle />
              </button>

              <!-- Account Dropdown -->
              <div
                class="hs-dropdown-menu hs-dropdown-open:opacity-100 w-60 transition-[opacity,margin] duration opacity-0 hidden z-20 bg-white rounded-xl shadow-xl dark:bg-neutral-900 border border-neutral-100 dark:border-neutral-700"
                role="menu" aria-orientation="vertical" aria-labelledby="hs-dnad" tabindex="-1">
                <div class="p-1 border-b border-gray-200 dark:border-neutral-700">
                  <a class="py-2 px-3 flex items-center gap-x-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800"
                    href="../../pro/dashboard/user-profile-my-profile.html">
                    <x-ui.avatar :name="auth()->user()->name" class="size-8" circle />

                    <div class="grow">
                      <span class="text-sm font-semibold text-gray-800 dark:text-neutral-300">
                        {{ auth()->user()->name }}
                      </span>
                      <p class="text-xs text-gray-500 dark:text-neutral-500">
                        {{ auth()->user()->email }}
                      </p>
                    </div>
                  </a>
                </div>


                  <div class="p-1 border-b border-gray-200 dark:border-neutral-800">
                    <a class="flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800"
                      href="#">
                      <x-ui.icon name="gear" />
                      Settings
                    </a>

                    <a class="flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800"
                      href="#">
                      <x-ui.icon name="user" />
                      My account
                    </a>
                  </div>

                {{-- <div class="px-4 py-3.5 border-y border-gray-200 dark:border-neutral-800">
                <!-- Switch/Toggle -->
                <div class="flex flex-wrap justify-between items-center gap-2">
                  <label for="hs-pro-dnaddm"
                    class="flex-1 cursor-pointer text-sm text-gray-800 dark:text-neutral-300">Dark mode</label>
                  <label for="hs-pro-dnaddm" class="relative inline-block w-11 h-6 cursor-pointer">
                    <input data-hs-theme-switch="" type="checkbox" id="hs-pro-dnaddm" class="peer sr-only">
                    <span
                      class="absolute inset-0 bg-gray-200 rounded-full transition-colors duration-200 ease-in-out peer-checked:bg-blue-600 dark:bg-neutral-700 dark:peer-checked:bg-blue-500 peer-disabled:opacity-50 peer-disabled:pointer-events-none"></span>
                    <span
                      class="absolute top-1/2 start-0.5 -translate-y-1/2 size-5 bg-white rounded-full shadow-sm !transition-transform duration-200 ease-in-out peer-checked:translate-x-full dark:bg-neutral-400 dark:peer-checked:bg-white"></span>
                  </label>
                </div>
                <!-- End Switch/Toggle -->
              </div> --}}

                <div class="p-1">

                    <form method="POST" action="{{ route('logout') }}">
                      @csrf

                      <button type="submit"
                        class="flex mt-0.5 gap-x-3 py-2 px-3 w-full rounded-lg text-sm text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800"
                        data-hs-overlay="#hs-pro-dasadam" aria-expanded="false">
                        <x-ui.icon name="sign-out" />
                        Logout
                      </button>
                    </form>


                </div>
              </div>
              <!-- End Account Dropdown -->
            </div>
            <!-- End Account Dropdown -->
          </div>
        @endauth
      </div>
    </div>
</header>
