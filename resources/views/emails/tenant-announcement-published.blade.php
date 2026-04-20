<x-mail::message>
# New announcement from {{ $tenantName }}

**{{ $title }}**

{{ $messageBody }}

@if($publishedAt)
Published at: {{ $publishedAt }}
@endif

Please log in to your barangay portal dashboard to view this and other updates.

{{ config('app.name') }}
</x-mail::message>
