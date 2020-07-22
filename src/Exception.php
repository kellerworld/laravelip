<?php

namespace Kellerworld\Laravelip;

use Exception as Ex;

class Exception extends Ex
{
    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        return response()->view(
            'errors.500',
            array(
                'exception' => $this
            )
        );
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->view(
            'errors.500',
            array(
                'exception' => $this
            )
        );
    }
}