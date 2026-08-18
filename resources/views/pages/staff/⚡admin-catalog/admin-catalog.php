<?php

use App\Models\ItemCategory;
use App\Models\UnitOfMeasure;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $activeTab = 'categories';

    // ── Category state ───────────────────────────────────────────────────────
    public bool   $showCategoryModal    = false;
    public ?int   $editingCategoryId    = null;
    public string $categoryName         = '';
    public string $categoryDescription  = '';
    public bool   $categoryActive       = true;

    // ── Unit state ───────────────────────────────────────────────────────────
    public bool   $showUnitModal        = false;
    public ?int   $editingUnitId        = null;
    public string $unitName             = '';
    public string $unitAbbreviation     = '';
    public bool   $unitActive           = true;

    // ── Flash ────────────────────────────────────────────────────────────────
    public ?string $flash     = null;
    public string  $flashType = 'success';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
    }

    // ── Computed ─────────────────────────────────────────────────────────────

    #[Computed]
    public function categories(): \Illuminate\Support\Collection
    {
        return ItemCategory::orderBy('name')->get();
    }

    #[Computed]
    public function units(): \Illuminate\Support\Collection
    {
        return UnitOfMeasure::orderBy('name')->get();
    }

    // ── Category CRUD ────────────────────────────────────────────────────────

    public function openCreateCategory(): void
    {
        $this->editingCategoryId   = null;
        $this->categoryName        = '';
        $this->categoryDescription = '';
        $this->categoryActive      = true;
        $this->resetErrorBag();
        $this->showCategoryModal   = true;
    }

    public function openEditCategory(int $id): void
    {
        $cat = ItemCategory::findOrFail($id);
        $this->editingCategoryId   = $id;
        $this->categoryName        = $cat->name;
        $this->categoryDescription = $cat->description ?? '';
        $this->categoryActive      = $cat->is_active;
        $this->resetErrorBag();
        $this->showCategoryModal   = true;
    }

    public function saveCategory(): void
    {
        $this->validate([
            'categoryName' => [
                'required', 'string', 'max:100',
                $this->editingCategoryId
                    ? \Illuminate\Validation\Rule::unique('item_categories', 'name')->ignore($this->editingCategoryId)
                    : \Illuminate\Validation\Rule::unique('item_categories', 'name'),
            ],
            'categoryDescription' => 'nullable|string|max:255',
        ], ['categoryName.unique' => 'A category with this name already exists.']);

        $data = [
            'name'        => $this->categoryName,
            'description' => $this->categoryDescription ?: null,
            'is_active'   => $this->categoryActive,
        ];

        if ($this->editingCategoryId) {
            ItemCategory::findOrFail($this->editingCategoryId)->update($data);
            $this->flash = 'Category updated.';
        } else {
            ItemCategory::create($data);
            $this->flash = 'Category created.';
        }

        $this->flashType         = 'success';
        $this->showCategoryModal = false;
        unset($this->categories);
    }

    public function closeCategoryModal(): void
    {
        $this->showCategoryModal = false;
        $this->resetErrorBag();
    }

    public function toggleCategory(int $id): void
    {
        $cat = ItemCategory::findOrFail($id);
        $cat->update(['is_active' => ! $cat->is_active]);
        unset($this->categories);
        $this->flash     = $cat->is_active ? 'Category deactivated.' : 'Category activated.';
        $this->flashType = 'success';
    }

    // ── Unit CRUD ────────────────────────────────────────────────────────────

    public function openCreateUnit(): void
    {
        $this->editingUnitId    = null;
        $this->unitName         = '';
        $this->unitAbbreviation = '';
        $this->unitActive       = true;
        $this->resetErrorBag();
        $this->showUnitModal    = true;
    }

    public function openEditUnit(int $id): void
    {
        $unit = UnitOfMeasure::findOrFail($id);
        $this->editingUnitId    = $id;
        $this->unitName         = $unit->name;
        $this->unitAbbreviation = $unit->abbreviation;
        $this->unitActive       = $unit->is_active;
        $this->resetErrorBag();
        $this->showUnitModal    = true;
    }

    public function saveUnit(): void
    {
        $this->validate([
            'unitName'         => 'required|string|max:100',
            'unitAbbreviation' => 'required|string|max:20',
        ]);

        $data = [
            'name'         => $this->unitName,
            'abbreviation' => $this->unitAbbreviation,
            'is_active'    => $this->unitActive,
        ];

        if ($this->editingUnitId) {
            UnitOfMeasure::findOrFail($this->editingUnitId)->update($data);
            $this->flash = 'Unit updated.';
        } else {
            UnitOfMeasure::create($data);
            $this->flash = 'Unit created.';
        }

        $this->flashType     = 'success';
        $this->showUnitModal = false;
        unset($this->units);
    }

    public function closeUnitModal(): void
    {
        $this->showUnitModal = false;
        $this->resetErrorBag();
    }

    public function toggleUnit(int $id): void
    {
        $unit = UnitOfMeasure::findOrFail($id);
        $unit->update(['is_active' => ! $unit->is_active]);
        unset($this->units);
        $this->flash     = $unit->is_active ? 'Unit deactivated.' : 'Unit activated.';
        $this->flashType = 'success';
    }

    public function dismissFlash(): void
    {
        $this->flash = null;
    }
};
