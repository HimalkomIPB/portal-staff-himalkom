<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SOTM {{ $selectedMonthName }} {{ $selectedYear }}</title>
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

    <h1>Laporan SOTM Bulan {{ $selectedMonthName }} {{ $selectedYear }}</h1>

    @foreach($departmentGroups as $dept)
        <h2>Departemen: {{ $dept['name'] }}</h2>
        
        @foreach($dept['grouped_members'] as $subName => $members)
            <h3>Divisi: {{ $subName }}</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 25%">Nama</th>
                        <th class="text-center" style="width: 15%">Kehadiran (10%)</th>
                        <th class="text-center" style="width: 15%">Keaktifan Komunikasi (30%)</th>
                        <th class="text-center" style="width: 15%">Sikap Disiplin (30%)</th>
                        <th class="text-center" style="width: 15%">Inovasi Inisiatif (30%)</th>
                        <th class="text-center" style="width: 15%">Skor Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        @php
                            $isBest = isset($dept['best_performers'][$subName]) && $dept['best_performers'][$subName] === $member['id'];
                        @endphp
                        <tr {!! $isBest ? 'style="background-color: #fdf6e3;"' : '' !!}>
                            <td>
                                {{ $member['name'] }}
                                @if($isBest)
                                    <span style="color: #d97706; font-weight: bold; font-size: 10px; margin-left: 5px;">(Best Performer)</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $member['scores']['Kehadiran (10%)'] ?? '-' }}</td>
                            <td class="text-center">{{ $member['scores']['Keaktifan Komunikasi (30%)'] ?? '-' }}</td>
                            <td class="text-center">{{ $member['scores']['Sikap Disiplin (30%)'] ?? '-' }}</td>
                            <td class="text-center">{{ $member['scores']['Inovasi Inisiatif (30%)'] ?? '-' }}</td>
                            <td class="text-center font-bold">{{ $member['combined_score'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada anggota yang dinilai</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endforeach
    @endforeach

</body>
</html>
