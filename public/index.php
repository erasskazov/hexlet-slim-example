<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use Validator\Validator;
use function Users\addUser;
use function Users\getUser;
use function Users\loadUsers;

const USERS_PATH = 'users/users.json';

session_start();

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

$app = AppFactory::createFromContainer($container);
$router = $app->getRouteCollector()->getRouteParser();

$app->get('/users', function ($request, $response) use ($router) {
    $users = loadUsers(USERS_PATH)['users'];
    $term = $request->getQueryParam('term');
    $filteredUsers = array_filter($users, fn ($user) => str_contains($user['nickname'], $term));
    $messages = $this->get('flash')->getMessages();
    $params = [
        'users' => $filteredUsers,
        'term' => $term,
        'urls' => ['users' => $router->urlFor('users')],
        'flash' => $messages
    ];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
})->setName('users');


$app->post('/users', function ($request, $response) use ($router) {
    $validator = new Validator();
    $user = $request->getParsedBodyParam('user');
    $errors = $validator->validate($user);
    if (count($errors) === 0) {
        addUser($user, USERS_PATH);
        $this->get('flash')->addMessage('success', 'Пользователь был успешно добавлен');
        return $response->withRedirect($router->urlFor('users'), 302);
    }
    
    $params = [
        'user' => $user,
        'urls' => ['newUser' => $router->urlFor('newUser'), 'allUsers' => $router->urlFor('users')],
        'errors' => $errors
    ];
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});

$app->get('/users/new', function ($request, $response) use ($router) {
    $params = [
        'user' => ['nickname' => '', 'email' => ''],
        'urls' => ['newUser' => $router->urlFor('newUser'), 'allUsers' => $router->urlFor('users')],
        'errors' => []
    ];
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
})->setName('newUser');


$app->get('/users/{id}', function ($request, $response, array $args) use ($router) {
    $user = getUser($args['id'], USERS_PATH);
    if ($user === null) {
        return $response->withStatus(404)->withRedirect($router->urlFor('users'));
    }
    $params = [
        'user' => $user,
        'urls' => ['newUser' => $router->urlFor('newUser'), 'allUsers' => $router->urlFor('users')]
    ];
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
})->setName('users');


$app->get('/foo', function ($request, $response) {
    $this->get('flash')->addMessage('success', 'This is message');
    return $response->withRedirect('/bar');
});

$app->get('/bar', function ($request, $response) {
    $messages = $this->get('flash')->getMessages();
    print_r($messages);
    $params = ['flash' => $messages];
    return $this->get('renderer')->render($response, 'bar.phtml', $params);
});

$app->run();


