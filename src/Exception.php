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
        echo '<html>
	<head>
		<link href=\'//fonts.googleapis.com/css?family=Lato:100\' rel=\'stylesheet\' type=\'text/css\'>
		<style>
			body {
				margin: 0;
				padding: 0;
				width: 100%;
				height: 100%;
				color: #B0BEC5;
				display: table;
				font-weight: 100;
				font-family: \'Lato\';
			}
			.container {
				text-align: center;
				display: table-cell;
				vertical-align: middle;
			}

			.content {
				text-align: center;
				display: inline-block;
			}

			.title {
				font-size: 72px;
				margin-bottom: 40px;
			}
			.link_contac{font-size:48px;color:#2cbbff;}
			.link_contac > a{border-bottom:1px solid #2cbbff;font-weight: bold;}
		</style>
	</head>
	<body>
	<div class="container">
		<div class="content">
			<div class="title">An error occurred in responding to your request.</div>
			<div class="title link_contac">Please <a href="https://www.happymangocredit.com/contact.html" style="color:#2cbbff;text-decoration:none; !important;">contact us</a>. </div>
			</div>
		</div>
	</body>
</html>';
        die;
//        return response()->view(
//            'errors.401',
//            array(
//                'exception' => $this
//            )
//        );
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        echo '<html>
	<head>
		<link href=\'//fonts.googleapis.com/css?family=Lato:100\' rel=\'stylesheet\' type=\'text/css\'>
		<style>
			body {
				margin: 0;
				padding: 0;
				width: 100%;
				height: 100%;
				color: #B0BEC5;
				display: table;
				font-weight: 100;
				font-family: \'Lato\';
			}
			.container {
				text-align: center;
				display: table-cell;
				vertical-align: middle;
			}

			.content {
				text-align: center;
				display: inline-block;
			}

			.title {
				font-size: 72px;
				margin-bottom: 40px;
			}
			.link_contac{font-size:48px;color:#2cbbff;}
			.link_contac > a{border-bottom:1px solid #000;}
		</style>
	</head>
	<body>
	<div class="container">
		<div class="content">
			<div class="title">An error occurred in responding to your request.</div>
			<div class="title link_contac">Please <a href="https://www.happymangocredit.com/contact.html" style="color:#333;text-decoration:none; !important;">contact us</a>. </div>
			</div>
		</div>
	</body>
</html>';
        die;
//        return response()->view(
//            'errors.401',
//            array(
//                'exception' => $this
//            )
//        );
    }
}