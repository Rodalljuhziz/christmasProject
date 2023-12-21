<?php

use PHPUnit\Framework\TestCase;
use mikehaertl\pdftk\Pdf;

final class indexTest extends TestCase
{
    public function testFieldPdf()
    {
        $json= json_decode(file_get_contents('ressources/data.json'), true);
        $pdf = new Pdf('ressources/cerfa_entreprise.pdf');

        $result = $pdf->fillForm($json)
            ->needAppearances()
            ->saveAs('pdfTest/examplePdf.pdf');

        $pdfFilled = new Pdf('ressources/filled.pdf');
        $dataFilled = $pdfFilled->getDataFields();
        $pdfToTest = new Pdf('pdfTest/examplePdf.pdf');
        $dataToTest = $pdfToTest->getDataFields();
        $this->assertEquals($dataToTest, $dataFilled);

    }
}