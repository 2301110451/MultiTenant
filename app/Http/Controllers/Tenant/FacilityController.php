<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacilityController extends Controller
{
    private function ensureOfficer(Request $request): void
    {
        $user = $request->user('tenant');
        abort_unless($user && ($user->isSecretary() || $user->isCaptain()), 403);
    }

    public function index(Request $request): View
    {
        $query = Facility::query()->latest();
        if ($request->user('tenant')?->isResident()) {
            $query->where('is_active', true);
        }

        $facilities = $query->paginate(15);

        return view('tenant.facilities.index', compact('facilities'));
    }

    public function create(Request $request): View
    {
        $this->ensureOfficer($request);

        return view('tenant.facilities.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureOfficer($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'capacity' => ['required', 'integer', 'min:0'],
            'rules' => ['nullable', 'string'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        Facility::query()->create($data);

        return redirect()->route('tenant.facilities.index')->with('status', 'facility-created');
    }

    public function edit(Request $request, Facility $facility): View
    {
        $this->ensureOfficer($request);

        return view('tenant.facilities.edit', compact('facility'));
    }

    public function update(Request $request, Facility $facility): RedirectResponse
    {
        $this->ensureOfficer($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'capacity' => ['required', 'integer', 'min:0'],
            'rules' => ['nullable', 'string'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $facility->update($data);

        return redirect()->route('tenant.facilities.index')->with('status', 'facility-updated');
    }

    public function destroy(Request $request, Facility $facility): RedirectResponse
    {
        $this->ensureOfficer($request);

        $facility->delete();

        return redirect()->route('tenant.facilities.index')->with('status', 'facility-deleted');
    }
}
