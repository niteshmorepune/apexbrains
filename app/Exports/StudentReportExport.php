<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private ?string $search = null, private ?int $levelId = null) {}

    public function collection(): Collection
    {
        $query = Student::with(['currentLevel', 'examAttempts'])
            ->where('is_active', true);

        if ($this->search) {
            $query->where(fn($q) => $q->where('first_name', 'like', '%' . $this->search . '%')
                ->orWhere('last_name', 'like', '%' . $this->search . '%'));
        }
        if ($this->levelId) {
            $query->where('current_level_id', $this->levelId);
        }

        return $query->get()->map(function ($s) {
            $s->avg_score  = round($s->examAttempts->avg('percentage') ?? 0, 1);
            $s->exam_count = $s->examAttempts->count();
            $s->last_score = round($s->examAttempts->sortByDesc('submitted_at')->first()?->percentage ?? 0, 1);
            return $s;
        });
    }

    public function headings(): array
    {
        return ['Student Code', 'Name', 'Level', 'Exams Taken', 'Avg Score (%)', 'Last Score (%)'];
    }

    public function map($row): array
    {
        return [
            $row->student_code,
            $row->full_name,
            $row->currentLevel?->title ?? '—',
            $row->exam_count,
            $row->exam_count ? $row->avg_score : '—',
            $row->exam_count ? $row->last_score : '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
