<?php

namespace Ipsum\Core\app\Exceptions;


use Prologue\Alerts\Facades\Alert;
use Throwable;

class Handler extends \App\Exceptions\Handler
{


    public function register()
    {
        parent::register();

        $this->renderable(function (Throwable $e) {
            if ($e->getPrevious() instanceof \Illuminate\Session\TokenMismatchException) {
                Alert::warning("Le fomulaire a expiré, merci de la valider à nouveau.")->flash();
                return back()->withInput();
            }
        });
    }
}
