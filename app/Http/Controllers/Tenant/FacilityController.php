<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\FacilityKind;
use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FacilityController extends Controller
{
    private function authorizeAction(Request $request, string $ability, ?Facility $facility = null): void
    {
        $user = $request->user('tenant');
        abort_unless($user, 403);
        Gate::forUser($user)->authorize($ability, $facility ?? Facility::class);
    }

    public function index(Request $request): View
    {
        $this->authorizeAction($request, 'viewAny');

        $kindFilter = match ($request->query('kind')) {
            'facility', 'equipment' => $request->query('kind'),
            default => null,
        };

        $query = Facility::query()->latest();
        if ($request->user('tenant')?->isResident()) {
            $query->where('is_active', true);
        }

        if ($kindFilter !== null) {
            $query->where('kind', $kindFilter);
        }

        $facilities = $query->paginate(12)->withQueryString();

        $facilityIds = $facilities->pluck('id')->filter()->values()->all();
        $blockingByFacilityId = collect();
        if ($facilityIds !== []) {
            $now = now();
            $blockingByFacilityId = Reservation::query()
                ->whereIn('facility_id', $facilityIds)
                ->whereIn('status', [
                    ReservationStatus::Pending->value,
                    ReservationStatus::Approved->value,
                ])
                ->where('starts_at', '<=', $now)
                ->where('ends_at', '>', $now)
                ->get()
                ->groupBy('facility_id')
                ->mapWithKeys(function ($group, $facilityId) {
                    $reservation = $group->sortByDesc(fn (Reservation $r) => $r->ends_at->timestamp)->first();

                    return [(int) $facilityId => $reservation];
                });
        }

        $canManage = $request->user('tenant')?->canManageTenant() ?? false;
        $canReserve = Gate::forUser($request->user('tenant'))->allows('create', Reservation::class);

        return view('tenant.facilities.index', [
            'facilities' => $facilities,
            'kindFilter' => $kindFilter,
            'canManage' => $canManage,
            'canReserve' => $canReserve,
            'blockingByFacilityId' => $blockingByFacilityId,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeAction($request, 'create');

        return view('tenant.facilities.create');
    }

    public function image(Request $request, Facility $facility)
    {
        $this->authorizeAction($request, 'viewAny');

        if ($request->user('tenant')?->isResident() && ! $facility->is_active) {
            abort(404);
        }

        $path = $facility->getAttributes()['image_path'] ?? null;
        if (! is_string($path) || $path === '' || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAction($request, 'create');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kind' => ['required', Rule::enum(FacilityKind::class)],
            'description' => ['nullable', 'string'],
            'capacity' => ['required', 'integer', 'min:0'],
            'rules' => ['nullable', 'string'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:5120'],
            'is_active' => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['image_path'] = $request->file('image')?->store('facility-images', 'public');
        unset($data['image']);

        Facility::query()->create($data);

        return redirect()->route('tenant.facilities.index')->with('status', 'facility-created');
    }

    public function edit(Request $request, Facility $facility): View
    {
        $this->authorizeAction($request, 'update', $facility);

        return view('tenant.facilities.edit', compact('facility'));
    }

    public function update(Request $request, Facility $facility): RedirectResponse
    {
        $this->authorizeAction($request, 'update', $facility);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kind' => ['required', Rule::enum(FacilityKind::class)],
            'description' => ['nullable', 'string'],
            'capacity' => ['required', 'integer', 'min:0'],
            'rules' => ['nullable', 'string'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'is_active' => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $shouldRemoveImage = $request->boolean('remove_image');
        $newImagePath = $request->file('image')?->store('facility-images', 'public');
        $currentImagePath = $facility->getAttributes()['image_path'] ?? null;

        if ($newImagePath !== null) {
            if (is_string($currentImagePath) && $currentImagePath !== '') {
                Storage::disk('public')->delete($currentImagePath);
            }
            $data['image_path'] = $newImagePath;
        } elseif ($shouldRemoveImage) {
            if (is_string($currentImagePath) && $currentImagePath !== '') {
                Storage::disk('public')->delete($currentImagePath);
            }
            $data['image_path'] = null;
        }

        unset($data['image'], $data['remove_image']);

        $facility->update($data);

        return redirect()->route('tenant.facilities.index')->with('status', 'facility-updated');
    }

    public function destroy(Request $request, Facility $facility): RedirectResponse
    {
        $this->authorizeAction($request, 'delete', $facility);

        $legacyPath = $facility->getAttributes()['image_path'] ?? null;
        if (is_string($legacyPath) && $legacyPath !== '') {
            Storage::disk('public')->delete($legacyPath);
        }

        $facility->delete();

        return redirect()->route('tenant.facilities.index')->with('status', 'facility-deleted');
    }
}
