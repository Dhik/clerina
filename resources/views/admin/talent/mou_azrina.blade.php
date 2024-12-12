<!DOCTYPE html>
<html>
<head>
    <title>Perjanjian Kerjasama</title>
    <style>
        @page {
            margin-top: 0.5in;
            margin-bottom: 0.5in;   
            margin-left: 1in;
            margin-right: 0.7in;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.5;
        }
        .header {
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
            border: none; /* Remove border for table cells */
        }
        .logo {
            width: 150px;
        }
        h1 {
            font-size: 20pt; /* Increased from 14pt to 17pt (3px larger) */
            margin: 0;
            font-weight: normal; /* Changed from bold to normal */
        }
        h2 {
            font-size: 15pt;
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
        }
        .company-info {
            font-size: 9pt;
            margin: 0;
        }
        .content-section {
            margin-top: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 5px;
        }
        th {
            background-color: #f2f2f2;
        }
        .no-border td {
            border: none;
            padding: 2px;
        }
        .indent {
            padding-left: 20px;
        }
        .highlight {
            background-color: yellow;
        }
        .signature-section {
            margin-top: 50px;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            border: none; /* Remove border for table cells */
            padding: 5px; /* Add some padding for spacing */
        }
        .footer {
            position: fixed;
            bottom: 0.5in;
            right: 0.5in;
            font-size: 10pt;
        }

        /* Page-specific footers */
        .page1 .footer::after { content: "Page 1 of 4"; }
        .page2 .footer::after { content: "Page 2 of 4"; }
        .page3 .footer::after { content: "Page 3 of 4"; }
        .page4 .footer::after { content: "Page 4 of 4"; }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Page 1 -->
    <div class="page1">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td style="width: 30%;">
                        <img src="{{ public_path('img/pt_azrina.png') }}" alt="Company Logo" class="logo">
                    </td>
                    <td>
                        <h1>PT LISA GLOBAL CANTIKA</h1>
                    </td>
                </tr>
            </table>
        </div>

        <p class="indent">
        Pada tanggal 11 September 2024 dibuat dan ditandatangani perjanjian kerjasama untuk
program endorsement, yang selanjutnya disebut juga sebagai “<strong>PERJANJIAN KERJA SAMA</strong>” antara :
        </p>

        <div class="content-section">
            <table class="no-border">
                <tr>
                    <td style="width: 40%;">Nama Perusahaan</td>
                    <td>: PT Lisa Global Cantika</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>: Ruko Garden City No 05 ( Ruko Warna Pink ) Cipagalo, Bojongsoang, Bandung</td>
                </tr>
                <tr>
                    <td>Kontak resmi</td>
                    <td>: Fahry Husein</td>
                </tr>
                <tr>
                    <td>No. Telepon</td>
                    <td>: 085173069356</td>
                </tr>
            </table>
            <p>Selanjutnya dalam hal ini disebut sebagai <strong>PIHAK PERTAMA.</strong></p>
        </div>

        <div class="content-section">
            <table class="no-border">
                <tr>
                    <td style="width: 40%;">Nama KOL</td>
                    <td>: {{ $talent->talent_name }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>: {{ $talent->address }}</td>
                </tr>
                <tr>
                    <td>No. Telepon</td>
                    <td>: {{ $talent->phone_number }}</td>
                </tr>
            </table>
            <p>Selanjutnya dalam hal ini disebut sebagai <strong>PIHAK KEDUA.</strong><p>
        </div>

        <div class="content-section">
            <table class="no-border">
                <tr>
                    <td><strong>PIHAK PERTAMA</strong> dan <strong>PIHAK KEDUA</strong> untuk selanjutnya disebut <strong>PARA PIHAK</strong>.</td>
                </tr>
            </table>
        </div>

        <h2>PASAL 1</h2>
        <h2>Kesepakatan Kerjasama</h2>

        <p class="indent">
        Dalam surat perjanjian kerjasama ini, <strong>PARA PIHAK</strong> telah setuju untuk mengadakan
        kerjasama endorsement pada media sosial <strong>PIHAK KEDUA</strong>.
        </p>

        <div class="content-section">
            <table class="no-border">
                <tr>
                    <td style="width: 30%;">Nama Akun</td>
                    <td>: {{ $talent->username }}</td>
                </tr>
                <tr>
                    <td>Jumlah Followers</td>
                    <td>: {{ number_format($talent->followers, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>SOW</td>
                    <td>: {{ $talent->scope_of_work }}</td>
                </tr>
                <tr>
                    <td>Masa Kerjasama</td>
                    <td>: {{ $talent->masa_kerjasama }}</td>
                </tr>
                <tr>
                    <td>Biaya Endorsement</td>
                    <td>: {{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="footer"></div>
    </div>

    <div class="page-break"></div>

    <!-- Page 2 -->
    <div class="page2">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td style="width: 30%;">
                        <img src="{{ public_path('img/pt_azrina.png') }}" alt="Company Logo" class="logo">
                    </td>
                    <td>
                        <h1>PT LISA GLOBAL CANTIKA</h1>
                    </td>
                </tr>
            </table>
        </div>

        <h2>PASAL 2</h2>
        <h2>Kewajiban Para Pihak</h2>

        <div class="content-section">
            <ol>
                <li><strong>PIHAK PERTAMA</strong> memberikan dan menyediakan produk yang telah disepakati dengan <strong>PIHAK KEDUA</strong> serta memberikan info terkait video yang diberikan.</li>
                <li><strong>PIHAK KEDUA</strong> wajib memposting {{ $talent->scope_of_work }} untuk brand dalam waktu {{ $talent->masa_kerjasama }}. </li>
                <li class="highlight"><strong>PIHAK KEDUA</strong> wajib mempertahankan video yang telah diunggah di platform terkait dan tidak diperkenankan untuk menghapus, menyunting, mengarsipkan atau menonaktifkan video tersebut dengan alasan apapun, kecuali video tersebut melanggar ketentuan hukum yang berlaku di Indonesia.</li>
                <li><strong>PIHAK KEDUA</strong> harus menyerahkan perkiraan rencana draft promosi video atau siaran langsung dan data yang terkait dengan produk kepada <strong>PIHAK PERTAMA</strong> sesuai jadwal yang disepakati.</li>
                <li>Jika <strong>PIHAK KEDUA</strong> tidak melakukan kewajiban seperti tidak memposting video sesuai jadwal yang telah disepakati bersama, tidak ada respon apapun mengenai kemuduran posting video atau membatalkan kerjasama secara sepihak maka <strong>PIHAK KEDUA</strong> wajib melakukan pengembalian dana secara materil dengan sejumlah sisa slot video yang belum terposting sebanyak 5x (lima kali) lipat dari nominal harga yang sudah disepakati.</li>
                <li>Apabila dalam pelaksanaan kerjasama terdapat hal-hal yang tidak sesuai dengan kesepakatan yang telah dibuat, maka pihak yang melanggar bersedia untuk menerima konsekuensi Hukum yang berlaku di Negara Kesatuan Republik Indonesia.</li>
            </ol>
        </div>

        <div class="footer"></div>
    </div>

    <div class="page-break"></div>

    <!-- Page 3 -->
    <div class="page3">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td style="width: 30%;">
                        <img src="{{ public_path('img/pt_azrina.png') }}" alt="Company Logo" class="logo">
                    </td>
                    <td>
                        <h1>PT LISA GLOBAL CANTIKA</h1>
                    </td>
                </tr>
            </table>
        </div>

        <h2>PASAL 3</h2>
        <h2>Biaya dan Pembayaran</h2>

        <div class="content-section">
            <table class="no-border">
                <tr>
                    <td style="width: 50%;">Rekening yang ditunjuk dari pihak pengirim</td>
                    <td></td>
                </tr>    
                <tr>
                    <td style="width: 30%;">Pengirim</td>
                    <td>: PT Lisa Global Cantika</td>
                </tr>
                <tr>
                    <td>Rekening Pengirim</td>
                    <td>: 3372378571</td>
                </tr>
                <tr>
                    <td>Bank Pengirim</td>
                    <td>: BCA</td>
                </tr>
            </table>
        </div>
        <div class="content-section">
            <table class="no-border">
                <tr>
                    <td style="width: 50%;">Rekening yang ditunjuk dari <strong>PIHAK KEDUA</strong></td>
                    <td></td>
                </tr>    
                <tr>
                    <td style="width: 30%;">Penerima</td>
                    <td>: {{ $talent->nama_rekening }}</td>
                </tr>
                <tr>
                    <td>Rekening Penerima</td>
                    <td>: {{ $talent->no_rekening }}</td>
                </tr>
                <tr>
                    <td>Bank Penerima</td>
                    <td>: {{ $talent->bank }}</td>
                </tr>
                <tr>
                    <td>No. NPWP</td>
                    <td>: {{ $talent->no_npwp }}</td>
                </tr>
                <tr>
                    <td>NIK</td>
                    <td>: {{ $talent->nik }}</td>
                </tr>
            </table>
        </div>

        <h2>PASAL 4</h2>
        <h2>Ketentuan dan Privasi</h2>

        <div class="content-section">
            <p>
                Sejak tanggal penandatanganan kontrak ini hingga akhir kontrak ini, semua ketentuan kontrak ini, dan semua materi dan informasi dari pihak lain (termasuk rahasia dagang, rencana perusahaan, kegiatan operasional, informasi keuangan dan rahasia dagang lainnya, dan lain-lain). <strong>PIHAK PERTAMA</strong> dan <strong>PIHAK KEDUA</strong> tidak boleh mengungkapkan kepada pihak lainnya sebelum memperoleh persetujuan dari kedua belah pihak. Pihak yang mengungkapkan akan bertanggung jawab atas pelanggaran kontrak dan memberikan kompensasi kepada pihak lainnya atas kerugian secara materil yang diakibatkannya.
            </p>
        </div>

        <div class="footer"></div>
    </div>

    <div class="page-break"></div>

    <!-- Page 4 -->
    <div class="page4">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td style="width: 30%;">
                        <img src="{{ public_path('img/pt_azrina.png') }}" alt="Company Logo" class="logo">
                    </td>
                    <td>
                        <h1>PT LISA GLOBAL CANTIKA</h1>
                    </td>
                </tr>
            </table>
        </div>

        <div class="content-section">
            <p>
                Kontrak ini ditandatangani oleh kedua belah pihak dan akan efektif setelah ditandatangani. Kontrak tambahan akan ditandatangani jika kontrak tidak mencakup hal-hal yang perlu direvisi atau diubah.
            </p>
        </div>

        <div class="signature-section">
            <p style="text-align: center;">{{ $tanggal_hari_ini }}</p>
            <table class="signature-table">
                <tr>
                    <td>Pihak Pertama</td>
                    <td>Pihak Kedua</td>
                </tr>
                <tr>
                    <td style="height: 80px;"></td>
                    <td style="height: 80px;">
                        <!-- You can add an image of the signature here if needed -->
                        <!-- <img src="{{ public_path('path/to/signature.png') }}" alt="Signature" style="height: 60px;"> -->
                    </td>
                </tr>
                <tr style="font-weight: bold; text-decoration: underline;">
                    <td>Fahry Husein</td>
                    <td>{{ $talent->talent_name }}</td>
                </tr>
                <tr>
                    <td>CEO Office</td>
                    <td>KOL</td>
                </tr>
            </table>
        </div>

        <div class="footer"></div>
    </div>
</body>
</html>
