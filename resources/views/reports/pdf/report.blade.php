<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
            background: white;
        }

        .header {
            padding: 28px 32px 18px;
            border-bottom: 3px solid #10b981;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .header-left .org {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 4px;
        }

        .header-left h1 {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }

        .header-right {
            text-align: right;
            font-size: 10px;
            color: #6b7280;
            line-height: 1.7;
        }

        .filters {
            padding: 8px 32px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
        }

        .content {
            padding: 0 32px 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        thead th {
            background: #f3f4f6;
            font-weight: 600;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #374151;
            padding: 9px 10px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
        }

        tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 10.5px;
            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:nth-child(even) td {
            background: #fafafa;
        }

        .footer {
            margin-top: 20px;
            padding: 10px 32px 0;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #9ca3af;
            display: flex;
            justify-content: space-between;
        }

        .empty {
            padding: 48px 32px;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <div class="org">Procurement System</div>
            <h1>{{ $title }}</h1>
        </div>
        <div class="header-right">
            Generated: {{ $generatedAt }}<br>
            @if($dateFrom || $dateTo)
                Period: {{ $dateFrom ?? '—' }} → {{ $dateTo ?? '—' }}<br>
            @endif
            @if($fiscalYear)
                Fiscal Year: {{ $fiscalYear }}
            @endif
        </div>
    </div>

    {{-- Active filter summary --}}
    @if($dateFrom || $dateTo || $fiscalYear)
    <div class="filters">
        Filters applied:
        @if($dateFrom || $dateTo) &nbsp;Date: {{ $dateFrom ?? 'start' }} → {{ $dateTo ?? 'end' }} @endif
        @if($fiscalYear) &nbsp;| Fiscal Year: {{ $fiscalYear }} @endif
    </div>
    @endif

    <div class="content">
        @if($rowCount > 0)
            <table>
                <thead>
                    <tr>
                        @foreach($headers as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            @foreach($row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="footer">
                <span>{{ $rowCount }} record(s)</span>
                <span>Confidential — {{ config('app.name') }}</span>
            </div>
        @else
            <div class="empty">No records found for the selected filters.</div>
        @endif
    </div>

</body>
</html>
