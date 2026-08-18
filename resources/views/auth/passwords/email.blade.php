<x-layouts::app>
<x-slot:title>
  Reset Password
</x-slot>

<div class="flex items-center justify-center h-full login-background-container">

  <x-ui.card class="w-xl space-y-8 p-8">
    <div class="flex items-center justify-center gap-2 flex-col">
      <img src="{{ asset('images/bp.png') }}" alt="Logo" class="w-15 h-auto">

      <x-ui.heading size="xl">{{ __('Reset Password') }}</x-ui.heading>
      <p class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Enter your email to receive a password recovery link.') }}
      </p>
    </div>

    <div class="mx-auto w-full space-y-4">
      @if (session('status'))
        <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800" role="alert">
          {{ session('status') }}
        </div>
      @endif

      <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <x-ui.field>
          <x-ui.label>{{ __('Email Address') }}</x-ui.label>
          <x-ui.input
            leftIcon="at"
            name="email"
            type="email"
            value="{{ old('email') }}"
            placeholder="e.g. user@cbu.ac.zm"
            required
            autocomplete="email"
            autofocus
          />
          <x-ui.error name="email" />
        </x-ui.field>

        <x-ui.button class="w-full" type="submit">
          {{ __('Send Password Reset Link') }}
        </x-ui.button>

        <x-ui.link variant="soft" href="{{ route('login') }}">
          {{ __('Back to Login') }}
        </x-ui.link>
      </form>
    </div>
  </x-ui.card>
</div>

</x-layouts::app>