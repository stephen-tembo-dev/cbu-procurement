<div class="relative flex items-center z-50 xl:w-md">
  <x-ui.input placeholder="Find a student" class="mx-auto" leftIcon="magnifying-glass"
    wire:model.live.debounce.500ms="searchTerm" wire:keydown.esc="closeSearch" />

  <x-ui.icon wire:loading name="spinner"
    class="text-primary animate-spin size-5 absolute top-[50%] translate-y-[-50%] right-4" />

  @if ($searchTerm)
    <div
      class="bg-white dark:bg-neutral-800 border border-black/10 dark:border-white/10 rounded-xl shadow-lg absolute w-full top-12 left-[50%] translate-x-[-50%] max-w-md z-50 overflow-hidden">
      <ul class="divide-y divide-black/5 dark:divide-white/5">
        @if ($students->isNotEmpty())
          @foreach ($students as $student)
            <li class="p-1">
              <a href="{{ route('students.profile.index', $student->id) }}"
                class="block p-2 hover:bg-black/5 dark:hover:bg-white/5 transition-colors rounded-lg">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-4 grow">
                    <x-ui.avatar size="lg" :name="$student->user->name" circle />
                    <div>
                      <div class="font-semibold text-gray-900 dark:text-white">{{ $student->user->name }}</div>
                      <div class="text-sm text-gray-500 dark:text-neutral-400">{{ $student->id }}</div>
                    </div>
                  </div>

                  <div class="text-sm text-gray-400 dark:text-neutral-500 flex flex-col">
                    <span>{{ $student->program?->code }}</span>
                    <span>{{ $student->campus?->name }}</span>
                  </div>
                </div>
              </a>
            </li>
          @endforeach
        @else
          <li class="p-4 text-center">
            <div class="text-gray-500 dark:text-neutral-400">No students found</div>
          </li>
        @endif
      </ul>
    </div>
  @endif
</div>
