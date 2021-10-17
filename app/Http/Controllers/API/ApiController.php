<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{
    public function success(string $message, int $code = 200, $data = []): Response
    {
        return $this->response(true, $data, $message, $code);
    }

    public function error(string $message, int $code = 400, $data = []): Response
    {
        return $this->response(false, $data, $message, $code);
    }

    private function response(bool $success, $data, string $message, int $code): Response
    {
        return response()->json(compact('success', 'data', 'message'), $code);
    }
}
