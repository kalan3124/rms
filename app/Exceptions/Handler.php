<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Storage;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        Storage::put('/public/errors/'.date('Y/m/d').".txt",date("H:m:s")."\n\nStack Trace:-\n".$exception->__toString()."\n\nRequest:-\n".json_encode($request->all())."\n\n");
        switch (get_class($exception)) {
            case 'App\Exceptions\MediAPIException':
                return response()->json([
                    'result' => false,
                    'message' => $exception->getMessage(),
                    'code' => 'ME' . $exception->getCode(),
                ]);
            case 'App\Exceptions\SalesAPIException':
                return response()->json([
                    'result' => false,
                    'message' => $exception->getMessage(),
                    'code' => 'SE' . $exception->getCode(),
                ]);
            case 'App\Exceptions\WebAPIException':
                return response()->json([
                    'message' => $exception->getMessage(),
                    'code' => 'WE' . $exception->getCode(),
                ], 422);
            case 'App\Exceptions\DisAPIException':
                return response()->json([
                    'result' => false,
                    'message' => $exception->getMessage(),
                    'code' => 'SE' . $exception->getCode(),
                ]);
            default:
                return parent::render($request, $exception);

        }

    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Auth\AuthenticationException $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            $response = ['result' => false, 'message' => "Unathenticated user!"];
            return response()->json($response);
        }
        return redirect()->guest('login');
    }
}
