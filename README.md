# laravelip
1、laravelip.sql导入数据库

2、app\Exceptions\Handler.php

    1)use Kellerworld\Laravelip\CheckIP;
    2)在render方法中添加 
        CheckIP::isNonUS($request);
3、app\Http\Controllers\Controller.php

    1)use Kellerworld\Laravelip\CheckIP;
    2)在__construct方法中添加 
              CheckIP::CheckIP();