<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant Report Export</title>
</head>
<body>
    <table border="1">
        <tbody>
            @foreach($rows as $row)
                <tr>
                    @forelse($row as $cell)
                        <td>{{ $cell }}</td>
                    @empty
                        <td></td>
                    @endforelse
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
