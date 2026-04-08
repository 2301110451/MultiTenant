<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extension approved</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <p>Hello,</p>

    <p>
        The central administrator has <strong>approved</strong> your barangay&rsquo;s request to <strong>extend</strong> access.
        You can <strong>continue using your barangay portal and accounts</strong> as normal.
    </p>

    <p>
        Barangay: <strong>{{ $barangayName }}</strong><br>
        Portal: <a href="{{ $portalUrl }}">{{ $portalUrl }}</a><br>
        Domain: <strong>{{ $domain }}</strong>
    </p>

    <p>If you have questions, contact your central administrator.</p>

    <p>Thank you.</p>
</body>
</html>
