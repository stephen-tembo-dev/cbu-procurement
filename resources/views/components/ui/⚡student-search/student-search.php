<?php

use App\Services\Students\StudentService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

new class extends Component {
    public ?string $searchTerm = null;
    public ?Collection $students = null;

    protected StudentService $studentService;

    public function boot()
    {
        $this->studentService = app(StudentService::class);
    }

    public function closeSearch()
    {
        $this->searchTerm = null;
    }

    public function updatedSearchTerm($searchTerm)
    {
        $this->students = $this->studentService->searchQuery($searchTerm);
    }
};
