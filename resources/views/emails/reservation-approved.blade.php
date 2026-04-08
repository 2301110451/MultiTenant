<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Approved</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <p>Hello {{ $reservation->user?->name ?? 'Resident' }},</p>

    <p>
        Good news. Your reservation request has been <strong>approved</strong>.
    </p>

    <p>
        <strong>Facility:</strong> {{ $reservation->facility?->name ?? 'N/A' }}<br>
        <strong>Start:</strong> {{ optional($reservation->starts_at)->format('M d, Y h:i A') }}<br>
        <strong>End:</strong> {{ optional($reservation->ends_at)->format('M d, Y h:i A') }}
    </p>

    @if($reservation->approver?->name)
        <p><strong>Approved by:</strong> {{ $reservation->approver->name }}</p>
    @endif

    <p>Please log in to your barangay portal to view full reservation details.</p>

    <p>Thank you.</p>
</body>
</html>
