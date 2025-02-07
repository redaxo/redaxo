<?php

namespace Redaxo\Core\Http\Exception;

use Redaxo\Core\Http\Response;
use Throwable;

final class NotFoundHttpException extends HttpException
{
    public function __construct(string|Throwable $cause)
    {
        parent::__construct($cause, Response::HTTP_NOT_FOUND);
    }
}
