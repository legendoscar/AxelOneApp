<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    public function render($request, Throwable $exception)
    {
        // Check if the request expects a JSON response
        if ($request->expectsJson()) {
            // Check if the exception is a QueryException
            if ($exception instanceof QueryException) {
                // Return a JSON response with a custom error message
                return response()->json([
                    'message' => 'A database error occurred. Please try again later.',
                    // 'error' => $exception->getMessage()
                ], 500);
            }

            // Handle other exceptions here if needed
        }

        return parent::render($request, $exception);
    }
}
