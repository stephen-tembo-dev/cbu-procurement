<x-layouts::app>
<x-slot:title>
  Verify Email
</x-slot>

<div class="flex items-center justify-center h-full login-background-container">

  <x-ui.card class="w-xl space-y-8 p-8">
    <div class="flex items-center justify-center gap-2 flex-col">
      <img src="{{ asset('images/bp.png') }}" alt="Logo" class="w-15 h-auto">

      <x-ui.heading size="xl">{{ __('Verify Your Email') }}</x-ui.heading>
      <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center">
        {{ __('Check your inbox to activate your ATLAS account.') }}
      </p>
    </div>

    <div class="mx-auto w-full space-y-4">
      @if (session('resent'))
        <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800" role="alert">
          {{ __('A fresh verification link has been sent to your email address.') }}
        </div>
      @endif

      <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center leading-relaxed">
        {{ __('Before proceeding, please check your email for a verification link.') }}
        {{ __('If you did not receive the email, you can request another one below.') }}
      </p>

      <form method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <x-ui.button class="w-full" type="submit">
          {{ __('Resend Verification Email') }}
        </x-ui.button>
      </form>

      <div class="text-center">
        <a href="{{ route('logout') }}"
          onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
          <x-ui.link variant="soft">
            {{ __('Log Out') }}
          </x-ui.link>
        </a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
          @csrf
        </form>
      </div>
    </div>
  </x-ui.card>
</div>

</x-layouts::app>