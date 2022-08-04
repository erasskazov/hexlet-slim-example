<?php

namespace Users;

function loadUsers($pathToUsersJson)
{
    $jsonFile = file_exists($pathToUsersJson) ? file_get_contents($pathToUsersJson) : '';
    $json = json_decode($jsonFile, true);
    $users = $json['users'] ?? [];
    $count = $json['count'] ?? 0;
    return ['count' => $count, 'users' => $users];
}

function addUser ($user, $pathToUsersJson)
{
    ['count' => $count, 'users' => $users] = loadUsers($pathToUsersJson);
    $newCount = $count + 1;
    $user['id'] = $newCount;
    $users[] = $user;
    $newJson = ['count' => $newCount, 'users' => $users];
    file_put_contents("users/users.json", json_encode($newJson));
    return true;
}

function userExists($id, $pathToUsersJson)
{
    ['count' => $count, 'users' => $users] = loadUsers($pathToUsersJson);
    foreach ($users as $user) {
        if ( (string) $user['id'] === (string) $id) {
            return true;
        }
    }
    return false;
}

function getUser($id, $pathToUsersJson)
{
    ['count' => $count, 'users' => $users] = loadUsers($pathToUsersJson);
    foreach ($users as $user) {
        if ( (string) $user['id'] === (string) $id) {
            return $user;
        }
    }
    return null;
}