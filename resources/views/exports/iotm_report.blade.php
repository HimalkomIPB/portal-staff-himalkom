<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>IOTM {{ $selectedMonthName }} {{ $selectedYear }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 30px;
            text-transform: uppercase;
        }
        h2 {
            font-size: 16px;
            color: #0b5bd3;
            border-bottom: 2px solid #0b5bd3;
            padding-bottom: 5px;
            margin-top: 30px;
            margin-bottom: 10px;
        }
        h3 {
            font-size: 14px;
            margin-top: 15px;
            margin-bottom: 10px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8fafc;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h1>Laporan Intern of the Month (IOTM) Bulan {{ $selectedMonthName }} {{ $selectedYear }}</h1>

    @foreach($departmentGroups as $dept)
        @if($dept['interns_count'] > 0)
            <h2>Departemen: {{ $dept['name'] }}</h2>

            @foreach($dept['grouped_interns'] as $subName => $interns)
                <h3>Divisi: {{ $subName }}</h3>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 18%">Nama Intern</th>
                            <th class="text-center" style="width: 10%">Ketepatan Waktu</th>
                            <th class="text-center" style="width: 10%">Inisiatif & Proaktif</th>
                            <th class="text-center" style="width: 10%">Kehadiran & Kontribusi</th>
                            <th class="text-center" style="width: 10%">Komunikasi & Kerja Sama</th>
                            <th class="text-center" style="width: 10%">Profesionalisme</th>
                            <th class="text-center" style="width: 6%">Penilai</th>
                            <th class="text-center" style="width: 8%">Rata-rata</th>
                            <th style="width: 18%">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($interns as $intern)
                            @php
                                $isBest = isset($dept['best_interns'][$subName]) && $dept['best_interns'][$subName] === $intern['id'];
                            @endphp
                            <tr {!! $isBest ? 'style="background-color: #fdf6e3;"' : '' !!}>
                                <td>
                                    {{ $intern['name'] }}
                                    @if($isBest)
                                        <span style="color: #d97706; font-weight: bold; font-size: 10px; margin-left: 5px;">(IOTM)</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $intern['avg_scores'] ? number_format($intern['avg_scores']['Ketepatan Waktu Kerja'], 2) : '-' }}</td>
                                <td class="text-center">{{ $intern['avg_scores'] ? number_format($intern['avg_scores']['Inisiatif dan Proaktif'], 2) : '-' }}</td>
                                <td class="text-center">{{ $intern['avg_scores'] ? number_format($intern['avg_scores']['Kehadiran dan Kontribusi Nyata'], 2) : '-' }}</td>
                                <td class="text-center">{{ $intern['avg_scores'] ? number_format($intern['avg_scores']['Komunikasi dan Kerja Sama'], 2) : '-' }}</td>
                                <td class="text-center">{{ $intern['avg_scores'] ? number_format($intern['avg_scores']['Profesionalisme'], 2) : '-' }}</td>
                                <td class="text-center">{{ $intern['evaluator_count'] ?? 0 }}</td>
                                <td class="text-center font-bold">{{ $intern['avg_final_score'] !== null ? number_format($intern['avg_final_score'], 2) : '-' }}</td>
                                <td style="font-size: 10px;">
                                    @if(!empty($intern['notes_list']))
                                        @foreach($intern['notes_list'] as $note)
                                            <div style="margin-bottom: 3px;"><strong>{{ $note['evaluator_name'] }}:</strong> {{ $note['notes'] }}</div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Belum ada intern yang dinilai</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endforeach
        @endif
    @endforeach

</body>
</html>
