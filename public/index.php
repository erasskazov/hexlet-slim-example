<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});


$app = AppFactory::createFromContainer($container);

$app->get('/users', function ($request, $response) {
    $directory = scandir("users");
    $usersFiles = array_diff($directory, array('..', '.'));
    $users = array_map(fn ($userFile) => json_decode(file_get_contents("users/" . $userFile), true), $usersFiles);
    $term = $request->getQueryParam('term');
    $filteredUsers = array_filter($users, fn ($user) => str_contains($user['nickname'], $term));
    $params = ['users' => $filteredUsers, 'term' => $term];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
});

$app->post('/users', function ($request, $response) {
    $user = $request->getParsedBodyParam('user');
    file_put_contents("users/" . $user['nickname'] . ".json", json_encode($user));
    return $response->withRedirect('/users');
});

$app->get('/users/new', function ($request, $response) {
    $params = ['user' => ['nickname' => '', 'email' => '']];
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
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

