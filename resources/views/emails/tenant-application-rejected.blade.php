<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant application update</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <p>Hello,</p>

    <p>
        Your tenant application for <strong>{{ $barangayName }}</strong> was reviewed by the super admin and was <strong>not approved</strong> at this time.
    </p>

    @if(! empty($reason))
        <p>
            Reason from super admin:<br>
            <strong>{{ $reason }}</strong>
        </p>
    @endif

    <p>
        Requested portal hostname reference: <strong>{{ $portalDomainHint }}</strong>
    </p>

    <p>Please coordinate with the central admin team if you want to submit a new application.</p>

    <p>Thank you.</p>
</body>
</html>
