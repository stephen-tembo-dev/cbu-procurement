<x-layouts::app>
<x-slot:title>
  Confirm Password
</x-slot>

<div class="flex items-center justify-center h-full login-background-container">

  <x-ui.card class="w-xl space-y-8 p-8">
    <div class="flex items-center justify-center gap-2 flex-col">
      <img src="{{ asset('images/bp.png') }}" alt="Logo" class="w-15 h-auto">

      <x-ui.heading size="xl">{{ __('Secure Area') }}</x-ui.heading>
      <p class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Please confirm your password before continuing.') }}
      </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="mx-auto w-full space-y-4">
      @csrf

      <x-ui.field>
        <x-ui.label>{{ __('Password') }}</x-ui.label>
        <x-ui.input
          leftIcon="password"
          name="password"
          type="password"
          revealable
          placeholder="••••••••"
          required
          autocomplete="current-password"
        />
        <x-ui.error name="password" />
      </x-ui.field>

      <x-ui.button class="w-full" type="submit">
        {{ __('Confirm Password') }}
      </x-ui.button>

      @if (Route::has('password.request'))
        <x-ui.link variant="soft" href="{{ route('password.request') }}">
          {{ __('Forgot Your Password?') }}
        </x-ui.link>
      @endif
    </form>

    <p class="text-center text-xs text-neutral-400 dark:text-neutral-500 uppercase tracking-widest">
      {{ __('ATLAS Security Protocol') }}
    </p>
  </x-ui.card>
</div>

</x-layouts::app>