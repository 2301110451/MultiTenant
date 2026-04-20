@php
    $accentSource = old('accent_source', filled($settings?->accent_color) ? 'custom' : 'plan');
    $accentHex = strtoupper((string) old('accent_color', $settings?->accent_color ?? '#2563EB'));
    if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $accentHex)) {
        $accentHex = '#2563EB';
    }
    /* Same vibrant grid as button colors — staff can soften with HSL sliders after picking. */
    $accentPresets = [
        '#2563EB', '#1D4ED8', '#6366F1', '#7C3AED', '#9333EA', '#A855F7', '#DB2777', '#E11D48',
        '#DC2626', '#EA580C', '#F59E0B', '#CA8A04', '#84CC16', '#16A34A', '#059669', '#0D9488',
        '#0891B2', '#0284C7', '#475569', '#0F172A',
    ];
    $bgPresets = $accentPresets;

    $bgSource = old('bg_source', filled($settings?->background_color) ? 'custom' : 'plan');
    $bgHex = strtoupper((string) old('background_color', $settings?->background_color ?? '#F0F4F8'));
    if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $bgHex)) {
        $bgHex = '#F0F4F8';
    }

    $sidebarSource = old('sidebar_source', filled($settings?->sidebar_background_color) ? 'custom' : 'plan');
    $sidebarHex = strtoupper((string) old('sidebar_background_color', $settings?->sidebar_background_color ?? '#0F172A'));
    if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $sidebarHex)) {
        $sidebarHex = '#0F172A';
    }
    $sidebarPresets = [
        '#020617', '#0F172A', '#0C1426', '#111827', '#1E293B', '#172554', '#1E1B4B', '#312E81',
        '#134E4A', '#14532D', '#3B0764', '#431407', '#450A0A', '#082F49', '#164E63', '#0C4A6E',
    ];

    $settingsFormInit = [
        'accentSource' => $accentSource,
        'accentHex' => $accentHex,
        'accentPresets' => $accentPresets,
        'bgSource' => $bgSource,
        'bgHex' => $bgHex,
        'bgPresets' => $bgPresets,
        'sidebarSource' => $sidebarSource,
        'sidebarHex' => $sidebarHex,
        'sidebarPresets' => $sidebarPresets,
    ];
@endphp

<x-tenant-layout title="Portal settings" breadcrumb="Portal settings">

    <div class="px-6 py-8 sm:px-10 max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Portal settings</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                <strong class="font-medium text-slate-700 dark:text-slate-300">Tenant admins</strong> set the barangay-wide
                <strong class="font-medium text-slate-700 dark:text-slate-300">button color</strong> (primary actions and links),
                <strong class="font-medium text-slate-700 dark:text-slate-300">sidebar color</strong> (left menu background),
                and <strong class="font-medium text-slate-700 dark:text-slate-300">page background</strong> (main area behind cards).
                Use the color square, <strong class="font-medium text-slate-700 dark:text-slate-300">drag the sliders</strong>, or tap a swatch—no codes required.
            </p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">
                This covers tenant-wide appearance settings here and in related flows—not full white-label control of every email, PDF, or label unless those are implemented separately.
            </p>
            <div class="mt-4 rounded-xl border border-indigo-200 bg-indigo-50/80 dark:border-indigo-500/30 dark:bg-indigo-950/30 px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                <p class="font-semibold text-indigo-900 dark:text-indigo-200">Barangay-wide default for all users</p>
                <p class="mt-1 text-slate-600 dark:text-slate-400">
                    What you save here—<strong class="font-medium text-slate-800 dark:text-slate-200">button and link color</strong>,
                    <strong class="font-medium text-slate-800 dark:text-slate-200">page background</strong>, and
                    <strong class="font-medium text-slate-800 dark:text-slate-200">sidebar background</strong>—applies to
                    <strong class="font-medium text-slate-800 dark:text-slate-200">everyone</strong> in this tenant
                    (including tenant admin, staff, and other roles) immediately after saving.
                </p>
            </div>
        </div>

        @if(session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</div>
        @endif

        @if (! $canUpdateSettings)
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 dark:border-slate-600 dark:bg-slate-900/50 dark:text-slate-300">
                You can review how the portal is styled. Only users with <strong class="font-medium">update</strong> rights on settings can save changes.
            </div>
        @endif

        <form method="POST" action="{{ route('tenant.settings.update') }}" class="t-card p-6 space-y-8"
              x-data="window.tenantSettingsForm(@js($settingsFormInit))"
              x-effect="
                  if (source === 'custom' && !/^#[0-9A-Fa-f]{6}$/i.test(hex)) hex = '#2563EB';
                  if (bgSource === 'custom' && !/^#[0-9A-Fa-f]{6}$/i.test(bgHex)) bgHex = '#F0F4F8';
                  if (sidebarSource === 'custom' && !/^#[0-9A-Fa-f]{6}$/i.test(sidebarHex)) sidebarHex = '#0F172A';
              ">
            @csrf
            @method('PUT')

            <fieldset @disabled(! $canUpdateSettings) class="min-w-0 border-0 p-0 m-0 space-y-8 disabled:[&_label]:cursor-default disabled:[&_button]:cursor-not-allowed">
            <div>
                <label class="t-label" for="branding_name">Name shown in the sidebar</label>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-1.5">Leave blank to use your barangay’s registered name.</p>
                <input id="branding_name" name="branding_name" value="{{ old('branding_name', $settings?->branding_name) }}" class="t-input" placeholder="e.g. Barangay San Jose">
            </div>

            {{-- Accent (buttons / links) --}}
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 sm:p-5 space-y-4">
                <div>
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Button &amp; link color</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Used for primary buttons, important links, and highlights. Either keep your plan’s built-in color or choose your own from the list or color box.
                    </p>
                </div>

                @error('accent_source')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                @error('accent_color')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <fieldset class="space-y-3">
                    <legend class="sr-only">Button and link color</legend>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-600 p-3 cursor-pointer has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-950/30">
                        <input type="radio" name="accent_source" value="plan" class="mt-1 text-indigo-600 focus:ring-indigo-500" x-model="source">
                        <span class="text-sm">
                            <span class="font-medium text-slate-900 dark:text-slate-100">Use my plan’s default (Basic / Standard / Premium)</span>
                            <span class="block text-slate-500 dark:text-slate-400 text-xs mt-0.5">Best if you want the official look for your subscription tier.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-600 p-3 cursor-pointer has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-950/30">
                        <input type="radio" name="accent_source" value="custom" class="mt-1 text-indigo-600 focus:ring-indigo-500" x-model="source">
                        <span class="text-sm">
                            <span class="font-medium text-slate-900 dark:text-slate-100">Choose my own button &amp; link color</span>
                            <span class="block text-slate-500 dark:text-slate-400 text-xs mt-0.5">Use the color box, drag the sliders below, or tap a swatch.</span>
                        </span>
                    </label>
                </fieldset>

                <div class="space-y-4 pt-1" x-show="source === 'custom'" x-cloak>
                    <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                        <div class="shrink-0">
                            <span class="t-label block mb-1.5">Color box</span>
                            <label class="block relative w-14 h-14 rounded-xl overflow-hidden ring-1 ring-slate-200 dark:ring-slate-600 shadow-sm cursor-pointer">
                                <span class="sr-only">Pick button and link color</span>
                                <input type="color"
                                       class="absolute inset-0 w-[150%] h-[150%] -translate-x-2 -translate-y-2 cursor-pointer disabled:cursor-not-allowed disabled:opacity-40"
                                       :value="safePicker()"
                                       @input="hex = $event.target.value.toUpperCase()"
                                       :disabled="source === 'plan'">
                            </label>
                        </div>
                        <div class="flex-1 min-w-0">
                            <label class="t-label" for="accent_color">Color code (optional)</label>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1.5">Only if you already have a code from elsewhere.</p>
                            <input id="accent_color" type="text" name="accent_color" x-model="hex" maxlength="7"
                                   :disabled="source === 'plan'"
                                   class="t-input font-mono uppercase tracking-wide max-w-xs"
                                   placeholder="#2563EB"
                                   autocomplete="off">
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-900/50 p-4 space-y-3">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-100">Drag sliders to fine-tune</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 -mt-1">Hue = color family · Richness = how strong · Lightness = how light or dark</p>
                        <div class="space-y-3">
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Hue</span>
                                <input type="range" min="0" max="360" x-model.number="ah" @input="accentFromSliders()"
                                       class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600"
                                       :disabled="source === 'plan'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(ah) + '°'"></span>
                            </label>
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Richness</span>
                                <input type="range" min="0" max="100" x-model.number="as" @input="accentFromSliders()"
                                       class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600"
                                       :disabled="source === 'plan'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(as) + '%'"></span>
                            </label>
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Lightness</span>
                                <input type="range" min="0" max="100" x-model.number="al" @input="accentFromSliders()"
                                       class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600"
                                       :disabled="source === 'plan'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(al) + '%'"></span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-2">Ready-made button colors</p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="c in accentPresets" :key="'a-' + c">
                                <button type="button"
                                        class="w-9 h-9 rounded-lg ring-1 ring-slate-200 dark:ring-slate-600 shrink-0 transition hover:scale-105 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900"
                                        :style="'background-color:' + c"
                                        :title="'Use ' + c"
                                        @click="pickAccent(c)"
                                        :aria-label="'Use button color ' + c">
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="rounded-xl border border-dashed border-slate-200 dark:border-slate-600 p-4 bg-slate-50/80 dark:bg-slate-900/40">
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-3">Preview</p>
                        <div class="flex flex-wrap items-center gap-3" :style="'--tenant-accent:' + safePicker()">
                            <button type="button" class="px-4 py-2 rounded-xl text-sm font-semibold text-white shadow-md pointer-events-none"
                                    style="background-color: var(--tenant-accent); box-shadow: 0 4px 14px rgba(0,0,0,0.12);">
                                Sample button
                            </button>
                            <span class="text-sm font-semibold pointer-events-none" style="color: var(--tenant-accent);">Sample link</span>
                            <span class="text-xs px-2.5 py-1 rounded-full border pointer-events-none bg-accent-soft">Soft highlight</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Page background --}}
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 sm:p-5 space-y-4">
                <div>
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Page background</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        The soft color behind your white cards and tables in the main area. In <strong class="font-medium text-slate-600 dark:text-slate-300">light mode</strong> this is what changes; dark mode keeps the default dark theme so text stays easy to read.
                    </p>
                </div>

                @error('bg_source')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                @error('background_color')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <fieldset class="space-y-3">
                    <legend class="sr-only">Page background color</legend>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-600 p-3 cursor-pointer has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-950/30">
                        <input type="radio" name="bg_source" value="plan" class="mt-1 text-indigo-600 focus:ring-indigo-500" x-model="bgSource">
                        <span class="text-sm">
                            <span class="font-medium text-slate-900 dark:text-slate-100">Use default page background</span>
                            <span class="block text-slate-500 dark:text-slate-400 text-xs mt-0.5">Same soft gray-blue canvas as a new portal.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-600 p-3 cursor-pointer has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-950/30">
                        <input type="radio" name="bg_source" value="custom" class="mt-1 text-indigo-600 focus:ring-indigo-500" x-model="bgSource">
                        <span class="text-sm">
                            <span class="font-medium text-slate-900 dark:text-slate-100">Choose my own page background</span>
                            <span class="block text-slate-500 dark:text-slate-400 text-xs mt-0.5">Use the color box, drag sliders, or tap a swatch from the color grid.</span>
                        </span>
                    </label>
                </fieldset>

                <div class="space-y-4 pt-1" x-show="bgSource === 'custom'" x-cloak>
                    <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                        <div class="shrink-0">
                            <span class="t-label block mb-1.5">Color box</span>
                            <label class="block relative w-14 h-14 rounded-xl overflow-hidden ring-1 ring-slate-200 dark:ring-slate-600 shadow-sm cursor-pointer">
                                <span class="sr-only">Pick page background color</span>
                                <input type="color"
                                       class="absolute inset-0 w-[150%] h-[150%] -translate-x-2 -translate-y-2 cursor-pointer disabled:cursor-not-allowed disabled:opacity-40"
                                       :value="safeBgPicker()"
                                       @input="bgHex = $event.target.value.toUpperCase()"
                                       :disabled="bgSource === 'plan'">
                            </label>
                        </div>
                        <div class="flex-1 min-w-0">
                            <label class="t-label" for="background_color">Color code (optional)</label>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1.5">Only if you already have a code from elsewhere.</p>
                            <input id="background_color" type="text" name="background_color" x-model="bgHex" maxlength="7"
                                   :disabled="bgSource === 'plan'"
                                   class="t-input font-mono uppercase tracking-wide max-w-xs"
                                   placeholder="#F0F4F8"
                                   autocomplete="off">
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-900/50 p-4 space-y-3">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-100">Drag sliders to fine-tune</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 -mt-1">For page backgrounds, keep <strong class="font-medium text-slate-600 dark:text-slate-300">Richness</strong> low and <strong class="font-medium text-slate-600 dark:text-slate-300">Lightness</strong> high so text stays easy to read.</p>
                        <div class="space-y-3">
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Hue</span>
                                <input type="range" min="0" max="360" x-model.number="bh" @input="bgFromSliders()"
                                       class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600"
                                       :disabled="bgSource === 'plan'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(bh) + '°'"></span>
                            </label>
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Richness</span>
                                <input type="range" min="0" max="100" x-model.number="bs" @input="bgFromSliders()"
                                       class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600"
                                       :disabled="bgSource === 'plan'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(bs) + '%'"></span>
                            </label>
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Lightness</span>
                                <input type="range" min="0" max="100" x-model.number="bl" @input="bgFromSliders()"
                                       class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600"
                                       :disabled="bgSource === 'plan'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(bl) + '%'"></span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-2">Ready-made page background colors</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">Same color grid as button colors. If the page feels too strong, raise <strong class="font-medium text-slate-600 dark:text-slate-300">Lightness</strong> on the sliders above.</p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="c in bgPresets" :key="'b-' + c">
                                <button type="button"
                                        class="w-9 h-9 rounded-lg ring-1 ring-slate-300 dark:ring-slate-600 shrink-0 transition hover:scale-105 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900"
                                        :style="'background-color:' + c"
                                        :title="'Use ' + c"
                                        @click="pickBg(c)"
                                        :aria-label="'Use background ' + c">
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="rounded-xl border border-dashed border-slate-200 dark:border-slate-600 p-2 overflow-hidden">
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-2 px-2 pt-2">Preview</p>
                        <div class="rounded-lg p-4 min-h-[120px] relative ring-1 ring-slate-200/80 dark:ring-slate-600"
                             :style="'background-color:' + safeBgPicker()">
                            <div class="rounded-lg bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700 p-3 max-w-xs">
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-100">Sample white card</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Your lists and forms sit on cards like this.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar (left menu) background --}}
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 sm:p-5 space-y-4">
                <div>
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Left menu (sidebar) background</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        The dark column where Dashboard, Reservations, Settings, and other links appear. Defaults match a new portal; you can pick a different dark tone if you prefer.
                    </p>
                </div>

                @error('sidebar_source')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                @error('sidebar_background_color')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <fieldset class="space-y-3">
                    <legend class="sr-only">Sidebar background</legend>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-600 p-3 cursor-pointer has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-950/30">
                        <input type="radio" name="sidebar_source" value="plan" class="mt-1 text-indigo-600 focus:ring-indigo-500" x-model="sidebarSource">
                        <span class="text-sm">
                            <span class="font-medium text-slate-900 dark:text-slate-100">Use default sidebar look</span>
                            <span class="block text-slate-500 dark:text-slate-400 text-xs mt-0.5">Same deep navy style as a new portal.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-600 p-3 cursor-pointer has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-950/30">
                        <input type="radio" name="sidebar_source" value="custom" class="mt-1 text-indigo-600 focus:ring-indigo-500" x-model="sidebarSource">
                        <span class="text-sm">
                            <span class="font-medium text-slate-900 dark:text-slate-100">Choose my own sidebar color</span>
                            <span class="block text-slate-500 dark:text-slate-400 text-xs mt-0.5">Pick a dark tone that still keeps white text readable.</span>
                        </span>
                    </label>
                </fieldset>

                <div class="space-y-4 pt-1" x-show="sidebarSource === 'custom'" x-cloak>
                    <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                        <div class="shrink-0">
                            <span class="t-label block mb-1.5">Color box</span>
                            <label class="block relative w-14 h-14 rounded-xl overflow-hidden ring-1 ring-slate-200 dark:ring-slate-600 shadow-sm cursor-pointer">
                                <span class="sr-only">Pick sidebar background</span>
                                <input type="color"
                                       class="absolute inset-0 w-[150%] h-[150%] -translate-x-2 -translate-y-2 cursor-pointer disabled:cursor-not-allowed disabled:opacity-40"
                                       :value="safeSidebarPicker()"
                                       @input="sidebarHex = $event.target.value.toUpperCase()"
                                       :disabled="sidebarSource === 'plan'">
                            </label>
                        </div>
                        <div class="flex-1 min-w-0">
                            <label class="t-label" for="sidebar_background_color">Color code (optional)</label>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1.5">Only if you already have a code from elsewhere.</p>
                            <input id="sidebar_background_color" type="text" name="sidebar_background_color" x-model="sidebarHex" maxlength="7"
                                   :disabled="sidebarSource === 'plan'"
                                   class="t-input font-mono uppercase tracking-wide max-w-xs"
                                   placeholder="#0F172A"
                                   autocomplete="off">
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-900/50 p-4 space-y-3">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-100">Drag sliders to fine-tune</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 -mt-1">Keep <strong class="font-medium text-slate-600 dark:text-slate-300">Lightness</strong> low so the menu stays dark enough for white labels.</p>
                        <div class="space-y-3">
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Hue</span>
                                <input type="range" min="0" max="360" x-model.number="sbh" @input="sidebarFromSliders()"
                                       class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600"
                                       :disabled="sidebarSource === 'plan'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(sbh) + '°'"></span>
                            </label>
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Richness</span>
                                <input type="range" min="0" max="100" x-model.number="sbs" @input="sidebarFromSliders()"
                                       class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600"
                                       :disabled="sidebarSource === 'plan'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(sbs) + '%'"></span>
                            </label>
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Lightness</span>
                                <input type="range" min="0" max="100" x-model.number="sbl" @input="sidebarFromSliders()"
                                       class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600"
                                       :disabled="sidebarSource === 'plan'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(sbl) + '%'"></span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-2">Ready-made sidebar colors</p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="c in sidebarPresets" :key="'s-' + c">
                                <button type="button"
                                        class="w-9 h-9 rounded-lg ring-1 ring-slate-300 dark:ring-slate-600 shrink-0 transition hover:scale-105 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900"
                                        :style="'background-color:' + c"
                                        :title="'Use ' + c"
                                        @click="pickSidebar(c)"
                                        :aria-label="'Use sidebar color ' + c">
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="rounded-xl border border-dashed border-slate-200 dark:border-slate-600 p-3">
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-2">Preview</p>
                        <div class="flex gap-3 rounded-lg overflow-hidden ring-1 ring-slate-200 dark:ring-slate-600 max-w-md">
                            <div class="w-28 shrink-0 py-4 px-2 flex flex-col items-center gap-2 text-[10px] font-semibold text-white/90"
                                 :style="'background-color:' + safeSidebarPicker()">
                                <span class="opacity-80">MENU</span>
                                <span class="rounded-md px-2 py-1 w-full text-center bg-white/15">Home</span>
                                <span class="rounded-md px-2 py-1 w-full text-center bg-white/10">List</span>
                            </div>
                            <div class="flex-1 bg-slate-100 dark:bg-slate-800 p-2 text-[10px] text-slate-500 dark:text-slate-400 flex items-center">
                                Main page area (unchanged)
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                <input type="checkbox" name="compact_layout" value="1" @checked((bool) old('compact_layout', $settings?->compact_layout ?? false))>
                Use compact layout <span class="text-slate-500 dark:text-slate-400 font-normal">(tighter spacing on lists and forms)</span>
            </label>

            @php $toggles = $settings?->module_toggles ?? []; @endphp
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 space-y-3">
                <div>
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Which sections appear in the menu</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Turn off items your barangay does not use (you can turn them back on anytime).</p>
                </div>
                <div class="space-y-2 pl-0.5">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200"><input type="checkbox" name="module_reports" value="1" @checked((bool) old('module_reports', $toggles['reports'] ?? true))> Reports</label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200"><input type="checkbox" name="module_facilities" value="1" @checked((bool) old('module_facilities', $toggles['facilities'] ?? true))> Facilities</label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200"><input type="checkbox" name="module_reservations" value="1" @checked((bool) old('module_reservations', $toggles['reservations'] ?? true))> Reservations</label>
                </div>
            </div>

            </fieldset>

            @if ($canUpdateSettings)
                <div class="flex justify-end pt-2">
                    <button type="submit" class="t-btn-primary">
                        Save settings
                    </button>
                </div>
            @endif
        </form>
    </div>

    @push('scripts')
    <script>
    (function () {
        function hexToRgb(hex) {
            var m = /^#?([0-9a-f]{6})$/i.exec(String(hex).trim());
            if (!m) return null;
            var n = parseInt(m[1], 16);
            return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255 };
        }
        function rgbToHsl(r, g, b) {
            r /= 255; g /= 255; b /= 255;
            var max = Math.max(r, g, b), min = Math.min(r, g, b);
            var h = 0, s = 0, l = (max + min) / 2;
            if (max !== min) {
                var d = max - min;
                s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                switch (max) {
                    case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                    case g: h = (b - r) / d + 2; break;
                    default: h = (r - g) / d + 4;
                }
                h /= 6;
            }
            return { h: Math.round(h * 360), s: Math.round(s * 100), l: Math.round(l * 100) };
        }
        function hslToRgb(h, s, l) {
            h = Number(h) / 360; s = Number(s) / 100; l = Number(l) / 100;
            if (s === 0) {
                var v = Math.round(l * 255);
                return { r: v, g: v, b: v };
            }
            function hue2rgb(p, q, t) {
                if (t < 0) t += 1;
                if (t > 1) t -= 1;
                if (t < 1 / 6) return p + (q - p) * 6 * t;
                if (t < 1 / 2) return q;
                if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
                return p;
            }
            var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
            var p = 2 * l - q;
            return {
                r: Math.round(hue2rgb(p, q, h + 1 / 3) * 255),
                g: Math.round(hue2rgb(p, q, h) * 255),
                b: Math.round(hue2rgb(p, q, h - 1 / 3) * 255),
            };
        }
        function rgbToHex(r, g, b) {
            return '#' + [r, g, b].map(function (x) {
                x = Math.max(0, Math.min(255, Math.round(x)));
                var hx = x.toString(16);
                return hx.length === 1 ? '0' + hx : hx;
            }).join('').toUpperCase();
        }
        window.__tenantColorUtils = {
            hexToHsl: function (hex) {
                var rgb = hexToRgb(hex);
                if (!rgb) return { h: 210, s: 80, l: 50 };
                return rgbToHsl(rgb.r, rgb.g, rgb.b);
            },
            hslToHex: function (h, s, l) {
                var rgb = hslToRgb(h, s, l);
                return rgbToHex(rgb.r, rgb.g, rgb.b);
            },
        };

        window.tenantSettingsForm = function (init) {
            return {
                source: init.accentSource,
                hex: init.accentHex,
                accentPresets: init.accentPresets,
                bgSource: init.bgSource,
                bgHex: init.bgHex,
                bgPresets: init.bgPresets,
                sidebarSource: init.sidebarSource,
                sidebarHex: init.sidebarHex,
                sidebarPresets: init.sidebarPresets,
                ah: 0,
                as: 0,
                al: 0,
                bh: 0,
                bs: 0,
                bl: 0,
                sbh: 0,
                sbs: 0,
                sbl: 0,
                init: function () {
                    var u = window.__tenantColorUtils;
                    var a = u.hexToHsl(this.hex);
                    this.ah = a.h; this.as = a.s; this.al = a.l;
                    var b = u.hexToHsl(this.bgHex);
                    this.bh = b.h; this.bs = b.s; this.bl = b.l;
                    var s = u.hexToHsl(this.sidebarHex);
                    this.sbh = s.h; this.sbs = s.s; this.sbl = s.l;
                    var self = this;
                    this.$watch('hex', function (v) {
                        if (self.source !== 'custom' || !/^#[0-9A-Fa-f]{6}$/i.test(v)) return;
                        var o = u.hexToHsl(v);
                        self.ah = o.h; self.as = o.s; self.al = o.l;
                    });
                    this.$watch('bgHex', function (v) {
                        if (self.bgSource !== 'custom' || !/^#[0-9A-Fa-f]{6}$/i.test(v)) return;
                        var o = u.hexToHsl(v);
                        self.bh = o.h; self.bs = o.s; self.bl = o.l;
                    });
                    this.$watch('sidebarHex', function (v) {
                        if (self.sidebarSource !== 'custom' || !/^#[0-9A-Fa-f]{6}$/i.test(v)) return;
                        var o = u.hexToHsl(v);
                        self.sbh = o.h; self.sbs = o.s; self.sbl = o.l;
                    });
                },
                safePicker: function () {
                    return /^#[0-9A-Fa-f]{6}$/i.test(this.hex) ? this.hex : '#2563EB';
                },
                safeBgPicker: function () {
                    return /^#[0-9A-Fa-f]{6}$/i.test(this.bgHex) ? this.bgHex : '#F0F4F8';
                },
                safeSidebarPicker: function () {
                    return /^#[0-9A-Fa-f]{6}$/i.test(this.sidebarHex) ? this.sidebarHex : '#0F172A';
                },
                pickAccent: function (c) {
                    this.source = 'custom';
                    this.hex = c;
                },
                pickBg: function (c) {
                    this.bgSource = 'custom';
                    this.bgHex = c;
                },
                pickSidebar: function (c) {
                    this.sidebarSource = 'custom';
                    this.sidebarHex = c;
                },
                accentFromSliders: function () {
                    this.hex = window.__tenantColorUtils.hslToHex(this.ah, this.as, this.al).toUpperCase();
                },
                bgFromSliders: function () {
                    this.bgHex = window.__tenantColorUtils.hslToHex(this.bh, this.bs, this.bl).toUpperCase();
                },
                sidebarFromSliders: function () {
                    this.sidebarHex = window.__tenantColorUtils.hslToHex(this.sbh, this.sbs, this.sbl).toUpperCase();
                },
            };
        };
    })();
    </script>
    @endpush
</x-tenant-layout>
