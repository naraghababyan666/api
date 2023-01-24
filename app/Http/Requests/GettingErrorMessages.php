<?php

namespace App\Http\Requests;

class GettingErrorMessages
{
    public static function gettingMessage($errors){
        $messages = [];
        foreach ($errors as $error => $key){
            $messages[$error] = $key[0];
        }
        return $messages;
    }
}
