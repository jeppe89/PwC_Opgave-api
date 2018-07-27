<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tobscure\JsonApi\Document;
use Tobscure\JsonApi\ErrorHandler;
use Tymon\JWTAuth\Exceptions\JWTException;

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
        $errors = array();
        $status = 400;

        if ($exception instanceof ModelNotFoundException) {
            $status = 404;
            $error = $this->errorResponse(
                'Not Found',
                'The resource was not found',
                '',
                $status
            );
            array_push($errors, $error);
        }
        if ($exception instanceof UnauthorizedException) {
            $status = 401;
            $error = $this->errorResponse(
                'Unauthorized',
                'You are not authorized to do this',
                '',
                $status
            );
            $errors[] = $error;
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            $status = 405;
            $error = $this->errorResponse(
                'Method Not Allowed',
                'This method is not allowed',
                '',
                $status
            );
            $errors[] = $error;
        }

        if ($exception instanceof ValidationException) {
            $status = 422;
            foreach ($exception->errors() as $errorMsg) {
                $error = $this->errorResponse(
                    'Invalid Data',
                    array_shift($errorMsg),
                    ''
                );
                $errors[] = $error;
            }
        }

        if ($exception instanceof QueryException) {
            $status = 422;
            $error = $this->errorResponse(
                'Invalid ID',
                'The ID is not a valid ID',
                '',
                $status
            );
            $errors[] = $error;
        }

        if ($exception instanceof HttpException) {
            $status = $exception->getStatusCode();
            switch ($status) {
                case 403:
                    $error = $this->errorResponse(
                        'Forbidden',
                        'Not authorized to do this',
                        '',
                        $status
                    );
                    $errors[] = $error;
                    break;
            }
        }

        if ($exception instanceof JWTException) {
            $status = 500;
            $error = $this->errorResponse(
                'Authentication server error',
                'An error occurred while trying to authenticate your request',
                '',
                $status
            );
            $errors[] = $error;
        }


        $document = new Document;
        $document->setErrors($errors);

        //return response()->json($document, $status);
        return parent::render($request, $exception);
    }

    private function errorResponse(string $title, string $detail, string $source, int $status = null)
    {
        $response = array(
            'source' => array('pointer' => $source),
            'title' => $title,
            'detail' => $detail
        );
        if ($status) {
            $response['status'] = strval($status);
        }
        return $response;
    }
}
