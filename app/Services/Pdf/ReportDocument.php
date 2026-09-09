<?php

/**
 * ReportDocument — TCPDF subclass for branded report output.
 *
 * Provides a consistent header (title + date range + timestamp)
 * and footer (page numbers + generator name) across all server-side PDF exports.
 *
 * The header artwork is drawn inside the top-margin band and the cursor is
 * always left at $tMargin, so body content starts at the same Y on every page.
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
     * Header drawn within the top margin band (top margin must be ~30mm).
     * Runs on every page via AddPage().
     *
     * @return void
     */
    public function Header(): void
    {
        $pageWidth = $this->getPageWidth();

        $this->SetFont('dejavusans', 'B', 13);
        $this->SetXY(10, 8);
        $this->Cell(0, 7, $this->reportTitle, 0, 1, 'C');

        $this->SetFont('dejavusans', '', 9);
        $nextY = 17;

        if ($this->dateRange !== '') {
            $this->SetXY(10, $nextY);
            $this->Cell(0, 5, $this->dateRange, 0, 1, 'C');
            $nextY += 5;
        }

        $this->SetXY(10, $nextY);
        $this->Cell(0, 5, $this->generatedAt, 0, 1, 'C');

        $this->setDrawColor(180, 180, 180);
        $this->Line(10, 27.5, $pageWidth - 10, 27.5);

        $this->setY($this->tMargin);
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

        $this->setX(-70);
        $this->Cell(0, 10, $this->generatedBy);
    }
}
