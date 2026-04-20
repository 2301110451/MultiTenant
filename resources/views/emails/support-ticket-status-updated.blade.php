<x-mail::message>
# Support ticket update

Hello {{ $ticket->requester_name }},

The status of your support ticket has been updated.

**Subject:** {{ $ticket->subject }}

**Previous status:** {{ str_replace('_', ' ', $previousStatus) }}

**New status:** {{ str_replace('_', ' ', $newStatus) }}

@if($ticket->tenant)
**Barangay / tenant:** {{ $ticket->tenant->name }}
@endif

Thank you for using our service.

{{ config('app.name') }}
</x-mail::message>
