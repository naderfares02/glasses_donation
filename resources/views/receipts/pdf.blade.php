<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Donation Receipt {{ $receipt->receipt_code }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
        }

        .box {
            border: 1px solid #ddd;
            padding: 16px;
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
            font-size: 20px;
            font-weight: bold;
            margin: 0;
        }

        .subtitle {
            font-size: 11px;
            color: #666;
            margin: 2px 0 0 0;
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

        /* Reference numbers block */
        .ref-box {
            background: #f7f7f8;
            border: 1px solid #e5e5e6;
            border-radius: 6px;
            padding: 10px 14px;
            margin: 14px 0;
        }

        .ref-box table td {
            padding: 3px 0;
        }

        .ref-label {
            color: #666;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ref-value {
            font-size: 13px;
            font-weight: bold;
            font-family: DejaVu Sans Mono, monospace;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 10px;
            font-size: 10.5px;
            font-weight: bold;
            background: #e6f4ea;
            color: #1e7e34;
        }

        .section-label {
            color: #666;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .details-table td {
            font-size: 11.5px;
            padding: 4px 0;
            border-bottom: 1px dashed #eee;
        }

        .footer {
            margin-top: 16px;
            font-size: 10px;
            color: #888;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="box">
        {{-- Header --}}
        <table>
            <tr>
                <td>
                    <p class="title">{{ config('app.name', 'Glasses Donation Platform') }}</p>
                    <p class="subtitle">Official Donation Receipt</p>
                </td>
                <td class="right">
                    <span class="status-badge">APPROVED</span>
                    <div class="muted" style="margin-top:6px;">Issued at</div>
                    <div><b>{{ $receipt->issued_at?->format('Y-m-d H:i') ?? '—' }}</b></div>
                </td>
            </tr>
        </table>

        {{-- Reference numbers --}}
        <div class="ref-box">
            <table>
                <tr>
                    <td style="width:50%;">
                        <div class="ref-label">Receipt Code</div>
                        <div class="ref-value">{{ $receipt->receipt_code }}</div>
                    </td>
                    <td style="width:50%;">
                        <div class="ref-label">Glasses Reference No.</div>
                        <div class="ref-value">{{ $receipt->glasses?->serial_number ?? '—' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="hr"></div>

        {{-- Donor / Recipient --}}
        <div class="row">
            <div class="col">
                <div class="section-label">Donor</div>
                <div><b>{{ $receipt->donor?->name ?? '—' }}</b></div>
                <div class="muted">{{ $receipt->donor?->email ?? '—' }}</div>
            </div>
            <div class="col right">
                <div class="section-label">Recipient</div>
                <div><b>{{ $receipt->recipient?->name ?? '—' }}</b></div>
                <div class="muted">{{ $receipt->recipient?->email ?? '—' }}</div>
            </div>
        </div>

        <div class="hr"></div>

        {{-- Glasses details --}}
        <div class="section-label">Glasses Details</div>
        <table class="details-table">
            <tr>
                <td class="muted">Title</td>
                <td class="right"><b>{{ $receipt->glasses?->title ?? '—' }}</b></td>
            </tr>
            <tr>
                <td class="muted">Brand</td>
                <td class="right"><b>{{ $receipt->glasses?->brand ?? '—' }}</b></td>
            </tr>
            <tr>
                <td class="muted">Condition</td>
                <td class="right">
                    <b>{{ $receipt->glasses?->condition ? ucfirst($receipt->glasses->condition) : '—' }}</b></td>
            </tr>
            <tr>
                <td class="muted">Lens type</td>
                <td class="right">
                    <b>{{ $receipt->glasses?->lens_type ? ucfirst(str_replace('_', ' ', $receipt->glasses->lens_type)) : '—' }}</b>
                </td>
            </tr>
        </table>

        <div class="hr"></div>

        {{-- Delivery / approval --}}
        <div class="section-label">Delivery &amp; Approval</div>
        <table class="details-table">
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

        <div class="section-label">Admin Note</div>
        <div style="font-size:11.5px;">{{ $receipt->admin_note ?: '—' }}</div>

        <div class="hr"></div>
        <div class="muted">
            This receipt certifies that the glasses referenced above (Ref. No.
            {{ $receipt->glasses?->serial_number ?? '—' }})
            were donated through {{ config('app.name', 'the platform') }} and the donation was reviewed and approved
            by an administrator on {{ $receipt->issued_at?->format('Y-m-d') ?? '—' }}. This document is a proof of
            donation record only and does not constitute a tax-deduction certificate.
        </div>

        <div class="footer">
            Generated automatically &middot; Receipt {{ $receipt->receipt_code }} &middot; Glasses Ref.
            {{ $receipt->glasses?->serial_number ?? '—' }}
        </div>
    </div>
</body>

</html>