<?php

use PHPUnit\Framework\TestCase;
use mikehaertl\pdftk\Pdf;

final class indexTest extends TestCase
{
    public function test()
    {
        $pdfTest = new Pdf('ressources/cerfa_entreprise.pdf');
        $result = $pdfTest->fillForm([
            'a1' => '11580*03',
            'a2' => 'Croix rouge bordeaux',
            'a3' => 'Moët et jambon',
            'a4' =>  12356894100056,
            'a5' => '7bis',
            'a6' => 'Victor Nigo',
            'a7' => 28120,
            'a8' => 'Saint judas sur franchise',
            'a9' => 'France',
            'a11' => 'Protection des alcooliques en tant de guerre',
            'a12' => '13/11/2023',
            'CAC1' => true,
        ])
            ->needAppearances()
            ->saveAs('pdfTest/examplePdf.pdf');

        $pdfFilled = new Pdf('ressources/filled.pdf');
        $dataFilled = $pdfFilled->getDataFields();
        $pdfToTest = new Pdf('pdfTest/examplePdf.pdf');
        $dataToTest = $pdfToTest->getDataFields();
        $this->assertEquals($dataToTest, $dataFilled);

    }
}