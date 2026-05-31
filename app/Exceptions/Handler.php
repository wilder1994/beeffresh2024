<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;
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

    public function render($request, Throwable $e): Response
    {
        if ($e instanceof TokenMismatchException) {
            if ($request->is('checkout/pagar', 'checkout')) {
                return redirect()
                    ->route('carrito.ver')
                    ->with('error', 'Tu sesión expiró o la página llevaba mucho tiempo abierta. Vuelve al carrito, confirma de nuevo y paga.');
            }

            if ($request->is('logout')) {
                if ($request->user() !== null) {
                    return redirect('/logout');
                }

                return redirect()
                    ->guest(route('login'))
                    ->with('status', 'Tu sesión expiró. Vuelve a iniciar sesión.');
            }
        }

        return parent::render($request, $e);
    }
}
