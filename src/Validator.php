<?php

class Validator
{
    public function validate($user)
    {
        $errors = [];
        if (empty($user['nickname'])) {
            $errors['nickname'] = "Can't be Blank";
        }
        if (empty($user['email'])) {
            $errors['email'] = "Can't be Blank";
        }
        return $errors;
    }
}