# laravelip
1、laravelip.sql导入数据库

2、app\Exceptions\Handler.php

    1)use Kellerworld\Laravelip\CheckIP;
    2)在render方法中添加 
        CheckIP::isNonUS($request,$exception);
3、app\Http\Controllers\Controller.php

    1)use Kellerworld\Laravelip\CheckIP;
    2)在__construct方法中添加 
              CheckIP::CheckIP();
4、composer.json中添加

    "require": {
        ...
        "kellerworld/laravelip": "dev-master",
        ... 
    }
5、执行：composer require kellerworld/laravelip              
