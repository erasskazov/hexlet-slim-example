<?php

namespace Users;

const USERS_PATH = 'users/users.json';

function loadUsers($loadCount = false)
{
    $jsonFile = file_exists(USERS_PATH) ? file_get_contents(USERS_PATH) : '';
    $json = json_decode($jsonFile, true);
    $users = $json['users'] ?? [];
    $count = $json['count'] ?? 0;
    if ($loadCount) {
        return ['count' => $count, 'users' => $users];
    }
    return $users;
}

function addUser ($user)
{
    ['count' => $count, 'users' => $users] = loadUsers(true);
    $newCount = $count + 1;
    $user['id'] = $newCount;
    $users[] = $user;
    $newJson = ['count' => $newCount, 'users' => $users];
    file_put_contents(USERS_PATH, json_encode($newJson));
    return true;
}

function updateUser($id, $newDataUser)
{
    ['count' => $count, 'users' => $users] = loadUsers(true);
    $updatedUsers = [];
    foreach ($users as $user) {
        $updatedUser = $user;
        if ((string) $user['id'] === (string) $id) {
            $updatedUser['nickname'] = $newDataUser['nickname'];
            $updatedUser['email'] = $newDataUser['email'];
        }
        $updatedUsers[] = $updatedUser;
    }
    $newJson =['count' => $count, 'users' => $updatedUsers];
    file_put_contents(USERS_PATH, json_encode($newJson));
    return true;
}

function userExists($id)
{
    $users = loadUsers();
    foreach ($users as $user) {
        if ( (string) $user['id'] === (string) $id) {
            return true;
        }
    }
    return false;
}

function getUser($id)
{
    $users = loadUsers();
    foreach ($users as $user) {
        if ( (string) $user['id'] === (string) $id) {
            return $user;
        }
    }
    return null;
}