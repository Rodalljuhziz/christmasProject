<?php

use PHPUnit\Framework\TestCase;
use mikehaertl\pdftk\Pdf;
use Application\Controller\PdfFiller;

final class indexTest extends TestCase
{
    const PATH = 'C:/Users/walcz/OneDrive/Desktop/christmasProject';
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

    public function testBase64()
    {
        $pdfFiller = new PdfFiller();

        $pdftkArray = json_decode('{
            "a1" : "123456789"
        }', true);
        $pdftk = new Pdf(self::PATH .'/ressources/cerfa_entreprise.pdf');
        $resultPdftk = $pdftk->fillForm($pdftkArray)
            ->needAppearances()
            ->saveAs(self::PATH .'/PdfGenerated/client.pdf');

        $clientJson = json_decode('{
            "type_cerfa": "entreprise",
            "num_ordre" : "123456789"
        }', true);

        $pdfFiller->createPDF($clientJson, self::PATH .'/Test/client.pdf');

        $pdftkBase64 = base64_encode(file_get_contents(self::PATH .'/PdfGenerated/client.pdf'));
        $clientBase64 = base64_encode(file_get_contents(self::PATH .'/Test/client.pdf'));
        $this->assertEquals($pdftkBase64,$clientBase64);
    }
}