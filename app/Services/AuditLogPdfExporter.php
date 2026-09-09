<?php

/**
 * AuditLogPdfExporter
 *
 * Converts activity log rows into a branded PDF document using TCPDF.
 * Pure data-in / bytes-out — no DB or session dependencies.
 *
 * @package ProyectoBase
 * @subpackage App\Services
 * @author jandrescodes
 * @version 1.0
 */

namespace App\Services;

use App\Services\Pdf\ReportDocument;

class AuditLogPdfExporter
{
    /** @var int Hard cap on rows exported per report */
    public const MAX_ROWS = 5000;

    /**
     * Renders activity log rows into a PDF byte string.
     *
     * @param array $rows  Rows from ActivityLog::getAll()
     * @param array $meta  Keys: title (string), date_range (string),
     *                     generated_at (string), generated_by (string),
     *                     truncated (bool)
     * @return string      Raw PDF bytes
     */
    public function render(array $rows, array $meta): string
    {
        $pdf = new ReportDocument('L', 'mm', 'LETTER');

        $pdf->reportTitle  = $meta['title']       ?? 'Audit Log Report';
        $pdf->dateRange    = $meta['date_range']   ?? '';
        $pdf->generatedAt  = $meta['generated_at'] ?? '';
        $pdf->generatedBy  = $meta['generated_by'] ?? '';

        $pdf->SetCreator('System');
        $pdf->SetAuthor($pdf->generatedBy);
        $pdf->SetTitle($pdf->reportTitle);

        $pdf->SetDefaultMonospacedFont('dejavusans');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->SetFont('dejavusans', '', 7);

        $pdf->AddPage();

        $this->renderTableHeader($pdf);
        $this->renderRows($pdf, $rows);

        if (($meta['truncated'] ?? false) === true) {
            $pdf->Ln(4);
            $pdf->SetFont('dejavusans', 'B', 8);
            $pdf->SetTextColor(200, 50, 50);
            $pdf->Cell(
                0,
                6,
                '* Report truncated — showing first ' . self::MAX_ROWS . ' of more matching records.'
            );
            $pdf->SetTextColor(0, 0, 0);
        }

        return $pdf->Output('', 'S');
    }

    /**
     * Renders the table header row.
     *
     * @param ReportDocument $pdf
     * @return void
     */
    private function renderTableHeader(ReportDocument $pdf): void
    {
        $pdf->SetFont('dejavusans', 'B', 7);
        $pdf->SetFillColor(230, 230, 230);

        $pdf->Cell(30, 7, 'Date', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'Actor', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Module', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Action', 1, 0, 'C', true);
        $pdf->Cell(70, 7, 'Description', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'IP', 1, 1, 'C', true);

        $pdf->SetFont('dejavusans', '', 7);
    }

    /**
     * Renders data rows into the PDF.
     *
     * @param ReportDocument $pdf
     * @param array          $rows
     * @return void
     */
    private function renderRows(ReportDocument $pdf, array $rows): void
    {
        foreach ($rows as $row) {
            $date    = date('Y-m-d H:i', strtotime($row['created_at'] ?? ''));
            $actor   = $row['actor_label'] ?? '—';
            $module  = $row['module'] ?? '';
            $action  = $row['action'] ?? '';
            $desc    = $row['description'] ?? '';
            $ip      = $row['ip_address'] ?? '—';

            $pdf->Cell(30, 6, $date, 1, 0, 'C');
            $pdf->Cell(40, 6, $this->truncate($actor, 28), 1, 0, 'L');
            $pdf->Cell(25, 6, $this->truncate($module, 16), 1, 0, 'C');
            $pdf->Cell(25, 6, $this->truncate($action, 16), 1, 0, 'C');
            $pdf->Cell(70, 6, $this->truncate($desc, 50), 1, 0, 'L');
            $pdf->Cell(25, 6, $this->truncate($ip, 16), 1, 1, 'C');
        }
    }

    /**
     * Truncates a string to a maximum length, appending an ellipsis if needed.
     *
     * @param string $text
     * @param int    $max
     * @return string
     */
    private function truncate(string $text, int $max): string
    {
        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return mb_substr($text, 0, $max - 1) . '…';
    }
}
