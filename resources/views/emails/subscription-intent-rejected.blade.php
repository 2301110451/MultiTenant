<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request update</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <p>Hello,</p>

    <p>
        The central administrator has <strong>not approved</strong> your recent request
        (<strong>{{ $intentLabel }}</strong>) for <strong>{{ $barangayName }}</strong>.
    </p>

    <p>
        Your tenant portal remains in its <strong>current state</strong> (for example, if it was suspended, it stays suspended until the central team changes it).
        This decision is final for this request; you may contact the central office if you need clarification.
    </p>

    <p>
        Portal address (for reference): <a href="{{ $portalUrl }}">{{ $portalUrl }}</a><br>
        Domain: <strong>{{ $domain }}</strong>
    </p>

    <p>Thank you.</p>
</body>
</html>
