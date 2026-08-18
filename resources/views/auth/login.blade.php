<x-layouts::app>
  <x-slot:title>
    Login
    </x-slot>

    <div class="flex items-center justify-center h-full login-background-container">

      <x-ui.card class="w-xl space-y-8 p-8">
        <div class="flex items-center justify-center gap-2 flex-col">
          <img src="{{ asset('images/bp.png') }}" alt="Logo" class="w-15 h-auto">

          <x-ui.heading size="xl">Log in to Purchase Manager</x-ui.heading>
        </div>

        <form action="{{ route('login') }}" method="POST" class="mx-auto w-full space-y-4">
          @csrf

          <div class="space-y-4">
            <x-ui.field>
              <x-ui.label>Email Address</x-ui.label>
              <x-ui.input leftIcon="user" name="email" type="text" placeholder="e.g., 2510001 or staff@cbu.ac.zm"
                :value="old('email')" required autofocus />
              <x-ui.error name="email" />
            </x-ui.field>

            <x-ui.field>
              <x-ui.label>Password</x-ui.label>
              <x-ui.input leftIcon="password" name="password" type="password" revealable placeholder="••••••••"
                required />
              <x-ui.error name="password" />
            </x-ui.field>
          </div>

          <div class="flex items-center justify-between">
            <x-ui.checkbox label="Remember me" name="remember" />
            <x-ui.link variant="soft" href="{{ route('password.request') }}" class="text-sm">
              Forgot password?
            </x-ui.link>
          </div>

          <x-ui.button class="w-full" type="submit">
            Log in
          </x-ui.button>

          <div class="flex flex-col items-center gap-2 mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
            <x-ui.link variant="soft" href="">
              Don't have an account?
              <span class="underline">Contact IT Support</span>
            </x-ui.link>
          </div>
        </form>
      </x-ui.card>
    </div>

</x-layouts::app>