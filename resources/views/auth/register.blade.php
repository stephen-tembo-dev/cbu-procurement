<x-layouts::app>
<x-slot:title>
  Register
</x-slot>

<div class="flex items-center justify-center h-full login-background-container">

  <x-ui.card class="w-xl space-y-8 p-8">
    <div class="flex items-center justify-center gap-2 flex-col">
      <img src="{{ asset('images/bp.png') }}" alt="Logo" class="w-15 h-auto">

      <x-ui.heading size="xl">{{ __('Create Account') }}</x-ui.heading>
      <p class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Join the ATLAS student & staff community.') }}
      </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mx-auto w-full space-y-4">
      @csrf

      <div class="space-y-4">
        <x-ui.field>
          <x-ui.label>{{ __('Full Name') }}</x-ui.label>
          <x-ui.input
            name="name"
            type="text"
            value="{{ old('name') }}"
            placeholder="e.g. John Doe"
            required
            autocomplete="name"
            autofocus
          />
          <x-ui.error name="name" />
        </x-ui.field>

        <x-ui.field>
          <x-ui.label>{{ __('Email Address') }}</x-ui.label>
          <x-ui.input
            leftIcon="at"
            name="email"
            type="email"
            value="{{ old('email') }}"
            placeholder="user@cbu.ac.zm"
            required
            autocomplete="email"
          />
          <x-ui.error name="email" />
        </x-ui.field>

        <x-ui.field>
          <x-ui.label>{{ __('Password') }}</x-ui.label>
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
          <x-ui.label>{{ __('Confirm Password') }}</x-ui.label>
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
        {{ __('Register Account') }}
      </x-ui.button>

      <x-ui.link variant="soft" href="{{ route('login') }}">
        {{ __('Already have an account?') }}
        <span class="underline">{{ __('Sign In') }}</span>
      </x-ui.link>
    </form>
  </x-ui.card>
</div>

</x-layouts::app>