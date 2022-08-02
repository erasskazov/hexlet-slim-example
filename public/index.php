<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];

$app = AppFactory::createFromContainer($container);

$app->get('/users', function ($request, $response) use ($users) {
    $term = $request->getQueryParam('term');
    $filteredUsers = array_filter($users, fn ($user) => str_contains($user, $term));
    $params = ['users' => $filteredUsers, 'term' => $term];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
});

$app->post('/users', function ($request, $response) {
    return $response->write('a eto POST /users');
});


$app->get('/users/{id}', function ($request, $response, array $args) {
    $params = ['id' => htmlspecialchars($args['id']), 'nickname' => 'user-' . ($args['id'])];
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

$app->get('/courses/{id}', function ($request, $response, array $args) {
    $id = $args['id'];
    return $response->write("Course id: {$id}");
});


$app->run();

