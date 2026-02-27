<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #3B82F6;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .info {
            margin-bottom: 15px;
            background: #f3f4f6;
            padding: 10px;
            border-radius: 5px;
        }
        .info p {
            margin: 3px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th {
            background-color: #3B82F6;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        table td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 9px;
        }
        table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .status {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
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
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎓 {{ $title }}</h1>
        <p>SMA Negeri - Sistem Dispensasi</p>
        <p>Dicetak pada: {{ date('d/m/y H:i') }}</p>
    </div>

    <div class="info">
        <p><strong>Filter:</strong></p>
        <p>Status: <strong>{{ $filters['status'] === 'all' ? 'Semua Status' : ucfirst($filters['status']) }}</strong></p>
        @if($filters['tanggal_mulai'] && $filters['tanggal_selesai'])
            <p>Periode: <strong>{{ date('d/m/y', strtotime($filters['tanggal_mulai'])) }} - {{ date('d/m/y', strtotime($filters['tanggal_selesai'])) }}</strong></p>
        @endif
        <p>Total Data: <strong>{{ $dispensasi->count() }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%">No</th>
                <th style="width: 8%">Tanggal</th>
                <th style="width: 15%">Nama Siswa</th>
                <th style="width: 7%">Kelas</th>
                <th style="width: 7%">Jam</th>
                <th style="width: 12%">Mata Pelajaran</th>
                <th style="width: 25%">Keperluan</th>
                <th style="width: 8%">Status</th>
                <th style="width: 15%">Disetujui Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dispensasi as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ date('d/m/y', strtotime($item->tanggal)) }}</td>
                <td>{{ $item->siswa->name }}</td>
                <td>{{ $item->kelas->nama_kelas }}</td>
                <td>{{ $item->jam_mulai }} - {{ $item->jam_selesai }}</td>
                <td>{{ $item->mata_pelajaran }}</td>
                <td>{{ Str::limit($item->keperluan, 80) }}</td>
                <td>
                    <span class="status status-{{ $item->status }}">
                        @if($item->status === 'pending') Menunggu
                        @elseif($item->status === 'approved') Disetujui
                        @else Ditolak
                        @endif
                    </span>
                </td>
                <td>{{ $item->approver->name ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh Sistem Dispensasi SMA Negeri</p>
    </div>
</body>
</html>
