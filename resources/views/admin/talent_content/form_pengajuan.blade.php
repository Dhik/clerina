<!DOCTYPE html>
<html>
<head>
    <title>Talent Contents Report</title>
    <style>
        @page {
            margin: 0.3cm;
        }
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            font-size: 7px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Form Pengajuan</h1>
    <table>
        <thead>
            <tr>
                <th>Nama Talent</th>
                <th>Rate Card Per Slot</th>
                <th>Slot</th>
                <th>Jenis Konten</th>
                <th>Rate Harga</th>
                <th>Besar Diskon</th>
                <th>Harga Setelah Diskon</th>
                <th>NPWP</th>
                <th>PPh (2.5%)</th>
                <th>Final TF</th>
                <th>Total Payment</th>
                <th>Keterangan (DP 50%)</th>
                <th>Nama PIC</th>
                <th>No Rekening</th>
                <th>Nama Bank</th>
                <th>Nama Penerima</th>
                <th>NIK</th>
            </tr>
        </thead>
        <tbody>
            @foreach($talentContents as $content)
                @php
                    $rate_card_per_slot = $content->talent->first_rate_card;
                    $slot = $content->talent->slot_final;
                    $rate_harga = $rate_card_per_slot * $slot;
                    $discount = $content->talent->discount;
                    $harga_setelah_diskon = $rate_harga - $discount;
                    $pph = 0.025 * $harga_setelah_diskon; // 2.5% PPh deduction
                    $final_tf = $harga_setelah_diskon - $pph;
                    $dp = $final_tf / 2; // DP 50%
                @endphp
                <tr>
                    <td>{{ $content->talent->talent_name }}</td>
                    <td>{{ number_format($rate_card_per_slot, 2) }}</td>
                    <td>{{ $slot }}</td>
                    <td>{{ $content->talent->content_type }}</td>
                    <td>{{ number_format($rate_harga, 2) }}</td>
                    <td>{{ number_format($discount, 2) }}</td>
                    <td>{{ number_format($harga_setelah_diskon, 2) }}</td>
                    <td>{{ $content->talent->no_npwp }}</td>
                    <td>{{ number_format($pph, 2) }}</td>
                    <td>{{ number_format($final_tf, 2) }}</td>
                    <td>{{ number_format($final_tf, 2) }}</td>
                    <td>{{ number_format($dp, 2) }}</td>
                    <td>{{ $content->talent->pic }}</td>
                    <td>{{ $content->talent->no_rekening }}</td>
                    <td>{{ $content->talent->bank }}</td>
                    <td>{{ $content->talent->nama_rekening }}</td>
                    <td>{{ $content->talent->nik }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
