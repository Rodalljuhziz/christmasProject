<?php
require __DIR__ . '/vendor/autoload.php';

use Application\Controller\App;
use Application\Controller\Router;
use Application\Controller\Request;
use Application\Controller\Response;
use Application\Model\Posts;

use mikehaertl\pdftk\Pdf;

Posts::load();

Router::get('/post', function (Request $request, Response $response) {

    $pdf = new Pdf('ressources/cerfa_entreprise.pdf');
    $result = $pdf->fillForm([
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
        ->saveAs('ressources/filled.pdf');

    $pdfdata = new Pdf('ressources/filled.pdf');
    $data = $pdfdata->getDataFields();
    if ($data === false) {
        $error = $pdfdata->getError();
    }

    /*// Get data as string
    $txt = (string) $data;
    $txt = $data->__toString();
    var_dump($txt);*/


// Get data as string
    echo $data;
    $txt = (string) $data;
    $txt = $data->__toString();
    var_dump($data);


    if ($result === false){
        $error = $pdf->getError();
    }
    var_dump($result);
    var_dump($pdf->getError());
    $response->toJSON(Posts::all());
});

Router::post('/post', function (Request $request, Response $response) {
    $b_post = Posts::add($request->getJSON());
    $response->p_status(201)->toJSON($b_post);
});

Router::get('/post/([0-9]*)', function (Request $request, Response $response) {
    $b_post = Posts::findById($request->paramtrs[0]);
    if ($b_post) {
        $response->toJSON($b_post);
    } else {
        $response->p_status(404)->toJSON(['error' => "Not Found"]);
    }
});

App::run();