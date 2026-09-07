<!-- resources/views/pdf/struk.blade.php -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Struk Pembayaran - {{ $transaksi->kode_transaksi }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 14px;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #ccc;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .details {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .details th,
        .details td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #eee;
        }

        .total {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>KelapaDua Sports</h2>
        <p>Bukti Pembayaran Lunas</p>
    </div>

    <table class="details">
        <tr>
            <th>Kode Transaksi</th>
            <td>{{ $transaksi->kode_transaksi }}</td>
        </tr>
        <tr>
            <th>Nama Penyewa</th>
            <td>{{ $transaksi->nama_penyewa }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $transaksi->email_penyewa }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td style="color: green; font-weight: bold;">LUNAS</td>
        </tr>
    </table>

    <div class="total">
        Total Pembayaran: Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}
    </div>
</body>

</html>
