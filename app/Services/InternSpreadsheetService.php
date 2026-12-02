<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Intern;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InternSpreadsheetService
{
    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $writer = new Writer;
            $writer->openToFile('php://output');

            // Sheet 1: Data Intern
            $sheet1 = $writer->getCurrentSheet();
            $sheet1->setName('Data Intern');

            $writer->addRow(Row::fromValues(['Nama Lengkap', 'Departemen', 'Divisi']));
            $writer->addRow(Row::fromValues(['Contoh: Budi Santoso', 'Education', 'Academic & Development']));

            // Sheet 2: Referensi Departemen & Divisi
            $sheet2 = $writer->addNewSheetAndMakeItCurrent();
            $sheet2->setName('Referensi');
            $writer->addRow(Row::fromValues(['Departemen', 'Singkatan', 'Divisi yang Tersedia']));

            $departments = Department::orderBy('name')->get();
            foreach ($departments as $dept) {
                $subs = Department::SUB_DIVISION_MAPPING[$dept->name] ?? [];
                if (empty($subs)) {
                    $writer->addRow(Row::fromValues([$dept->name, $dept->abbreviation ?? '-', '-']));
                } else {
                    foreach ($subs as $sub) {
                        $writer->addRow(Row::fromValues([$dept->name, $dept->abbreviation ?? '-', $sub]));
                    }
                }
            }

            $writer->close();
        }, 'template_data_intern.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @return array{created: int, updated: int, errors: list<string>}
     */
    public function importFromFile(string $filePath): array
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $reader = $this->makeReader($filePath, $ext);
        $reader->open($filePath);

        $departments = Department::all();
        $created = 0;
        $updated = 0;
        $errors = [];

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                $rowIndex = 0;
                foreach ($sheet->getRowIterator() as $row) {
                    $rowIndex++;
                    $cells = array_map(fn ($v) => trim((string) $v), $row->toArray());

                    // Skip empty rows
                    if (empty(array_filter($cells, fn ($v) => $v !== ''))) {
                        continue;
                    }

                    // Skip header row or example row
                    $firstCell = strtolower($cells[0] ?? '');
                    if ($rowIndex === 1 && (str_contains($firstCell, 'nama') || str_contains($firstCell, 'name'))) {
                        continue;
                    }
                    if (str_starts_with($firstCell, 'contoh:')) {
                        continue;
                    }

                    $name = $cells[0] ?? '';
                    $deptInput = $cells[1] ?? '';
                    $subInput = $cells[2] ?? '';

                    if ($name === '' || $deptInput === '') {
                        $errors[] = "Baris {$rowIndex}: Nama Lengkap dan Departemen wajib diisi.";

                        continue;
                    }

                    $department = $this->resolveDepartment($departments, $deptInput);
                    if (! $department) {
                        $errors[] = "Baris {$rowIndex} ({$name}): Departemen '{$deptInput}' tidak ditemukan.";

                        continue;
                    }

                    $subDivision = $this->resolveSubDivision($department->name, $subInput);
                    if ($subInput !== '' && $subInput !== '-' && $subDivision === null) {
                        $errors[] = "Baris {$rowIndex} ({$name}): Divisi '{$subInput}' tidak valid untuk departemen {$department->name}.";

                        continue;
                    }

                    $existing = Intern::withTrashed()
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                        ->where('department_id', $department->id)
                        ->first();

                    if ($existing) {
                        if ($existing->trashed()) {
                            $existing->restore();
                        }
                        $existing->update([
                            'name' => $name,
                            'sub_division' => $subDivision,
                            'is_active' => true,
                        ]);
                        $updated++;
                    } else {
                        Intern::create([
                            'name' => $name,
                            'department_id' => $department->id,
                            'sub_division' => $subDivision,
                            'is_active' => true,
                        ]);
                        $created++;
                    }
                }

                // Only read the first sheet ('Data Intern')
                break;
            }
        } finally {
            $reader->close();
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    private function makeReader(string $filePath, string $ext): CsvReader|XlsxReader
    {
        if ($ext === 'csv') {
            $firstLine = fgets(fopen($filePath, 'r')) ?: '';
            $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
            $options = new CsvOptions;
            $options->FIELD_DELIMITER = $delimiter;

            return new CsvReader($options);
        }

        return new XlsxReader;
    }

    private function resolveDepartment($departments, string $input): ?Department
    {
        $needle = mb_strtolower(trim($input));

        return $departments->first(function (Department $dept) use ($needle) {
            return mb_strtolower($dept->name) === $needle
                || ($dept->abbreviation && mb_strtolower($dept->abbreviation) === $needle);
        });
    }

    private function resolveSubDivision(string $deptName, string $input): ?string
    {
        $trimmed = trim($input);
        if ($trimmed === '' || $trimmed === '-') {
            return null;
        }

        $needle = mb_strtolower($trimmed);
        $validSubs = Department::SUB_DIVISION_MAPPING[$deptName] ?? [];

        foreach ($validSubs as $sub) {
            if (mb_strtolower($sub) === $needle) {
                return $sub;
            }
        }

        // Handle common spelling variant ("Talent Development" -> "Talent Developement")
        if ($deptName === 'Talent and Sport' && $needle === 'talent development') {
            return 'Talent Developement';
        }

        return null;
    }
}
