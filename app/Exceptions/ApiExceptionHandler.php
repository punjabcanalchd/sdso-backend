<?php

namespace App\Exceptions;

use Throwable;
use Log;
use Mail;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

use App\Mail\ExceptionOccured;
use App\Models\ExceptionLog;

class ApiExceptionHandler
{
    /**
     * Handle API exceptions.
     */
    public function render(Request $request, Throwable $e)
    {
        if (!($request->is("api/*") || $request->wantsJson())) {
            return null;
        }

        $this->handleException($e);

        $status = $this->resolveStatusCode($e);

        $message = $this->resolveMessage($e);

        $response = [
            "success" => false,
            "message" => $message,
            "code" => $status,
        ];
    
        if ($e instanceof ValidationException) {
            $response["errors"] = $e->errors();
        }

        if (config("app.debug")) {
            $response["exception"] = class_basename($e);
            $response["debug_message"] = $e->getMessage();
            $response["file"] = $e->getFile();
            $response["line"] = $e->getLine();
        }

        return response()->json($response, $status);
    }

    /**
     * Handle logging + email.
     */
    protected function handleException(Throwable $exception): void
    {
        try {
            $this->addLog($exception);
            $content = [
                "message" => $exception->getMessage(),
                "file" => $exception->getFile(),
                "line" => $exception->getLine(),
                "trace" => $exception->getTrace(),
                "url" => request()->url(),
                "body" => request()->all(),
                "ip" => $this->getClientIp(),
            ];

            Mail::to("punjabcanalchd@gmail.com")->send(
                new ExceptionOccured($content)
            );
        } catch (Throwable $exception) {
            Log::error($exception);
        }
    }

    /**
     * Save exception into database.
     */
    protected function addLog(Throwable $exception): void
    {
        try {
            $model = new ExceptionLog();
            $model->message = strval($exception->getMessage());
            $model->line = $exception->getLine();
            $model->trace = json_encode($exception->getTrace());
            $model->url = request()->url();
            $model->body = json_encode(request()->all());
            $model->ip = $this->getClientIp();
            $model->status = 0;
            $model->save();
        } catch (Throwable $e) {
            Log::error($e);
        }
    }

    /**
     * Get client IP address.
     */
    protected function getClientIp(): string
    {
        $clientIp = "";

        if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            $ipArray = explode(",", $ip);
            $clientIp = trim($ipArray[0]);
        } elseif (!empty($_SERVER["HTTP_X_REAL_IP"])) {
            $clientIp = $_SERVER["HTTP_X_REAL_IP"];
        } else {
            if (isset($_SERVER["REMOTE_ADDR"])) {
                $clientIp = $_SERVER["REMOTE_ADDR"];
            }
        }

        return $clientIp;
    }

    /**
     * Resolve HTTP status code.
     */
    protected function resolveStatusCode(Throwable $e): int
    {
        return match (true) {
            $e instanceof ThrottleRequestsException => 429,
            $e instanceof AuthenticationException => 401,
            $e instanceof ValidationException => 422,
            $e instanceof ModelNotFoundException,
            $e->getPrevious() instanceof ModelNotFoundException
                => 404,
            $e instanceof HttpExceptionInterface => $e->getStatusCode(),
            default => ($code = (int) $e->getCode()) >= 100 && $code <= 599
                ? $code
                : 500,
        };
    }

    /**
     * Resolve response message.
     */
    protected function resolveMessage(Throwable $e): string
    {
        return match (true) {
            $e instanceof ThrottleRequestsException
                => "Too many requests have been made. Please try again later.",
            $e instanceof AuthenticationException
                => "The access token provided is invalid.",
            $e instanceof ValidationException
                => "The provided data failed validation.",
            $e instanceof ModelNotFoundException,
            $e->getPrevious() instanceof ModelNotFoundException
                => "The requested resource was not found.",
            $e instanceof HttpExceptionInterface => $e->getMessage(),
            default => config("app.debug")
                ? $e->getMessage()
                : "An unexpected error has occurred. Please try again later.",
        };
    }
}
