<?php

namespace App\Http\Controllers\Node;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NodeController extends Controller
{
    public function error(string $error, int $code = 400): Response
    {
        return response()->json(compact('error'), $code);
    }
}
