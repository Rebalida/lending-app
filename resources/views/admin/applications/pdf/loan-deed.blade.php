{{-- DomPDF template — Loan Deed. Thin shell over the shared partials ($mode = 'pdf').
     Inline styles only, no flexbox/grid. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Loan Deed — {{ $application->application_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9.5pt; color: #1a1a2e; line-height: 1.5; margin: 0; }
        .deed-page { padding: 10px 20px; }

        /* Cover */
        .deed-cover-spacer { height: 180px; }
        .deed-cover-title { font-size: 26pt; font-weight: bold; color: #1e2a78; border-bottom: 1.5px solid #6b7280; padding-bottom: 6px; margin: 0 0 30px 0; }
        .deed-cover-party { margin-bottom: 10px; }
        .deed-cover-dash { display: inline-block; width: 30px; vertical-align: top; }
        .deed-cover-party > div { display: inline-block; width: 80%; vertical-align: top; }
        .deed-cover-and { margin: 10px 0 10px 30px; }
        .deed-cover-firm { margin-top: 220px; text-align: right; }
        .deed-firm-wordmark { font-size: 12pt; color: #1e2a78; margin: 0; }
        .deed-firm-light { color: #9ca3af; font-weight: normal; }
        .deed-firm-address { font-size: 8pt; color: #4b5563; margin-top: 6px; }

        /* TOC */
        .deed-toc-heading { font-size: 15pt; color: #1e2a78; margin: 0 0 12px 0; }
        .deed-toc-table { width: 100%; border-collapse: collapse; }
        .deed-toc-table td { padding: 1.5px 0; vertical-align: bottom; font-size: 8.5pt; }
        .deed-toc-num { width: 40px; }
        .deed-toc-main td { font-weight: bold; color: #1e2a78; padding-top: 6px; }
        .deed-toc-sub .deed-toc-num { padding-left: 18px; }
        .deed-toc-leader { display: inline-block; width: 100%; border-bottom: 1px dotted #9ca3af; }
        .deed-toc-page { width: 30px; text-align: right; }

        /* Headings and text */
        .deed-h1 { font-size: 20pt; font-weight: bold; color: #1e2a78; margin: 0 0 16px 0; }
        .deed-h2 { font-size: 14pt; font-weight: bold; color: #1e2a78; margin: 18px 0 8px 0; }
        .deed-h3 { font-size: 11pt; font-weight: bold; color: #1e2a78; border-bottom: 0.75px solid #6b7280; padding-bottom: 3px; margin: 18px 0 8px 0; }
        .deed-h4 { font-size: 9.5pt; font-weight: bold; margin: 12px 0 4px 0; }
        .deed-p { margin: 0 0 6px 0; text-align: justify; }
        .deed-muted { color: #6b7280; font-size: 8pt; }

        /* Lists */
        .deed-ol { margin: 4px 0 8px 26px; padding: 0; }
        .deed-ol li { margin-bottom: 4px; text-align: justify; }

        /* Financial / fee tables */
        .deed-fin-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .deed-fin-table td { border: 0.75px solid #374151; padding: 4px 6px; vertical-align: top; font-size: 8.5pt; }
        .deed-fin-table td:first-child { width: 34%; }
        .deed-fin-table td:last-child { width: 16%; }
        .deed-fin-header td { font-weight: bold; }

        /* Recitals / lettered clause tables */
        .deed-clause-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .deed-clause-table td { padding: 2px 0; vertical-align: top; text-align: justify; }
        .deed-clause-num { width: 28px; }

        /* Schedule */
        .deed-schedule-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .deed-schedule-table td { padding: 6px 8px; vertical-align: top; }
        .deed-sched-label { width: 27%; background-color: #bfd0e4; font-weight: bold; }
        .deed-schedule-table tr td { background-color: #eceff4; }
        .deed-schedule-table tr td.deed-sched-label { background-color: #bfd0e4; }

        /* Execution */
        .deed-exec-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .deed-exec-table td { vertical-align: bottom; padding: 3px 0; }
        .deed-exec-left { width: 55%; padding-right: 24px; vertical-align: top; }
        .deed-exec-right { width: 45%; }
        .deed-sig-line { border-bottom: 0.75px solid #1a1a2e; height: 36px; margin-bottom: 3px; width: 90%; }
        .deed-sig-underline { border-bottom: 0.75px solid #1a1a2e; min-height: 16px; margin-bottom: 3px; width: 90%; }
        .deed-sig-img { max-height: 55px; max-width: 90%; border-bottom: 0.75px solid #1a1a2e; margin-bottom: 3px; }
        .deed-dated { margin: 18px 0; }
        .deed-dated-line { display: inline-block; border-bottom: 0.75px solid #1a1a2e; width: 280px; }

        /* Fixed footer on every page */
        .pdf-footer {
            position: fixed;
            bottom: 8px;
            left: 20px;
            right: 20px;
            font-size: 7pt;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="pdf-footer">
        Loan Deed — {{ $application->application_number }} &nbsp;|&nbsp; Generated {{ $generatedAt->format('d M Y g:i A') }}
    </div>

    @include('shared.loan-deed.document', ['application' => $application, 'd' => $deedData, 'mode' => 'pdf'])
</body>
</html>
