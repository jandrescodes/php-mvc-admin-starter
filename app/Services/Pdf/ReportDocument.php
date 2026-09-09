<?php

/**
 * ReportDocument — TCPDF subclass for branded report output.
 *
 * Provides a consistent header (logo + title + date range + timestamp)
 * and footer (page numbers + generator name) across all server-side PDF exports.
 *
 * @package ProyectoBase
 * @subpackage App\Services\Pdf
 * @author jandrescodes
 * @version 1.0
 */

namespace App\Services\Pdf;

class ReportDocument extends \TCPDF
{
    /** @var string Report title displayed in the header */
    public string $reportTitle = '';

    /** @var string Date range subtitle displayed below the title */
    public string $dateRange = '';

    /** @var string Timestamp of when the report was generated */
    public string $generatedAt = '';

    /** @var string Name of the user who generated the report */
    public string $generatedBy = '';

    /**
     * Header: logo + title + date range + generation timestamp.
     *
     * @return void
     */
    public function Header(): void
    {
        $logoPath = dirname(dirname(dirname(__DIR__))) . '/public/img/AdminLTELogo.png';

        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 10, 15, 15, '', '', '', false, 300);
        }

        $this->SetFont('dejavusans', 'B', 14);
        $this->Cell(0, 8, $this->reportTitle, 1);

        $this->SetFont('dejavusans', '', 9);

        if ($this->dateRange !== '') {
            $this->Cell(0, 5, $this->dateRange, 1);
        }

        $this->Cell(0, 5, $this->generatedAt, 1);

        $this->Ln(5);
    }

    /**
     * Footer: page numbers on the left, generator name on the right.
     *
     * @return void
     */
    public function Footer(): void
    {
        $this->setY(-15);
        $this->SetFont('dejavusans', '', 8);

        $this->Cell(0, 10, 'Pagina ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages());

        $this->setX(-60);
        $this->Cell(0, 10, $this->generatedBy);
    }
}
