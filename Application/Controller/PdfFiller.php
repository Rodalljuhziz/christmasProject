<?php

namespace Application\Controller;

use DateTime;
use mikehaertl\pdftk\Pdf;
class PdfFiller
{
    public function validate($model, $client)
    {
        $FILLED = [];
        foreach($model as $field => $rules)
        {
            $value = @$client[$field];
            if ($rules['mandatory'] === true && !$value) return "missing field '$field'";
            if (is_array($rules['mandatory'])) {
                foreach ($rules['mandatory'] as $subfield => $subvalues) {
                    foreach ($subvalues as $subvalue) {
                        if (!isset($value) && $subvalue === $client[$subfield]) return "missing field '$field'";
                    }
                }
            }
            if (isset($value)) {
                if ($rules['type'] === 'date' && !$this->isValidDate($value)) {
                    return "incompatible date format for field '$field'";
                }
                if ($rules['type'] !== 'date' && gettype($value) !== $rules['type']) {
                    return "incompatible type for field '$field'";
                }
                if (isset($rules['dependency'])) {
                    $dependency = $rules['dependency']['field'];
                    foreach ($rules['dependency']['values'][$client[$dependency]] as $subfield => $subvalue) {
                        if ($rules['type'] === 'date') {
                            $FILLED[$subfield] = DateTime::createFromFormat('Y-m-d', $value)->format($subvalue);
                        } else {
                            $FILLED[$subfield] = $subvalue;
                        }
                    }
                } else {
                    $FILLED[$rules['field']] = $value;
                }
            }
        }
        return $FILLED;
    }
    public static function createPdf()
    {
        $json= json_decode(file_get_contents(__DIR__.'/../Json/data.json'), true);
        $pdf = new Pdf('ressources/cerfa_entreprise.pdf');
        var_dump(__DIR__);
        $result = $pdf->fillForm($json)
            ->needAppearances()
            ->saveAs('ressources/filled.pdf');

        $pdf2 = new Pdf('ressources/cerfa_entreprise.pdf');
        $data = $pdf2->getDataFields();
        $arr = (array) $data;
        $arr = $data->__toArray();

        print("<pre>".print_r($arr,true)."</pre>");

        if ($result === false){
            $error = $pdf->getError();
            var_dump($error);
        }
    }

    public function testData()
    {
        $model = json_decode('{
  "asso_name":{
    "type": "string",
    "mandatory": true,
    "field": "a2"
  },
  "asso_siren":{
    "type": "string",
    "mandatory": true,
    "field": "a4"
  },
  "asso_streetNumber":{
  "type": "string",
  "mandatory": false,
  "field": "a5"
},
  "asso_street":{
    "type": "string",
    "mandatory": true,
    "field": "a6"
  },
  "asso_city":{
    "type": "string",
    "mandatory": false,
    "field": "a6"
  },
  "asso_postcode":{
    "type": "string",
    "mandatory": false,
    "field": "a8"
  },
  "asso_country":{
    "type": "string",
    "mandatory": false,
    "field": "a9"
  },
  "asso_type": {
    "type": "string",
    "mandatory": true,
    "dependency": {
      "field": "type",
      "values": {
        "LOI1901": {
          "CAC0": 1,
          "CAC1": 1
        },
        "FRUP": {
          "CAC0": 2,
          "CAC1": 1
        },
        "FRUP_MOZEL": {
          "CAC0": 2,
          "CAC1": 1
        },
        "FUNIV": {
          "CAC0": 3,
          "CAC1": 1
        },
        "FENT": {
          "CAC0": 4,
          "CAC1": 1
        },
        "MDF": {
          "CAC0": 5,
          "CAC1": 1
        },
        "OSBL": {
          "CAC0": 6,
          "CAC1": 1
        },
        "AUTRES": {
          "CAC0": 7,
          "CAC1": 1
        },
        "ASS_CULT": {
          "CAC2": 1
        },
        "ENSSUP": {
          "CAC3": 1
        },
        "ENSSUPCONSULAIRE": {
          "CAC4": 1
        },
        "SOPP": {
          "CAC5": 1
        },
        "ENSSUPCINE": {
          "CAC6": 1
        },
        "ASS_OPP": {
          "CAC7": 1
        },
        "MECENE": {
          "CAC8": 1
        },
        "ETATACT": {
          "CAC9": 1
        },
        "SNPMCOM": {
          "CAC10": 1
        },
        "SNPMMUSIC": {
          "CAC11": 1
        },
        "SNPMCOM": {
          "CAC12": 1
        },
        "ONDPAT": {
          "CAC13": 1
        },
        "FONDDOTATION": {
          "CAC14": 1
        },
        "FINANCEENT": {
          "CAC15": 1
        },
        "FEDERER": {
          "CAC16": 1
        },
        "SAUVEGARDE": {
          "CAC17": 1
        },
        "UE": {
          "CAC18": 1
        }
      }
    }
  },
  "type": "date",
  "mandatory": {
    "type": ["FRUP", "FRUP_MOZEL", "FUNIV", "SOPP", "FONDPAT","FINANCEENT","FEDERER"]
  },
  "dependency": {
    "field": "type",
    "values": {
      "FRUP": {
        "d12": "d/m/y",
        "d13": "d/m/y"
      },
      "FRUP_MOZEL": {
        "d14": "d/m/y"
      },
      "FUNIV": {
        "d14": "d/m/y"
      },
      "SOPP": {
        "a14": "d/m/y"
      },
      "FONDPAT": {
        "a15": "d/m/y"
      },
      "FINANCEENT": {
        "a15": "d/m/y"
      },
      "FEDERER": {
        "a15": "d/m/y"
      }
    }
  }
}', true);

                $client = json_decode('{
            "asso_siren": "SPA",
            "asso_name": "LA SPA",
            "asso_street": "Paris",
            "asso_type": "LOI1901",
            "date": "2023-01-01"
        }', true);
                $client2 = json_decode('{
            "asso_siren": "SPA",
            "asso_name": "LA SPA",
            "asso_street": "Paris",
            "asso_type": "FRUP",
            "date": "2023-01-01"
        }', true);
                echo json_encode( $this->validate($model, $client) ) ." >> Done". PHP_EOL;
                echo json_encode( $this->validate($model, $client2) ) ." >> Done". PHP_EOL;
                $client = json_decode('{
            "asso_siren": "SPA",
            "asso_name": "LA SPA",
            "asso_street": "Paris",
            "asso_type": "FRUP",
            "date": "2023-01-01"
        }', true);
                $client2 = json_decode('{
            "asso_siren": "SPA",
            "asso_name": "LA SPA",
            "asso_street": "Paris",
            "asso_type": "FRUP",
            "date": "2023-01-01"
        }', true);
                $client3 = json_decode('{
            "asso_siren": "SPA",
            "asso_name": "LA SPA",
            "asso_street": "Paris",
            "asso_type": "LOI1901",
            "date": "2023-01-01"
        }', true);


        echo json_encode( $this->validate($model, $client) ) ." >> Done". PHP_EOL;
        echo json_encode( $this->validate($model, $client2) ) ." >> Done". PHP_EOL;
        echo json_encode( $this->validate($model, $client3) ) ." >> Done". PHP_EOL;
    }
    public function isValidDate($date, $format = 'Y-m-d'): bool
    {
        $dateTime = DateTime::createFromFormat($format, $date);
        return $dateTime && $dateTime->format($format) === $date;
    }
}