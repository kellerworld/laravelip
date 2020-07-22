<?php

namespace Kellerworld\Laravelip;
use Illuminate\Support\Facades\DB;
use GeoIp2\Database\Reader;
use Symfony\Component\HttpFoundation\Request as RequestIP;

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
        $ip=$RequestIP->createFromGlobals()->getClientIp();
//        $ip='124.133.163.112';
        $list=DB::table('blacklist')->where('ip',$ip)->get();
        //        var_dump(DB::getQueryLog());
//        var_dump(DB::getQueryLog());

        if(count($list)!=0){
            //---------throw a exception
            throw new Exception('package report:ip is in blacklist.');
        }
    }
    /*
     * self::addBlacklist($ip);
     */
    public static function addBlacklist($ip){
//        DB::table('blacklist')->where('ip',$ip)->get();
        DB::table('blacklist')->insert(
            [
                'ip' => $ip,
                'created_at' => date('Y-m-d H:i:s',time()),
                'updated_at' => date('Y-m-d H:i:s',time())
            ]
        );
    }
    /*
     * 在/app/Exceptions/Handler.php中调用此方法
     * 当遇到exception时，检查ip是否属于美国
     * 属于，则放行
     * 否则，加黑名单，并记录
     */
    public static function isNonUS($request){
        $RequestIP=new RequestIP();
        $ip=$RequestIP->createFromGlobals()->getClientIp();
//        $ip='124.133.163.112';
        $reader = new Reader('/home/www-data/GeoLite2-Country.mmdb');
        $country_isoCode=$reader->country($ip)->country->isoCode;
        //record request for future analysis
        DB::table('exception_request')->insert(
            [
                'ip' => $ip,
                'url' => $request->fullUrl(),
                'method' =>$request->getMethod(),
                'created_at' => date('Y-m-d H:i:s',time()),
                'updated_at' => date('Y-m-d H:i:s',time())
            ]
        );
        if($country_isoCode!=='US')
        {
            self::addBlacklist($ip);
        }
//        $country=$reader->country('124.133.163.112');
//
//        return $country_isoCode;
    }
}


