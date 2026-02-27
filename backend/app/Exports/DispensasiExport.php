<?php

namespace App\Exports;

use App\Models\Dispensasi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DispensasiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Dispensasi::with(['siswa', 'kelas', 'approver']);

        // Apply filters
        if (isset($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['kelas_id'])) {
            $query->where('kelas_id', $this->filters['kelas_id']);
        }

        if (isset($this->filters['tanggal_mulai'])) {
            $query->whereDate('tanggal', '>=', $this->filters['tanggal_mulai']);
        }

        if (isset($this->filters['tanggal_selesai'])) {
            $query->whereDate('tanggal', '<=', $this->filters['tanggal_selesai']);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Nama Siswa',
            'Kelas',
            'NISN',
            'Jam Mulai',
            'Jam Selesai',
            'Mata Pelajaran',
            'Keperluan',
            'Status',
            'Disetujui Oleh',
            'Catatan',
            'Waktu Pengajuan',
        ];
    }

    public function map($dispensasi): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $dispensasi->tanggal ? date('d/m/Y', strtotime($dispensasi->tanggal)) : '-',
            $dispensasi->siswa->name ?? '-',
            $dispensasi->kelas->nama_kelas ?? '-',
            $dispensasi->siswa->nisn ?? '-',
            $dispensasi->jam_mulai ?? '-',
            $dispensasi->jam_selesai ?? '-',
            $dispensasi->mata_pelajaran ?? '-',
            $dispensasi->keperluan ?? '-',
            $this->getStatusText($dispensasi->status),
            $dispensasi->approver->name ?? '-',
            $dispensasi->catatan ?? '-',
            $dispensasi->created_at ? $dispensasi->created_at->format('d/m/Y H:i') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3B82F6']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 15,
            'C' => 25,
            'D' => 12,
            'E' => 15,
            'F' => 12,
            'G' => 12,
            'H' => 20,
            'I' => 40,
            'J' => 12,
            'K' => 25,
            'L' => 30,
            'M' => 20,
        ];
    }

    private function getStatusText($status)
    {
        $texts = [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak'
        ];
        return $texts[$status] ?? $status;
    }
}
