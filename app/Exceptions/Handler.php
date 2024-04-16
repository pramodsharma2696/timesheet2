<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof MethodNotAllowedHttpException) {
            return redirect('/home');
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        if ($exception instanceof UnauthorizedHttpException) {
            if ($exception->getMessage() === 'Token not provided') {
                return response()->json(['message' => 'TOKEN NOT PROVIDED'], 401);
            }
        }

        return parent::render($request, $exception);
    }
}
