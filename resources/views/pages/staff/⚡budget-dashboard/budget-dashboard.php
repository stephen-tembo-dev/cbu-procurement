<?php

use App\Models\Finance\BudgetAllocation;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public int $fiscalYear;

    public function mount(): void
    {
        abort_unless(
            auth()->user()->hasAnyRole(['admin', 'bursar', 'vc', 'procurement', 'auditor']),
            403
        );

        $this->fiscalYear = (int) date('Y');
    }

    #[Computed]
    public function allocations(): \Illuminate\Support\Collection
    {
        return BudgetAllocation::with('costCentre.department')
            ->where('fiscal_year', $this->fiscalYear)
            ->get()
            ->sortBy([
                ['costCentre.department.name', 'asc'],
                ['costCentre.name', 'asc'],
            ]);
    }

    #[Computed]
    public function summary(): array
    {
        $all = $this->allocations;

        return [
            'allocated' => $all->sum('allocated_amount'),
            'committed' => $all->sum('committed_amount'),
            'spent'     => $all->sum('spent_amount'),
            'available' => $all->sum(fn ($a) => $a->availableBalance()),
            'count'     => $all->count(),
        ];
    }

    #[Computed]
    public function byDepartment(): \Illuminate\Support\Collection
    {
        return $this->allocations
            ->groupBy(fn ($a) => $a->costCentre->department->name ?? 'Unassigned')
            ->map(fn ($group, $dept) => [
                'department' => $dept,
                'centres'    => $group->count(),
                'allocated'  => $group->sum('allocated_amount'),
                'committed'  => $group->sum('committed_amount'),
                'spent'      => $group->sum('spent_amount'),
                'available'  => $group->sum(fn ($a) => $a->availableBalance()),
            ])
            ->sortByDesc('allocated')
            ->values();
    }

    public function availableYears(): array
    {
        return range((int) date('Y') - 3, (int) date('Y') + 1);
    }
};
