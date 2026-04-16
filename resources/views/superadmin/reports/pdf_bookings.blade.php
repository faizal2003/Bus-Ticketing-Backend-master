<!DOCTYPE html>
<html>

<head>
    <title>Laporan Pemesanan</title>
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
    <h2>Laporan Pemesanan</h2>
    <p>Periode: {{ $startDate }} s/d {{ $endDate }}</p>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Pelanggan</th>
                <th>Rute</th>
                <th>Berangkat</th>
                <th>Jumlah</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bookings as $b)
                <tr>
                    <td>{{ $b->booking_code }}</td>
                    <td>{{ $b->user->name ?? 'N/A' }}</td>
                    <td>{{ $b->schedule->departure_city ?? '' }} → {{ $b->schedule->arrival_city ?? '' }}</td>
                    <td>{{ optional($b->schedule)->departure_time ? $b->schedule->departure_time->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td>{{ $b->total_passengers }}</td>
                    <td>{{ number_format($b->total_price, 0, ',', '.') }}</td>
                    <td>{{ $b->booking_status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
