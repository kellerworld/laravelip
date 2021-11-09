<?php

namespace Kellerworld\Laravelip;
use Illuminate\Support\Facades\DB;
use GeoIp2\Database\Reader;
use Symfony\Component\HttpFoundation\Request as RequestIP;
use Illuminate\Support\Facades\Auth;

class CheckIP
{
    /*
     * 获取ip
     * 检查黑名单中是否存在
     * 存在则抛出异常
     * 不存在则忽略
     */
    public static function checkIP()
    {
        $RequestIP=new RequestIP();
//        var_dump($RequestIP);die;
        $ip=$RequestIP->createFromGlobals()->getClientIp();
//        var_dump(Auth::check());die;
        if(Auth::check() == false) {
            $CountryInfo=self::GetCountryName($ip);
//            var_dump($CountryInfo);die;
            $country_name=$CountryInfo['country_name'];
            self::CheckUserAgent($ip,$country_name.'_AuthFalse');
        }
        $whitelist=DB::table('whitelist')->where('ip',$ip)->get();
        if(count($whitelist)==0) {
            $list = DB::table('blacklist')->where('ip', $ip)->get();
            if (count($list) != 0) {
                //---------throw a exception
                DB::table('blacklist')->where('ip', $ip)->increment('times');
                //log start
                $file = '/tmp/throwout'. date("Y-m-d", time()) .'.log';
                $content = "++++++++++++\r\n";
                $content .= "-----" . date("Y-m-d H:i:s", time()) . "----\r\n";
                $content .= "-----" . $ip . "----\r\n";
                $content .= "SiteID:" . config('checkip.site_id') . "\n";
                file_put_contents($file, $content, FILE_APPEND);
                //log end

                throw new Exception('package report:ip is in blacklist.');
            }
        }
    }
    /*
     * self::addBlacklist($ip);
     */
    public static function addBlacklist($ip,$id){
        $whitelist=DB::table('whitelist')->where('ip',$ip)->get();
        if(count($whitelist)==0) {
            if(config('checkip.THROWOUT'))
            {
                DB::table('blacklist')->insert(
                    [
                        'ip' => $ip,
                        'times' => 1,
                        'created_at' => date('Y-m-d H:i:s', time()),
                        'updated_at' => date('Y-m-d H:i:s', time())
                    ]
                );
                //log start
                $file = '/tmp/throwout'. date("Y-m-d", time()) .'.log';
                $content = "++++++++++++\r\n";
                $content .= "-----" . date("Y-m-d H:i:s", time()) . "----\r\n";
                $content .= "-----" . $ip . "----\r\n";
                $content .= "SiteID:" . config('checkip.site_id') . "\n";
                file_put_contents($file, $content, FILE_APPEND);
                //log end
                throw new Exception('package report:ip black.');
            }else{
                DB::table('exception_request')
                    ->where('id', $id)
                    ->update(['status' => 1]);
                //log start
                $file = '/tmp/throwout'. date("Y-m-d", time()) .'.log';
                $content = "++++++++++++\r\n";
                $content .= "-----" . date("Y-m-d H:i:s", time()) . "----\r\n";
                $content .= "-----" . $ip . "----\r\n";
                $content .= "SiteID:" . config('checkip.site_id') . "\n";
                file_put_contents($file, $content, FILE_APPEND);
                //log end
            }
        }
    }
    public static function exceptionType($e){
        switch($e){
            case ($e instanceof HttpException):
                return 'HttpException';
                break;
            case ($e instanceof HttpResponseException):
                return 'HttpResponseException';
                break;
            case ($e instanceof NotFoundHttpException):
                return 'NotFoundHttpException';
                break;
            case ($e instanceof AccessDeniedHttpException):
                return 'AccessDeniedHttpException';
                break;
            case ($e instanceof BadRequestHttpException):
                return 'BadRequestHttpException';
                break;
            case ($e instanceof ConflictHttpException):
                return 'ConflictHttpException';
                break;
            case ($e instanceof ControllerDoesNotReturnResponseException):
                return 'ControllerDoesNotReturnResponseException';
                break;
            case ($e instanceof GoneHttpException):
                return 'GoneHttpException';
                break;
            case ($e instanceof HttpExceptionInterface):
                return 'HttpExceptionInterface';
                break;
            case ($e instanceof LengthRequiredHttpException):
                return 'LengthRequiredHttpException';
                break;
            case ($e instanceof MethodNotAllowedHttpException):
                return 'MethodNotAllowedHttpException';
                break;
            case ($e instanceof NotAcceptableHttpException):
                return 'NotAcceptableHttpException';
                break;
            case ($e instanceof PreconditionFailedHttpException):
                return 'PreconditionFailedHttpException';
                break;
            case ($e instanceof PreconditionRequiredHttpException):
                return 'PreconditionRequiredHttpException';
                break;
            case ($e instanceof ServiceUnavailableHttpException):
                return 'ServiceUnavailableHttpException';
                break;
            case ($e instanceof TooManyRequestsHttpException):
                return 'TooManyRequestsHttpException';
                break;
            case ($e instanceof UnauthorizedHttpException):
                return 'UnauthorizedHttpException';
                break;
            case ($e instanceof UnprocessableEntityHttpException):
                return 'UnprocessableEntityHttpException';
                break;
            case ($e instanceof UnsupportedMediaTypeHttpException):
                return 'UnsupportedMediaTypeHttpException';
                break;
            case ($e instanceof \Exception):
                return 'Exception1';
                break;
            default:
                return 'Not sure Exception';
        }
    }
    /*
     * 在/app/Exceptions/Handler.php中调用此方法
     * 当遇到exception时，检查ip是否属于美国
     * 属于，则放行
     * 否则，加黑名单，并记录
     * 不记录get传输方式的访问记录
     * 如果url中包含指定字符串包含,则不作记录
     * 如果request_data中包含指定字符串，则不作记录
     * 如果提交的数据中包含密码字段，则不作记录
     */
    public static function isNonUS($request,$exception){
        if(strpos($request->fullUrl(),'https://user.happymangocredit.com/api/v1') !== false){
            //如果url中包含指定字符串包含,则不作记录
        }
        else{
            self::checkIP();
            $site_id=config('checkip.site_id');
            $country_list=explode(',',config('checkip.country_list'));
            $browser='null';
            $country_iso_code='null';
            $country_name='null';
            $password=-1;
            foreach ($request->headers as $k=> $v) {
                if ($k == 'user-agent')
                {
                    $browser = $v[0];
                    break;
                }
            }
            foreach ((array)($request->request) as $key => $val){
                if(isset($val['password']))
                {
                    $val['password']='*****';
                    $password=1;
                }
                if(isset($val['repeatpwd']))
                {
                    $val['repeatpwd']='*****';
                    $password=1;
                }
                if(isset($val['password_confirmation']))
                {
                    $val['password_confirmation']='*****';
                    $password=1;
                }
                $request_data=json_encode($val);
                break;
            }
            //如果提交的数据中包含密码字段，则不拦截
            if($password==-1){
                $status_code=-1;
                foreach ((array)($exception) as $key => $val){
                    if(strpos($key,'statusCode')!==false)
                    {
                        $status_code=$val;
                    }

                }
                //log start
                $file = '/tmp/checkIP'. date("Y-m-d", time()) .'.log';
                $content = "++++++isNonUS++++++\r\n";
                $content .= "-----" . date("Y-m-d H:i:s", time()) . "----\r\n";
                $content .= "SiteID:" . config('checkip.site_id') . "\r\n";

                $content .= "exception:".json_encode((array)$exception)."\r\n";
                $content .= "status code:".$status_code."\r\n".$request;
                file_put_contents($file, $content,FILE_APPEND);
                //log end
                $RequestIP=new RequestIP();
                $ip=$RequestIP->createFromGlobals()->getClientIp();
                //本地文件数据库
                //        $reader = new Reader('D:\GeoLite2-Country.mmdb');
                //        $country_isoCode=$reader->country($ip)->country->isoCode;
                //record request for future analysis
                $host=$request->getHost();
                $prefix=substr($host , 0 , strpos($host, '.'));
                $CountryInfo=self::GetCountryName($ip);
//            var_dump($CountryInfo);die;
                $country_name=$CountryInfo['country_name'];
                $country_iso_code=$CountryInfo['country_iso_code'];
                //        var_dump($status_code);die;
                if(is_object($status_code)){
                    $status_code_str=json_encode($status_code，JSON_FORCE_OBJECT);
                    $request_data=$request_data.'--object:'.$status_code_str;
                }else{
                    if($status_code == 401){
                        self::CheckUserAgent($ip,$country_name.'_401');
                    }
                }
                if($status_code==-1){
                    $browser.="\r\n exception:".json_encode((array)$exception)."\n";
                }
                //如果传输方式不是GET，则可能记录、加黑名单;
                if($request->getMethod() != 'get' && $request->getMethod() != 'GET'){
                    //                json_decode($exception->getMessage ());
                    //                if(json_last_error() == JSON_ERROR_NONE && $site_id==3){      }
                    $status=0;
                    $str_arr=[
                        '"key":"page.login',
                        '"key":"page.si'
                    ];
                    foreach($str_arr as $key => $val){
                        if($status==0){
                            if(strpos($request_data,$val) !== false){
                                //如果包含
                                $status++;
                            }
                        }
                    }
                    //如果request_data中包含指定字符串，则不记录、不加黑名单
                    if($status==0){
                        $id=DB::table('exception_request')->insertGetId(
                            [
                                'ip' => $ip,
                                'url' => $request->fullUrl(),
                                'method' =>$request->getMethod(),
                                'request_data' =>$request_data,
                                'browser' =>$browser,
                                'status_code' =>$status_code,
                                'country_iso_code' =>$country_iso_code,
                                'country_name' =>$country_name,
                                'site_id' =>$site_id,
                                'exception_type' =>self::exceptionType($exception),
                                'created_at' => date('Y-m-d H:i:s',time()),
                                'updated_at' => date('Y-m-d H:i:s',time())
                            ]
                        );
                        //如果不在白名单国家或者如果字符串包含**，则加入黑名单
                        if(!in_array($country_iso_code,$country_list) || stristr($request_data,'{"0x":["') !== false || stristr($request->fullUrl(),'/.env') !== false)
                        {
                            self::addBlacklist($ip,$id);
                            self::CheckUserAgent($ip,$country_name.'_blacklist');
                        }
                    }
                }
            }
        }
    }
    /**
     * 判断IP是否在某个网络内
     * @param $ip
     * @param $network
     * @return bool
     */

    public static function ip_in_network($ip, $network)
    {
        $ip = (double) (sprintf("%u", ip2long($ip)));
        $s = explode('/', $network);
        $network_start = (double) (sprintf("%u", ip2long($s[0])));
        $network_len = pow(2, 32 - $s[1]);
        $network_end = $network_start + $network_len - 1;

        if ($ip >= $network_start && $ip <= $network_end)
        {
            return true;
        }
        return false;
    }

    public static function GetCountryName($ip)
    {
        $result=DB::table('geolite2_country_blocks_ipv4')->where('network', 'like', substr($ip , 0 , strpos($ip, '.')+1).'%')->get();
        if(count($result)>0){
            foreach ($result as $val){
                if(self::ip_in_network($ip, $val->network))
                {
                    $country=DB::table('geolite2_country_locations_en')->where('geoname_id',$val->geoname_id)->select('country_iso_code','country_name')->get();
                    $data['country_iso_code']=$country[0]->country_iso_code;
                    $data['country_name']=$country[0]->country_name;
                    break;
                }
            }
        }else{
            $data['country_iso_code']=$data['country_name']='Unmatched';
        }
        return $data;
    }
    public static function CheckUserAgent($ip,$country_name)
    {
//        var_dump(Auth::check());die;

        //检查是否过期token

        $ua_arr=[
            "Safari",
            "Opera",
            "Firefox",
            "Chrome",
            "Trident",
            "360SE",
            "QQBrowser",
            "UBrowser",
            "The World",
            "compatible; MSIE",
            "TencentTraveler",
            "Avant Browser",
            "Amazon Simple Notification Service Agent",
            "Mozilla",
            "Go-http-client/1.1",
            "UCWEB"
        ];
        $status=0;
        if(!isset($_SERVER['HTTP_USER_AGENT'])){
            $_SERVER['HTTP_USER_AGENT']='undefined';
        }
        foreach($ua_arr as $key => $val){
            if($status==0){
                if(strpos($_SERVER['HTTP_USER_AGENT'],$val) !== false){
                    //如果包含
                    $status++;
                }
            }
        }
//        var_dump($status);die;
        if($status==0){
            //log start
            $file  = '/tmp/CheckUserAgent'. date("Y-m-d", time()) .'.log';
            $content = "-----".date("Y-m-d H:i:s",time())."----\r\n";
            $content .= "SiteID:".config('checkip.site_id')."\r\n";
            $content .= "USER_AGENT:".$_SERVER['HTTP_USER_AGENT']."\r\n";
            $content .= "IP:".$ip."\r\n";
            $content .= "Country Name:".$country_name."\r\n";
            file_put_contents($file, $content,FILE_APPEND);
            //log end
        }
//            var_dump($_SERVER['HTTP_USER_AGENT']);die;
    }
}


