<?php
require __DIR__ . '/vendor/autoload.php';

use Application\Controller\App;
use Application\Controller\Router;
use Application\Controller\Request;
use Application\Controller\Response;
use Application\Model\Posts;
use Application\Controller\PdfFiller;

Router::get('/post', function (Request $request, Response $response) {

    $pdf = new PdfFiller();
    $pdf->createPdf();
    $pdf->testData();
   // $response->toJSON(Posts::all());
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