<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::query()->latest()->paginate(15);

        return view('central.plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('central.plans.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:plans,slug'],
            'monthly_reservation_limit' => ['nullable', 'integer', 'min:1'],
            'features_json' => ['nullable', 'string'],
        ]);

        $features = $this->resolveFeatures($request, $data['features_json'] ?? null);

        Plan::query()->create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'monthly_reservation_limit' => $data['monthly_reservation_limit'] ?? null,
            'features' => $features,
        ]);

        return redirect()->route('central.plans.index')->with('status', 'plan-created');
    }

    public function edit(Plan $plan): View
    {
        return view('central.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:plans,slug,'.$plan->id],
            'monthly_reservation_limit' => ['nullable', 'integer', 'min:1'],
            'features_json' => ['nullable', 'string'],
        ]);

        $features = $this->resolveFeatures($request, $data['features_json'] ?? null, $plan->features);

        $plan->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'monthly_reservation_limit' => $data['monthly_reservation_limit'] ?? null,
            'features' => $features,
        ]);

        return redirect()->route('central.plans.index')->with('status', 'plan-updated');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $plan->delete();

        return redirect()->route('central.plans.index')->with('status', 'plan-deleted');
    }

    private function resolveFeatures(Request $request, ?string $featuresJson, ?array $fallback = null): ?array
    {
        // If raw JSON was submitted (legacy / API path), prefer that.
        if (! empty($featuresJson)) {
            $decoded = json_decode($featuresJson, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Otherwise build from feature_* checkbox fields.
        $knownKeys = ['reports', 'qr', 'payments'];
        $features  = [];
        foreach ($knownKeys as $key) {
            if ($request->boolean('feature_'.$key)) {
                $features[] = $key;
            }
        }

        return empty($features) ? ($fallback ?? null) : $features;
    }
}
