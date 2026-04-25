<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Approved</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <p>Hello,</p>

    <p>
        Your barangay <strong>{{ $barangayName }}</strong> has been registered and approved by the super admin.
        The barangay portal is ready to use.
    </p>

    <p>
        Barangay domain: <strong>{{ $domain }}</strong><br>
        Portal link:
        <a href="{{ $portalUrl }}">{{ $portalUrl }}</a>
    </p>

    <p><strong>Login credentials:</strong></p>
    <ul>
        @if(!empty($tenantAdminEmail))
            <li>
                <strong>Tenant Admin Gmail:</strong> {{ $tenantAdminEmail }}<br>
                <strong>Tenant Admin Password:</strong> {{ $tenantAdminPassword ?: '—' }}
            </li>
        @endif
        @if(!empty($staffEmail))
            <li style="margin-top: 8px;">
                <strong>Staff Gmail:</strong> {{ $staffEmail }}<br>
                <strong>Staff Password:</strong> {{ $staffPassword ?: '—' }}
            </li>
        @endif
    </ul>

    <p>Sign in at that address using the credentials above.</p>

    <p>Thank you.</p>
</body>
</html>
