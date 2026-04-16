<!DOCTYPE html>
<html>

<head>
    <title>Laporan Bus</title>
    <style>
        body {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>

<body>
    <h2>Laporan Bus</h2>
    <p>Periode: {{ $startDate }} s/d {{ $endDate }}</p>
    <table>
        <thead>
            <tr>
                <th>Nama Bus</th>
                <th>Plat</th>
                <th>Tipe</th>
                <th>Kursi</th>
                <th>Jadwal</th>
                <th>Penumpang</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($buses as $bus)
                <tr>
                    <td>{{ $bus->bus_name }}</td>
                    <td>{{ $bus->plate_number }}</td>
                    <td>{{ $bus->bus_type }}</td>
                    <td>{{ $bus->total_seats }}</td>
                    <td>{{ $bus->total_schedules ?? 0 }}</td>
                    <td>{{ $bus->total_passengers ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
