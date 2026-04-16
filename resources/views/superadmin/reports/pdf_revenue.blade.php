<!DOCTYPE html>
<html>

<head>
    <title>Laporan Pendapatan</title>
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
    <h2>Laporan Pendapatan</h2>
    <p>Periode: {{ $startDate }} s/d {{ $endDate }}</p>
    <p><strong>Total Pendapatan: Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong></p>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($revenueData as $item)
                <tr>
                    <td>{{ $item->date }}</td>
                    <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
