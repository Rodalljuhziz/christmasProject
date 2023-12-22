<?php

namespace Application\Controller;

use DateTime;
use Exception;
use mikehaertl\pdftk\Pdf;

class PdfFiller
{
    public static $TYPE_INDIVIDUAL = "particulier";
    public static $TYPE_SOCIETY = "entreprise";
    public static $DATE_FORMAT = 'Y-m-d';
    const PATH = 'C:/Users/walcz/OneDrive/Desktop/christmasProject';



    public static function allTypes(): array
    {
        return [
            self::$TYPE_INDIVIDUAL =>  'ressources/cerfa_particulier.pdf',
            self::$TYPE_SOCIETY => 'ressources/cerfa_entreprise.pdf',
        ];
    }
    public function validate($client): array
    {
        $result = [];
        $model = json_decode(file_get_contents(__DIR__.'/../Json/model.json'), true);

        if (!in_array($client['type_cerfa'], array_keys(self::allTypes()))) {
            throw new \Exception("incompatible type for field 'type'");
        }

        foreach($model as $field => $rules)
        {
            $value = $client[$field];

            $mandatory = isset($rules['mandatory']) && $rules['mandatory'] === true && !$value;
            $dependency = isset($rules['dependency']) && (in_array($client[$rules['dependency']['field']], array_keys($rules['dependency']['values'])) && !$value);
            if ($mandatory || $dependency) {
                throw new \Exception("missing field '$field'");
            }
            if (isset($value)) {
                if ($rules['type'] === 'date' && !$this->isValidDate($value)) {
                    throw new \Exception("incompatible date format for field '$field'");
                }
                if ($rules['type'] !== 'date' && gettype($value) !== $rules['type']) {
                    throw new \Exception("incompatible type for field '$field'");
                }

                if (isset($rules['dependency'])) {
                    $dependency = $rules['dependency']['field'];
                    if (isset($rules['dependency']['values'][$client[$dependency]]))
                        foreach ($rules['dependency']['values'][$client[$dependency]] as $subfield => $subvalue) {
                            if ($rules['type'] === 'date') {
                                $result[$subfield] = DateTime::createFromFormat(self::$DATE_FORMAT, $value)->format($subvalue);
                            } else {
                                $result[$subfield] = $subvalue;
                            }
                        }
                } else {
                    $result[$rules['field']] = $value;
                }
            }
        }
        return $result;
    }
    public function createPdf($jsonArray, $pathCreation)
    {

        $validation = $this->validate($jsonArray);
        $pdf = new Pdf(self::PATH .'/ressources/cerfa_entreprise.pdf');
        $result = $pdf->fillForm($validation)
            ->needAppearances()
            ->saveAs($pathCreation);

        /* $pdf2 = new Pdf('ressources/cerfa_entreprise.pdf');
        $data = $pdf2->getDataFields();
        $arr = (array) $data;
        $arr = $data->__toArray();

        print("<pre>".print_r($arr,true)."</pre>");*/

        if ($result === false){
            $error = $pdf->getError();
            var_dump($error);
        }
    }

    public function testData()
    {
        $client = json_decode('{
            "type_cerfa" : "entreprise",
            "asso_siren": "SPA",
            "asso_name": "LA SPA",
            "asso_street": "Paris",
            "asso_type": "LOI1901",
            "date": "2023-01-01"
        }', true);
                $client2 = json_decode('{
            "type_cerfa" : "entreprise",
            "asso_siren": "SPA",
            "asso_name": "LA SPA",
            "asso_street": "Paris",
            "asso_type": "FRUP",
            "date": "2023-01-01"
        }', true);
                echo json_encode( $this->validate($client) ) ." >> Done". PHP_EOL;
                echo json_encode( $this->validate($client2) ) ." >> Done". PHP_EOL;
                $client = json_decode('{
            "type_cerfa" : "entreprise",
            "asso_siren": "SPA",
            "asso_name": "LA SPA",
            "asso_street": "Paris",
            "asso_type": "FRUP",
            "date": "2023-01-01"
        }', true);
                $client2 = json_decode('{
            "type_cerfa" : "entreprise",
            "asso_siren": "SPA",
            "asso_name": "LA SPA",
            "asso_street": "Paris",
            "asso_type": "FRUP",
            "date": "2023-01-01"
        }', true);
                $client3 = json_decode('{
            "type_cerfa" : "entreprise",
            "asso_siren": "SPA",
            "asso_name": "LA SPA",
            "asso_street": "Paris",
            "asso_type": "LOI1901",
            "date": "2023-01-01"
        }', true);


        echo json_encode( $this->validate($client) ) ." >> Done". PHP_EOL;
        echo json_encode( $this->validate($client2) ) ." >> Done". PHP_EOL;
        echo json_encode( $this->validate($client3) ) ." >> Done". PHP_EOL;
    }
    private function createSignature(string $id, string $base64Signature): string
    {
        $path = "Signatures/signature_{$id}";
        $png = $path .'.png';
        $pdf = $path .'.pdf';
        file_put_contents($png, $base64Signature);
        $tcpdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $tcpdf->AddPage(); // page blank
        $tcpdf->AddPage();
        $tcpdf->Image($png,134,216,50,20,'PNG'); // @TODO How to keep ratio ?
        $tcpdf->Output('/home/runcloud/webapps/cerfa-generator/'. $pdf, 'F');
        return $pdf;
    }
    public function generatePDF()
    {
        $id = uniqid();
        $signature = $this->createSignature($id, base64_decode($this->values()['signature']));
        $filename = "CerfaReceipt{$id}.pdf";
        $template = new Pdf('ressources/cerfa_entreprise.pdf');
        $result = $template->fillForm($this->values());
        $template = new Pdf($template);
        $result = $template->flatten() // to compress
        ->multistamp($signature) // to add signature
        ->saveAs('PdfGenerated/' . $filename);

        $this->file = $filename;
    }
    public function isValidDate($date, $format = 'Y-m-d'): bool
    {
        $dateTime = DateTime::createFromFormat($format, $date);
        return $dateTime && $dateTime->format($format) === $date;
    }
}