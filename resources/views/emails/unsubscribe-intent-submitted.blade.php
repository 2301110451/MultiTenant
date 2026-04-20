@php
    $tenant = $tenant ?? null;
    $domain = $tenant?->domains->first()?->domain;
@endphp

<p>Hello Admin,</p>

<p>
    A barangay submitted a <strong>full unsubscribe</strong> request and is waiting for super admin approval.
</p>

<ul>
    <li><strong>Barangay:</strong> {{ $tenant?->name ?? 'N/A' }}</li>
    <li><strong>Domain:</strong> {{ $domain ?? 'N/A' }}</li>
    <li><strong>Current status:</strong> {{ ucfirst((string) ($tenant?->status ?? 'unknown')) }}</li>
</ul>

@if(! empty($message))
    <p><strong>Officer message:</strong></p>
    <blockquote style="margin:0;padding:10px 12px;border-left:3px solid #94a3b8;background:#f8fafc;color:#334155;">
        {{ $message }}
    </blockquote>
@endif

<p>
    Review and approve/reject this request in the Central panel under <strong>Subscription Requests</strong>.
</p>
