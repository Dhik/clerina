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
                <th>Username</th>
                <th>Rate Card Per Slot</th>
                <th>Slot</th>
                <th>Jenis Konten</th>
                <th>Rate Harga</th>
                <th>Besar Diskon</th>
                <th>Harga Setelah Diskon</th>
                <th>NPWP</th>
                <th>
                    @if($talentContents->first()->isPTorCV)
                        PPh23 (2%)
                    @else
                        PPh21 (2,5%)
                    @endif
                </th>
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
            @php
                $totalTransfer = 0;
            @endphp
            @foreach($talentContents as $content)
                @php
                    $rate_card_per_slot = $content->talent->price_rate;
                    $slot = $content->talent->slot_final;
                    $rate_harga = $rate_card_per_slot * $slot;
                    $discount = $content->talent->discount;
                    $harga_setelah_diskon = $rate_harga - $discount;
                    $pphPercentage = $content->isPTorCV ? 0.02 : 0.025;
                    $pphAmount = $harga_setelah_diskon * $pphPercentage;
                    $final_tf = $harga_setelah_diskon - $pphAmount;
                    $dp = $final_tf / 2; // DP 50%
                @endphp
                <tr>
                    <td>{{ $content->talent->username }}</td>
                    <td>{{ number_format($rate_card_per_slot, 2) }}</td>
                    <td>{{ $slot }}</td>
                    <td>{{ $content->talent->content_type }}</td>
                    <td>{{ number_format($rate_harga, 2) }}</td>
                    <td>{{ number_format($discount, 2) }}</td>
                    <td>{{ number_format($harga_setelah_diskon, 2) }}</td>
                    <td>{{ $content->talent->no_npwp }}</td>
                    <td>{{ number_format($pphAmount, 2) }}</td>
                    <td>{{ number_format($final_tf, 2) }}</td>
                    <td>
                        @php
                            $displayValue = $final_tf;
                            if (in_array($content->status_payment, ["Termin 1", "Termin 3", "Termin 2"])) {
                                $displayValue = $final_tf / 3;
                            } elseif ($content->status_payment === "DP 50%") {
                                $displayValue = $final_tf / 2;
                            } elseif ($content->status_payment === "Full Payment") {
                                $displayValue = $final_tf;
                            } elseif ($content->status_payment === "Pelunasan 50%") {
                                $displayValue = $final_tf / 2;
                            }
                            $totalTransfer += $displayValue;
                        @endphp
                        {{ number_format($displayValue, 2) }}
                    </td>
                    <td>{{ $content->status_payment }}</td>
                    <td>{{ $content->talent->pic }}</td>
                    <td>{{ $content->talent->no_rekening }}</td>
                    <td>{{ $content->talent->bank }}</td>
                    <td>{{ $content->talent->nama_rekening }}</td>
                    <td>{{ $content->talent->nik }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="10" style="text-align: right;">Total Transfer:</td>
                <td>{{ number_format($totalTransfer, 2) }}</td>
                <td colspan="6"></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
