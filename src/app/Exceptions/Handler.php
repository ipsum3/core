<?php


namespace Ipsum\Core\app\Exceptions;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Prologue\Alerts\Facades\Alert;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler
{
    public static function register(ExceptionHandler $handler): void
    {
        $handler->renderable(function (HttpExceptionInterface $e) {
            if (
                $e instanceof TokenMismatchException
                || $e->getPrevious() instanceof TokenMismatchException
            ) {
                Alert::warning(
                    'Le formulaire a expiré, merci de le valider à nouveau.'
                )->flash();

                return back()->withInput();
            }

            return null;
        });
    }
}
