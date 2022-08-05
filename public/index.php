<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Middleware\MethodOverrideMiddleware;

use function Users\addUser;
use function Users\getUser;
use function Users\loadUsers;
use function Users\updateUser;
use function Users\userExists;

session_start();

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});


$app = AppFactory::createFromContainer($container);
$app->add(MethodOverrideMiddleware::class);
$app->addErrorMiddleware(true, true, true);

$router = $app->getRouteCollector()->getRouteParser();

$app->get('/users', function ($request, $response) use ($router) {
    $users = loadUsers();
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
    $validator = new \Validator();
    $user = $request->getParsedBodyParam('user');
    $errors = $validator->validate($user);
    if (count($errors) === 0) {
        addUser($user);
        $this->get('flash')->addMessage('success', 'Пользователь был успешно добавлен');
        return $response->withRedirect($router->urlFor('users'), 302);
    }
    
    $params = [
        'user' => $user,
        'urls' => ['newUser' => $router->urlFor('newUser'), 'allUsers' => $router->urlFor('users')],
        'errors' => $errors
    ];
    return $this->get('renderer')->render($response->withStatus(422), 'users/new.phtml', $params);
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
    $user = getUser($args['id']);
    if ($user === null) {
        return $response->withStatus(404)->write('Page Not Found');
    }
    $params = [
        'user' => $user,
        'urls' => ['newUser' => $router->urlFor('newUser'), 'allUsers' => $router->urlFor('users')]
    ];
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
})->setName('users');


$app->get('/users/{id}/edit', function ($request, $response, $args) use ($router) {
    $id = $args['id'];
    $user = getUser($id);
    var_dump($user);
    if ($user === null) {
        return $response->withStatus(404)->write("User doesn't exists");
    }
    $params = [
        'user' => $user,
        'errors' => [],
        'urls' => ['allUsers' => $router->urlFor('users'), 'newUser' => $router->urlFor('newUser')]
    ];
    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
})->setName('editUser');


$app->patch('/users/{id}', function ($request, $response, $args) use ($router) {
    $id = $args['id'];

    if (!userExists($id)) {
        return $response->withStatus(404)->write('ERROR');
    }

    $userData = $request->getParsedBodyParam('user');
    $userData['id'] = $id;
    $validator = new \Validator();
    $errors = $validator->validate($userData);

    if (count($errors) === 0) {
        updateUser($id, $userData);
        $this->get('flash')->addMessage('success', 'User has been update');
        // $url = $router->urlFor('editUser', ['id' => $id]);
        return $response->withRedirect($router->urlFor('users'));
    }
    
    $params = [
        'user' => $userData,
        'errors' => $errors,
        'urls' => ['allUsers' => $router->urlFor('users'), 'newUser' => $router->urlFor('newUser')]
    ];

    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
});

$app->run();


