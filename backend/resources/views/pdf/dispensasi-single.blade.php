<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Dispensasi - {{ $dispensasi->siswa->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            line-height: 1.6;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .kop-surat h1 {
            margin: 0;
            font-size: 24px;
            color: #3B82F6;
        }
        .kop-surat h2 {
            margin: 5px 0;
            font-size: 18px;
        }
        .kop-surat p {
            margin: 3px 0;
            font-size: 12px;
            color: #666;
        }
        .title {
            text-align: center;
            margin: 30px 0;
        }
        .title h3 {
            margin: 0;
            font-size: 16px;
            text-decoration: underline;
        }
        .title p {
            margin: 5px 0;
            font-size: 12px;
        }
        .content {
            margin: 20px 0;
        }
        .info-table {
            width: 100%;
            margin: 20px 0;
        }
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        .info-table td:first-child {
            width: 200px;
            font-weight: bold;
        }
        .info-table td:nth-child(2) {
            width: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 12px;
        }
        .status-pending {
            background: #FEF3C7;
            color: #92400E;
        }
        .status-approved {
            background: #D1FAE5;
            color: #065F46;
        }
        .status-rejected {
            background: #FEE2E2;
            color: #991B1B;
        }
        .approval-section {
            margin-top: 40px;
            padding: 15px;
            background: #f9fafb;
            border-left: 4px solid #3B82F6;
        }
        .signature {
            margin-top: 50px;
        }
        .signature-box {
            display: inline-block;
            text-align: center;
            width: 200px;
        }
        .signature-box p {
            margin: 5px 0;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 60px;
        }
        .right {
            float: right;
        }
        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Kop Surat -->
    <div class="kop-surat">
        <h1>🎓 SMA NEGERI</h1>
        <h2>SISTEM DISPENSASI SISWA</h2>
        <p>Alamat: Jl. Pendidikan No. 123, Kota, Provinsi</p>
        <p>Telp: (021) 1234567 | Email: info@smaku.sch.id</p>
    </div>

    <!-- Title -->
    <div class="title">
        <h3>SURAT DISPENSASI</h3>
        <p>Nomor: {{ str_pad($dispensasi->id, 5, '0', STR_PAD_LEFT) }}/DISP/{{ date('Y') }}</p>
    </div>

    <!-- Content -->
    <div class="content">
        <p>Yang bertanda tangan di bawah ini, memberikan dispensasi kepada:</p>

        <table class="info-table">
            <tr>
                <td>Nama Siswa</td>
                <td>:</td>
                <td><strong>{{ $dispensasi->siswa->name }}</strong></td>
            </tr>
            <tr>
                <td>NISN</td>
                <td>:</td>
                <td>{{ $dispensasi->siswa->nisn }}</td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td>:</td>
                <td>{{ $dispensasi->kelas->nama_kelas }}</td>
            </tr>
            <tr>
                <td>Tanggal Dispensasi</td>
                <td>:</td>
                <td>{{ date('d/m/y', strtotime($dispensasi->tanggal)) }}</td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td>:</td>
                <td>{{ $dispensasi->jam_mulai }} - {{ $dispensasi->jam_selesai }}</td>
            </tr>
            <tr>
                <td>Mata Pelajaran Ditinggalkan</td>
                <td>:</td>
                <td>{{ $dispensasi->mata_pelajaran }}</td>
            </tr>
            <tr>
                <td>Keperluan</td>
                <td>:</td>
                <td>{{ $dispensasi->keperluan }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>:</td>
                <td>
                    <span class="status-badge status-{{ $dispensasi->status }}">
                        @if($dispensasi->status === 'pending') MENUNGGU PERSETUJUAN
                        @elseif($dispensasi->status === 'approved') DISETUJUI
                        @else DITOLAK
                        @endif
                    </span>
                </td>
            </tr>
        </table>

        @if($dispensasi->status !== 'pending')
        <div class="approval-section">
            <p><strong>Informasi Persetujuan:</strong></p>
            <table class="info-table">
                <tr>
                    <td>Diproses Oleh</td>
                    <td>:</td>
                    <td>{{ $dispensasi->approver->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Waktu Diproses</td>
                    <td>:</td>
                    <td>{{ date('d/m/y H:i', strtotime($dispensasi->updated_at)) }}</td>
                </tr>
                @if($dispensasi->catatan)
                <tr>
                    <td>Catatan</td>
                    <td>:</td>
                    <td>{{ $dispensasi->catatan }}</td>
                </tr>
                @endif
            </table>
        </div>
        @endif
    </div>

    <!-- Signature -->
    <div class="signature">
        <div class="signature-box right">
            <p>{{ $dispensasi->kelas->nama_kelas ?? 'Kota' }}, {{ date('d/m/y') }}</p>
            <p><strong>{{ $dispensasi->status === 'approved' ? 'Menyetujui' : 'Petugas Kesiswaan' }}</strong></p>
            <div class="signature-line"></div>
            <p><strong>{{ $dispensasi->approver->name ?? '(...............................)' }}</strong></p>
            <p>NIP. {{ $dispensasi->approver->nip ?? '................................' }}</p>
        </div>
    </div>

    <div style="clear: both;"></div>

    <!-- Footer -->
    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis pada {{ date('d/m/y H:i') }}</p>
        <p>Sistem Dispensasi SMA Negeri - Dokumen sah dengan atau tanpa tanda tangan basah</p>
    </div>
</body>
</html>
