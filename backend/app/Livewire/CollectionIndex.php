<?php

namespace App\Livewire;

use App\Models\CollectionCategory;
use App\Models\CollectionWork;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CollectionIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $type = 'all';

    public ?string $status = null;

    public ?int $yearMin = null;

    public ?int $yearMax = null;

    public ?string $season = null;

    public array $categoryIds = [];

    public string $categoryMode = 'or';

    public ?int $ratingMin = null;

    public ?int $ratingMax = null;

    public bool $favoriteOnly = false;

    #[Url]
    public string $search = '';

    public string $sort = 'newest';

    #[Url]
    public string $view = 'card';

    public function updated(): void
    {
        $this->resetPage();
    }

    #[On('collection-saved')]
    public function onSaved(): void
    {
        // Presence of this handler re-renders the component after a save.
    }

    #[On('collection-deleted')]
    public function onDeleted(): void
    {
        // Presence of this handler re-renders the component after a delete.
    }

    public function getWorksProperty(): LengthAwarePaginator
    {
        $query = CollectionWork::with('categories');

        if ($this->type !== 'all') {
            $query->where('type', $this->type);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->yearMin !== null) {
            $query->where('release_year', '>=', $this->yearMin);
        }

        if ($this->yearMax !== null) {
            $query->where('release_year', '<=', $this->yearMax);
        }

        if ($this->season) {
            $query->where('release_season', $this->season);
        }

        if ($this->favoriteOnly) {
            $query->where('is_favorite', true);
        }

        if ($this->ratingMin !== null) {
            $query->where('rating', '>=', $this->ratingMin);
        }

        if ($this->ratingMax !== null) {
            $query->where('rating', '<=', $this->ratingMax);
        }

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('title_original', 'like', "%{$this->search}%");
            });
        }

        if ($this->categoryIds) {
            if ($this->categoryMode === 'and') {
                foreach ($this->categoryIds as $cid) {
                    $query->whereHas('categories', fn ($c) => $c->where('collection_categories.id', $cid));
                }
            } else {
                $query->whereHas('categories', fn ($c) => $c->whereIn('collection_categories.id', $this->categoryIds));
            }
        }

        match ($this->sort) {
            'rating' => $query->orderByDesc('rating'),
            'year' => $query->orderByDesc('release_year'),
            'title' => $query->orderBy('title'),
            default => $query->orderByDesc('created_at'),
        };

        return $query->paginate(24);
    }

    public function getStatsProperty(): array
    {
        return [
            'total' => CollectionWork::count(),
            'anime' => CollectionWork::where('type', 'anime')->count(),
            'manga' => CollectionWork::where('type', 'manga')->count(),
            'completed' => CollectionWork::where('status', 'completed')->count(),
            'watching' => CollectionWork::where('status', 'watching')->count(),
            'favorites' => CollectionWork::where('is_favorite', true)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.collection-index', [
            'works' => $this->works,
            'stats' => $this->stats,
            'categories' => CollectionCategory::orderBy('display_order')->get()->groupBy('group'),
        ])->layout('components.layouts.app', ['title' => '收藏']);
    }
}
