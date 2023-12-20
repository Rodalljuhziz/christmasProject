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

    $json= json_decode(file_get_contents('ressources/data.json'), true);
    $pdf = new Pdf('ressources/cerfa_entreprise.pdf');
    var_dump($json);
    $result = $pdf->fillForm($json)
        ->needAppearances()
        ->saveAs('ressources/filled.pdf');

    if ($result === false){
        $error = $pdf->getError();
        var_dump($error);
    }
    //var_dump($result);

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