<x-layouts::app>
<x-slot:title>
  Reset Password
</x-slot>

<div class="flex items-center justify-center h-full login-background-container">

  <x-ui.card class="w-xl space-y-8 p-8">
    <div class="flex items-center justify-center gap-2 flex-col">
      <img src="{{ asset('images/bp.png') }}" alt="Logo" class="w-15 h-auto">

      <x-ui.heading size="xl">{{ __('Set New Password') }}</x-ui.heading>
      <p class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Please choose a strong password to secure your account.') }}
      </p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="mx-auto w-full space-y-4">
      @csrf

      <input type="hidden" name="token" value="{{ $token }}">

      <div class="space-y-4">
        <x-ui.field>
          <x-ui.label>{{ __('Email Address') }}</x-ui.label>
          <x-ui.input
            leftIcon="at"
            name="email"
            type="email"
            value="{{ $email ?? old('email') }}"
            required
            autocomplete="email"
            autofocus
          />
          <x-ui.error name="email" />
        </x-ui.field>

        <x-ui.field>
          <x-ui.label>{{ __('New Password') }}</x-ui.label>
          <x-ui.input
            leftIcon="password"
            name="password"
            type="password"
            revealable
            placeholder="••••••••"
            required
            autocomplete="new-password"
          />
          <x-ui.error name="password" />
        </x-ui.field>

        <x-ui.field>
          <x-ui.label>{{ __('Confirm New Password') }}</x-ui.label>
          <x-ui.input
            leftIcon="password"
            name="password_confirmation"
            type="password"
            revealable
            placeholder="••••••••"
            required
            autocomplete="new-password"
          />
        </x-ui.field>
      </div>

      <x-ui.button class="w-full" type="submit">
        {{ __('Reset Password') }}
      </x-ui.button>
    </form>

    <p class="text-center text-xs text-neutral-400 dark:text-neutral-500 uppercase tracking-widest">
      {{ __('ATLAS Security Protocol') }}
    </p>
  </x-ui.card>
</div>

</x-layouts::app>