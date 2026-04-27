<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tiket - {{ $booking->ticket->ticket_code }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 20px;
        }
        .ticket {
            border: 2px solid #1E88E5;
            border-radius: 10px;
            overflow: hidden;
            max-width: 700px;
            margin: 0 auto;
        }
        .header {
            background-color: #1E88E5;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
        }
        .row {
            clear: both;
            margin-bottom: 15px;
        }
        .col-6 {
            width: 50%;
            float: left;
        }
        .label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .value {
            font-size: 16px;
            font-weight: bold;
        }
        .route-info {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .qr-section {
            text-align: center;
            padding: 20px;
            border-top: 1px dashed #ccc;
        }
        .qr-code {
            margin-bottom: 10px;
        }
        .ticket-footer {
            background-color: #f9f9f9;
            padding: 15px;
            font-size: 11px;
            color: #777;
            text-align: center;
            border-top: 1px solid #eee;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="ticket">
            <div class="header">
                <h1>TIKET BUS ONLINE</h1>
                <p>Booking ID: {{ $booking->booking_code }}</p>
            </div>
            
            <div class="content">
                <div class="route-info">
                    <div class="row">
                        <div class="col-6">
                            <div class="label">KEBERANGKATAN</div>
                            <div class="value">{{ $booking->schedule->departure_city }}</div>
                            <div class="label" style="margin-top: 5px;">{{ $booking->schedule->departure_time->format('d M Y, H:i') }} WIB</div>
                        </div>
                        <div class="col-6">
                            <div class="label">KEDATANGAN</div>
                            <div class="value">{{ $booking->schedule->arrival_city }}</div>
                            <div class="label" style="margin-top: 5px;">{{ $booking->schedule->arrival_time->format('d M Y, H:i') }} WIB</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="label">NAMA BUS</div>
                        <div class="value">{{ $booking->schedule->bus->bus_name }}</div>
                    </div>
                    <div class="col-6">
                        <div class="label">NOMOR BUS / TIPE</div>
                        <div class="value">{{ $booking->schedule->bus->bus_number }} / {{ $booking->schedule->bus->bus_type }}</div>
                    </div>
                </div>

                <div class="row" style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
                    <h3 style="margin-top: 0; font-size: 16px;">INFORMASI PENUMPANG</h3>
                    @foreach($booking->passengers as $index => $passenger)
                        <div style="margin-bottom: 10px; border-bottom: 1px solid #f0f0f0; padding-bottom: 5px;">
                            <div class="row">
                                <div class="col-6">
                                    <div class="label">NAMA PENUMPANG {{ $index + 1 }}</div>
                                    <div class="value">{{ $passenger->full_name }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="label">NOMOR KURSI</div>
                                    <div class="value" style="color: #1E88E5; font-size: 18px;">{{ $passenger->seat_number }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="qr-section">
                    <div class="qr-code">
                        <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(150)->generate($booking->ticket->generateQrData())) }}">
                    </div>
                    <div class="value" style="color: #1E88E5; letter-spacing: 2px;">{{ $booking->ticket->ticket_code }}</div>
                    <p class="label">Tunjukkan QR Code ini kepada petugas saat boarding</p>
                </div>
            </div>

            <div class="ticket-footer">
                <p><strong>Catatan Penting:</strong></p>
                <p>1. Harap hadir 30 menit sebelum jadwal keberangkatan.</p>
                <p>2. Tiket ini merupakan bukti pembayaran yang sah.</p>
                <p>3. Dilarang membawa barang-barang terlarang dan berbahaya.</p>
                <p>4. Informasi lebih lanjut hubungi Support: +62 812-3456-7890</p>
            </div>
        </div>
    </div>
</body>
</html>
