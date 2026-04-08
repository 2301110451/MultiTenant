<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal suspended</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <p>Hello,</p>

    <p>
        The barangay portal for <strong>{{ $barangayName }}</strong> has been <strong>suspended or deactivated</strong> by the central administrator.
    </p>

    <p>
        Portal address: <strong>{{ $domain }}</strong><br>
        Link (currently unavailable): <a href="{{ $portalUrl }}">{{ $portalUrl }}</a>
    </p>

    <p>
        Residents and officers cannot use this URL until the portal is set back to active in the central system.
        You will receive another notice when access is restored, if email notifications are enabled.
    </p>

    @if (! empty($subscriptionActionUrl))
        <p>
            <strong>Subscription choice</strong><br>
            If you are a barangay officer, you can use this secure link (valid for 30 days) to tell the central team whether you want to
            <strong>fully unsubscribe</strong> or <strong>request an extension</strong> of your subscription:
        </p>
        <p>
            <a href="{{ $subscriptionActionUrl }}">{{ $subscriptionActionUrl }}</a>
        </p>
    @endif

    <p>Thank you.</p>
</body>
</html>
