<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Donation Receipt</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
        }

        .box {
            border: 1px solid #ddd;
            padding: 14px;
            border-radius: 8px;
        }

        .row {
            width: 100%;
        }

        .col {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }

        .muted {
            color: #666;
            font-size: 11px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .hr {
            border-top: 1px solid #eee;
            margin: 12px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 6px 0;
        }

        .right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="box">
        <table>
            <tr>
                <td>
                    <p class="title">Donation Receipt</p>
                    <div class="muted">Receipt Code: <b>{{ $receipt->receipt_code }}</b></div>
                </td>
                <td class="right">
                    <div class="muted">Issued at</div>
                    <div><b>{{ $receipt->issued_at?->format('Y-m-d H:i') }}</b></div>
                </td>
            </tr>
        </table>

        <div class="hr"></div>

        <div class="row">
            <div class="col">
                <div class="muted">Donor</div>
                <div><b>{{ $receipt->donor?->name }}</b></div>
                <div class="muted">{{ $receipt->donor?->email }}</div>
            </div>
            <div class="col right">
                <div class="muted">Recipient</div>
                <div><b>{{ $receipt->recipient?->name }}</b></div>
                <div class="muted">{{ $receipt->recipient?->email }}</div>
            </div>
        </div>

        <div class="hr"></div>

        <div class="muted">Glasses</div>
        <div><b>{{ $receipt->glasses?->title ?? '—' }}</b></div>

        <table>
            <tr>
                <td class="muted">Delivered date</td>
                <td class="right"><b>{{ $receipt->delivered_date?->format('Y-m-d') ?? '—' }}</b></td>
            </tr>
            <tr>
                <td class="muted">Approved by</td>
                <td class="right"><b>{{ $receipt->approver?->name ?? '—' }}</b></td>
            </tr>
        </table>

        <div class="hr"></div>

        <div class="muted">Admin note</div>
        <div>{{ $receipt->admin_note ?: '—' }}</div>

        <div class="hr"></div>
        <div class="muted">
            This receipt confirms that the donation was approved by the platform.
        </div>
    </div>
</body>

</html>