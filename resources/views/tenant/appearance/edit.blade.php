@php
    $accentMode = old('accent_mode', filled($user->appearance_accent_color) ? 'custom' : 'follow');
    $accentHex = strtoupper((string) old('accent_color', $user->appearance_accent_color ?? '#2563EB'));
    if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $accentHex)) {
        $accentHex = '#2563EB';
    }

    $accentPresets = [
        '#2563EB', '#1D4ED8', '#6366F1', '#7C3AED', '#9333EA', '#A855F7', '#DB2777', '#E11D48',
        '#DC2626', '#EA580C', '#F59E0B', '#CA8A04', '#84CC16', '#16A34A', '#059669', '#0D9488',
        '#0891B2', '#0284C7', '#475569', '#0F172A',
    ];
    $bgPresets = $accentPresets;

    $bgMode = old('bg_mode', filled($user->appearance_background_color) ? 'custom' : 'follow');
    $bgHex = strtoupper((string) old('background_color', $user->appearance_background_color ?? '#F0F4F8'));
    if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $bgHex)) {
        $bgHex = '#F0F4F8';
    }

    $sidebarMode = old('sidebar_mode', filled($user->appearance_sidebar_background_color) ? 'custom' : 'follow');
    $sidebarHex = strtoupper((string) old('sidebar_background_color', $user->appearance_sidebar_background_color ?? '#0F172A'));
    if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $sidebarHex)) {
        $sidebarHex = '#0F172A';
    }
    $sidebarPresets = [
        '#020617', '#0F172A', '#0C1426', '#111827', '#1E293B', '#172554', '#1E1B4B', '#312E81',
        '#134E4A', '#14532D', '#3B0764', '#431407', '#450A0A', '#082F49', '#164E63', '#0C4A6E',
    ];

    $appearanceFormInit = [
        'accentMode' => $accentMode,
        'accentHex' => $accentHex,
        'accentPresets' => $accentPresets,
        'bgMode' => $bgMode,
        'bgHex' => $bgHex,
        'bgPresets' => $bgPresets,
        'sidebarMode' => $sidebarMode,
        'sidebarHex' => $sidebarHex,
        'sidebarPresets' => $sidebarPresets,
    ];
@endphp

<x-tenant-layout title="My display" breadcrumb="My display">
    <div class="px-6 py-8 sm:px-10 max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">My display</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                <strong class="font-medium text-slate-700 dark:text-slate-300">Match barangay portal</strong> uses the
                <strong class="font-medium text-slate-700 dark:text-slate-300">button, page background, and sidebar</strong> colors from
                <strong class="font-medium text-slate-700 dark:text-slate-300">Portal settings</strong> (set by your barangay admin)—the same shared look for everyone who keeps this option.
                Choose <strong class="font-medium text-slate-700 dark:text-slate-300">Custom</strong> only if you want <strong class="font-medium text-slate-700 dark:text-slate-300">your account</strong> to differ for each part you customize.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('tenant.account.display.update') }}" class="t-card p-6 space-y-8"
              x-data="window.userAppearanceForm(@js($appearanceFormInit))"
              x-effect="
                  if (accentMode === 'custom' && !/^#[0-9A-Fa-f]{6}$/i.test(accentHex)) accentHex = '#2563EB';
                  if (bgMode === 'custom' && !/^#[0-9A-Fa-f]{6}$/i.test(bgHex)) bgHex = '#F0F4F8';
                  if (sidebarMode === 'custom' && !/^#[0-9A-Fa-f]{6}$/i.test(sidebarHex)) sidebarHex = '#0F172A';
              ">
            @csrf
            @method('PUT')

            {{-- Accent --}}
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 sm:p-5 space-y-4">
                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Button &amp; link color</p>
                @error('accent_mode')<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                @error('accent_color')<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror

                <fieldset class="space-y-3">
                    <legend class="sr-only">Accent color</legend>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-600 p-3 cursor-pointer has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-950/30">
                        <input type="radio" name="accent_mode" value="follow" class="mt-1 text-indigo-600 focus:ring-indigo-500" x-model="accentMode">
                        <span class="text-sm">
                            <span class="font-medium text-slate-900 dark:text-slate-100">Match barangay portal</span>
                            <span class="block text-slate-500 dark:text-slate-400 text-xs mt-0.5">Same buttons and links as the barangay’s shared portal style.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-600 p-3 cursor-pointer has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-950/30">
                        <input type="radio" name="accent_mode" value="custom" class="mt-1 text-indigo-600 focus:ring-indigo-500" x-model="accentMode">
                        <span class="text-sm">
                            <span class="font-medium text-slate-900 dark:text-slate-100">Use my own color</span>
                            <span class="block text-slate-500 dark:text-slate-400 text-xs mt-0.5">Only you see this choice while signed in.</span>
                        </span>
                    </label>
                </fieldset>

                <div class="space-y-4 pt-1" x-show="accentMode === 'custom'" x-cloak>
                    <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                        <div class="shrink-0">
                            <span class="t-label block mb-1.5">Color box</span>
                            <label class="block relative w-14 h-14 rounded-xl overflow-hidden ring-1 ring-slate-200 dark:ring-slate-600 shadow-sm cursor-pointer">
                                <span class="sr-only">Pick accent</span>
                                <input type="color" class="absolute inset-0 w-[150%] h-[150%] -translate-x-2 -translate-y-2 cursor-pointer"
                                       :value="safeAccent()" @input="accentHex = $event.target.value.toUpperCase()" :disabled="accentMode === 'follow'">
                            </label>
                        </div>
                        <div class="flex-1 min-w-0">
                            <label class="t-label" for="accent_color">Color code (optional)</label>
                            <input id="accent_color" type="text" name="accent_color" x-model="accentHex" maxlength="7"
                                   :disabled="accentMode === 'follow'"
                                   class="t-input font-mono uppercase tracking-wide max-w-xs" placeholder="#2563EB" autocomplete="off">
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-900/50 p-4 space-y-3">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-100">Drag sliders to fine-tune</p>
                        <div class="space-y-3">
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Hue</span>
                                <input type="range" min="0" max="360" x-model.number="ah" @input="accentFromSliders()" class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600" :disabled="accentMode === 'follow'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(ah) + '°'"></span>
                            </label>
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Richness</span>
                                <input type="range" min="0" max="100" x-model.number="as" @input="accentFromSliders()" class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600" :disabled="accentMode === 'follow'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(as) + '%'"></span>
                            </label>
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Lightness</span>
                                <input type="range" min="0" max="100" x-model.number="al" @input="accentFromSliders()" class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600" :disabled="accentMode === 'follow'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(al) + '%'"></span>
                            </label>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="c in accentPresets" :key="'a-' + c">
                            <button type="button" class="w-9 h-9 rounded-lg ring-1 ring-slate-200 dark:ring-slate-600 shrink-0 transition hover:scale-105"
                                    :style="'background-color:' + c" @click="pickAccent(c)" :aria-label="'Use ' + c"></button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Page background --}}
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 sm:p-5 space-y-4">
                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Page background</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 -mt-2">Main canvas behind cards &mdash; applies in <strong class="font-medium text-slate-600 dark:text-slate-300">light and dark</strong> mode when you choose a custom color.</p>
                @error('bg_mode')<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                @error('background_color')<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror

                <fieldset class="space-y-3">
                    <legend class="sr-only">Page background</legend>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-600 p-3 cursor-pointer has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-950/30">
                        <input type="radio" name="bg_mode" value="follow" class="mt-1 text-indigo-600 focus:ring-indigo-500" x-model="bgMode">
                        <span class="text-sm">
                            <span class="font-medium text-slate-900 dark:text-slate-100">Match barangay portal</span>
                            <span class="block text-slate-500 dark:text-slate-400 text-xs mt-0.5">Same soft canvas as the shared portal style.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-600 p-3 cursor-pointer has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-950/30">
                        <input type="radio" name="bg_mode" value="custom" class="mt-1 text-indigo-600 focus:ring-indigo-500" x-model="bgMode">
                        <span class="text-sm">
                            <span class="font-medium text-slate-900 dark:text-slate-100">Use my own page background</span>
                        </span>
                    </label>
                </fieldset>

                <div class="space-y-4 pt-1" x-show="bgMode === 'custom'" x-cloak>
                    <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                        <div class="shrink-0">
                            <span class="t-label block mb-1.5">Color box</span>
                            <label class="block relative w-14 h-14 rounded-xl overflow-hidden ring-1 ring-slate-200 dark:ring-slate-600 shadow-sm cursor-pointer">
                                <input type="color" class="absolute inset-0 w-[150%] h-[150%] -translate-x-2 -translate-y-2 cursor-pointer"
                                       :value="safeBg()" @input="bgHex = $event.target.value.toUpperCase()" :disabled="bgMode === 'follow'">
                            </label>
                        </div>
                        <div class="flex-1 min-w-0">
                            <label class="t-label" for="background_color">Color code (optional)</label>
                            <input id="background_color" type="text" name="background_color" x-model="bgHex" maxlength="7"
                                   :disabled="bgMode === 'follow'"
                                   class="t-input font-mono uppercase tracking-wide max-w-xs" placeholder="#F0F4F8" autocomplete="off">
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-900/50 p-4 space-y-3">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-100">Drag sliders to fine-tune</p>
                        <div class="space-y-3">
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Hue</span>
                                <input type="range" min="0" max="360" x-model.number="bh" @input="bgFromSliders()" class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600" :disabled="bgMode === 'follow'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(bh) + '°'"></span>
                            </label>
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Richness</span>
                                <input type="range" min="0" max="100" x-model.number="bs" @input="bgFromSliders()" class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600" :disabled="bgMode === 'follow'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(bs) + '%'"></span>
                            </label>
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Lightness</span>
                                <input type="range" min="0" max="100" x-model.number="bl" @input="bgFromSliders()" class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600" :disabled="bgMode === 'follow'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(bl) + '%'"></span>
                            </label>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="c in bgPresets" :key="'b-' + c">
                            <button type="button" class="w-9 h-9 rounded-lg ring-1 ring-slate-300 dark:ring-slate-600 shrink-0 transition hover:scale-105"
                                    :style="'background-color:' + c" @click="pickBg(c)"></button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 sm:p-5 space-y-4">
                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Left menu (sidebar) background</p>
                @error('sidebar_mode')<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                @error('sidebar_background_color')<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror

                <fieldset class="space-y-3">
                    <legend class="sr-only">Sidebar</legend>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-600 p-3 cursor-pointer has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-950/30">
                        <input type="radio" name="sidebar_mode" value="follow" class="mt-1 text-indigo-600 focus:ring-indigo-500" x-model="sidebarMode">
                        <span class="text-sm">
                            <span class="font-medium text-slate-900 dark:text-slate-100">Match barangay portal</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-600 p-3 cursor-pointer has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-950/30">
                        <input type="radio" name="sidebar_mode" value="custom" class="mt-1 text-indigo-600 focus:ring-indigo-500" x-model="sidebarMode">
                        <span class="text-sm">
                            <span class="font-medium text-slate-900 dark:text-slate-100">Use my own sidebar color</span>
                            <span class="block text-slate-500 dark:text-slate-400 text-xs mt-0.5">Keep lightness low so white labels stay readable.</span>
                        </span>
                    </label>
                </fieldset>

                <div class="space-y-4 pt-1" x-show="sidebarMode === 'custom'" x-cloak>
                    <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                        <div class="shrink-0">
                            <span class="t-label block mb-1.5">Color box</span>
                            <label class="block relative w-14 h-14 rounded-xl overflow-hidden ring-1 ring-slate-200 dark:ring-slate-600 shadow-sm cursor-pointer">
                                <input type="color" class="absolute inset-0 w-[150%] h-[150%] -translate-x-2 -translate-y-2 cursor-pointer"
                                       :value="safeSidebar()" @input="sidebarHex = $event.target.value.toUpperCase()" :disabled="sidebarMode === 'follow'">
                            </label>
                        </div>
                        <div class="flex-1 min-w-0">
                            <label class="t-label" for="sidebar_background_color">Color code (optional)</label>
                            <input id="sidebar_background_color" type="text" name="sidebar_background_color" x-model="sidebarHex" maxlength="7"
                                   :disabled="sidebarMode === 'follow'"
                                   class="t-input font-mono uppercase tracking-wide max-w-xs" placeholder="#0F172A" autocomplete="off">
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-900/50 p-4 space-y-3">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-100">Drag sliders to fine-tune</p>
                        <div class="space-y-3">
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Hue</span>
                                <input type="range" min="0" max="360" x-model.number="sbh" @input="sidebarFromSliders()" class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600" :disabled="sidebarMode === 'follow'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(sbh) + '°'"></span>
                            </label>
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Richness</span>
                                <input type="range" min="0" max="100" x-model.number="sbs" @input="sidebarFromSliders()" class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600" :disabled="sidebarMode === 'follow'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(sbs) + '%'"></span>
                            </label>
                            <label class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-28 shrink-0">Lightness</span>
                                <input type="range" min="0" max="100" x-model.number="sbl" @input="sidebarFromSliders()" class="flex-1 h-2 rounded-lg cursor-pointer accent-indigo-600" :disabled="sidebarMode === 'follow'">
                                <span class="text-xs font-mono text-slate-500 w-10 text-right shrink-0" x-text="Math.round(sbl) + '%'"></span>
                            </label>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="c in sidebarPresets" :key="'s-' + c">
                            <button type="button" class="w-9 h-9 rounded-lg ring-1 ring-slate-300 dark:ring-slate-600 shrink-0 transition hover:scale-105"
                                    :style="'background-color:' + c" @click="pickSidebar(c)"></button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="t-btn-primary">Save my display</button>
            </div>
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
        window.__userAppearanceColorUtils = {
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

        window.userAppearanceForm = function (init) {
            return {
                accentMode: init.accentMode,
                accentHex: init.accentHex,
                accentPresets: init.accentPresets,
                bgMode: init.bgMode,
                bgHex: init.bgHex,
                bgPresets: init.bgPresets,
                sidebarMode: init.sidebarMode,
                sidebarHex: init.sidebarHex,
                sidebarPresets: init.sidebarPresets,
                ah: 0, as: 0, al: 0,
                bh: 0, bs: 0, bl: 0,
                sbh: 0, sbs: 0, sbl: 0,
                init: function () {
                    var u = window.__userAppearanceColorUtils;
                    var a = u.hexToHsl(this.accentHex);
                    this.ah = a.h; this.as = a.s; this.al = a.l;
                    var b = u.hexToHsl(this.bgHex);
                    this.bh = b.h; this.bs = b.s; this.bl = b.l;
                    var s = u.hexToHsl(this.sidebarHex);
                    this.sbh = s.h; this.sbs = s.s; this.sbl = s.l;
                    var self = this;
                    this.$watch('accentHex', function (v) {
                        if (self.accentMode !== 'custom' || !/^#[0-9A-Fa-f]{6}$/i.test(v)) return;
                        var o = u.hexToHsl(v);
                        self.ah = o.h; self.as = o.s; self.al = o.l;
                    });
                    this.$watch('bgHex', function (v) {
                        if (self.bgMode !== 'custom' || !/^#[0-9A-Fa-f]{6}$/i.test(v)) return;
                        var o = u.hexToHsl(v);
                        self.bh = o.h; self.bs = o.s; self.bl = o.l;
                    });
                    this.$watch('sidebarHex', function (v) {
                        if (self.sidebarMode !== 'custom' || !/^#[0-9A-Fa-f]{6}$/i.test(v)) return;
                        var o = u.hexToHsl(v);
                        self.sbh = o.h; self.sbs = o.s; self.sbl = o.l;
                    });
                },
                safeAccent: function () { return /^#[0-9A-Fa-f]{6}$/i.test(this.accentHex) ? this.accentHex : '#2563EB'; },
                safeBg: function () { return /^#[0-9A-Fa-f]{6}$/i.test(this.bgHex) ? this.bgHex : '#F0F4F8'; },
                safeSidebar: function () { return /^#[0-9A-Fa-f]{6}$/i.test(this.sidebarHex) ? this.sidebarHex : '#0F172A'; },
                pickAccent: function (c) { this.accentMode = 'custom'; this.accentHex = c; },
                pickBg: function (c) { this.bgMode = 'custom'; this.bgHex = c; },
                pickSidebar: function (c) { this.sidebarMode = 'custom'; this.sidebarHex = c; },
                accentFromSliders: function () {
                    this.accentHex = window.__userAppearanceColorUtils.hslToHex(this.ah, this.as, this.al).toUpperCase();
                },
                bgFromSliders: function () {
                    this.bgHex = window.__userAppearanceColorUtils.hslToHex(this.bh, this.bs, this.bl).toUpperCase();
                },
                sidebarFromSliders: function () {
                    this.sidebarHex = window.__userAppearanceColorUtils.hslToHex(this.sbh, this.sbs, this.sbl).toUpperCase();
                },
            };
        };
    })();
    </script>
    @endpush
</x-tenant-layout>
