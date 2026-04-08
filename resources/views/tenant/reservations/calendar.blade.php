<x-tenant-layout title="Calendar" breadcrumb="Calendar">

    <div class="px-6 py-8 sm:px-10">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Reservation Calendar</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Month and week views of pending and approved bookings.</p>
        </div>
        <div class="t-card p-4">
            <div id="calendar" class="min-h-[480px]"></div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var isDark = document.documentElement.classList.contains('dark');
                const el = document.getElementById('calendar');
                const calendar = new FullCalendar.Calendar(el, {
                    initialView: 'dayGridMonth',
                    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek' },
                    events: @json($events),
                    themeSystem: 'standard',
                });
                calendar.render();

                // Apply dark mode styles inline since FullCalendar doesn't have native dark mode
                if (isDark) {
                    el.style.setProperty('--fc-border-color', '#334155');
                    el.style.setProperty('--fc-button-bg-color', '#334155');
                    el.style.setProperty('--fc-button-border-color', '#475569');
                    el.style.setProperty('--fc-button-text-color', '#f1f5f9');
                    el.style.setProperty('--fc-button-hover-bg-color', '#475569');
                    el.style.setProperty('--fc-button-active-bg-color', '#6366f1');
                    el.style.setProperty('--fc-today-bg-color', 'rgba(99,102,241,0.15)');
                    el.style.setProperty('--fc-page-bg-color', '#0f172a');
                    el.style.setProperty('--fc-neutral-bg-color', '#1e293b');
                    el.style.setProperty('--fc-list-event-hover-bg-color', '#1e293b');
                    el.style.color = '#cbd5e1';
                }
            });
        </script>
    @endpush
</x-tenant-layout>
