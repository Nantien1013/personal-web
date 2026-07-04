<?php

namespace App\Livewire;

use App\Models\CollectionCategory;
use App\Models\CollectionWork;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Component;

class CollectionForm extends Component
{
    use AuthorizesRequests;

    public ?int $workId = null;

    public bool $show = false;

    public string $type = 'anime';

    public string $title = '';

    public ?string $title_original = null;

    public ?string $cover_url = null;

    public string $status = 'plan';

    public ?int $rating = null;

    public bool $is_favorite = false;

    public ?int $release_year = null;

    public ?string $release_season = null;

    public ?string $media_type = null;

    public ?string $source_type = null;

    public ?int $episodes_total = null;

    public int $episodes_watched = 0;

    public ?int $volumes_total = null;

    public int $volumes_read = 0;

    public ?string $author = null;

    public ?string $studio = null;

    public ?string $note = null;

    public array $categoryIds = [];

    public function rules(): array
    {
        return [
            'type' => 'required|in:anime,manga',
            'title' => 'required|string|max:255',
            'title_original' => 'nullable|string|max:255',
            'cover_url' => 'nullable|url|max:500',
            'status' => 'required|in:watching,completed,plan,on_hold,dropped',
            'rating' => 'nullable|integer|min:0|max:5',
            'is_favorite' => 'boolean',
            'release_year' => 'nullable|integer|min:1900|max:2100',
            'release_season' => 'nullable|in:winter,spring,summer,autumn',
            'media_type' => 'nullable|string|max:30',
            'source_type' => 'nullable|string|max:30',
            'episodes_total' => 'nullable|integer|min:0',
            'episodes_watched' => 'integer|min:0',
            'volumes_total' => 'nullable|integer|min:0',
            'volumes_read' => 'integer|min:0',
            'author' => 'nullable|string|max:100',
            'studio' => 'nullable|string|max:100',
            'note' => 'nullable|string',
            'categoryIds' => 'array',
            'categoryIds.*' => 'integer|exists:collection_categories,id',
        ];
    }

    #[On('open-collection-form')]
    public function open(?int $workId = null): void
    {
        $this->resetValidation();
        $this->reset();

        if ($workId) {
            $work = CollectionWork::with('categories')->findOrFail($workId);
            $this->workId = $work->id;
            $this->type = $work->type;
            $this->title = $work->title;
            $this->title_original = $work->title_original;
            $this->cover_url = $work->cover_url;
            $this->status = $work->status;
            $this->rating = $work->rating;
            $this->is_favorite = $work->is_favorite;
            $this->release_year = $work->release_year;
            $this->release_season = $work->release_season;
            $this->media_type = $work->media_type;
            $this->source_type = $work->source_type;
            $this->episodes_total = $work->episodes_total;
            $this->episodes_watched = $work->episodes_watched;
            $this->volumes_total = $work->volumes_total;
            $this->volumes_read = $work->volumes_read;
            $this->author = $work->author;
            $this->studio = $work->studio;
            $this->note = $work->note;
            $this->categoryIds = $work->categories->pluck('id')->all();
        }

        $this->show = true;
    }

    public function save(): void
    {
        $this->authorize(
            $this->workId ? 'update' : 'create',
            $this->workId ? CollectionWork::findOrFail($this->workId) : CollectionWork::class
        );

        $data = $this->validate();

        $work = $this->workId
            ? tap(CollectionWork::findOrFail($this->workId))->update($data)
            : CollectionWork::create($data);

        $work->categories()->sync($this->categoryIds);

        $this->show = false;
        $this->dispatch('collection-saved');
        $this->dispatch('toast', message: '已儲存', type: 'success');
    }

    public function delete(): void
    {
        $work = CollectionWork::findOrFail($this->workId);
        $this->authorize('delete', $work);
        $work->delete();

        $this->show = false;
        $this->dispatch('collection-deleted');
        $this->dispatch('toast', message: '已刪除', type: 'success');
    }

    public function close(): void
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.collection-form', [
            'categories' => CollectionCategory::orderBy('display_order')->get()->groupBy('group'),
        ]);
    }
}
