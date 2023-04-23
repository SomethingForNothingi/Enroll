<?php

namespace App\Exceptions;

use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;

class CustomJWTException extends JWTException
{
    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Token is invalid'
        ], 401);
    }
}
