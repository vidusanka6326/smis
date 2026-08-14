<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 16px 0 8px; }
        p.meta { margin: 0 0 16px; color: #555; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f3f4f6; font-weight: bold; }
        .empty { color: #777; font-style: italic; }
        .footer { margin-top: 24px; font-size: 9px; color: #777; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="meta">
        {{ $school }}
        @if (filled($subtitle))
            — {{ $subtitle }}
        @endif
        — {{ __('Generated :when', ['when' => $generatedAt->timezone(config('app.timezone'))->format('Y-m-d H:i')]) }}
    </p>

    @foreach ($tables as $table)
        <h2>{{ $table['title'] }}</h2>
        <table>
            <thead>
                <tr>
                    @foreach ($table['headers'] as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($table['rows'] as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td class="empty" colspan="{{ max(count($table['headers']), 1) }}">{{ __('No data.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

    <p class="footer">{{ __('Confidential school record — SMIS') }}</p>
</body>
</html>
