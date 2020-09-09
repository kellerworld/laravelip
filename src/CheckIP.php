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
        $list = DB::table('blacklist')->where('ip', $ip)->get();
        //        var_dump(DB::getQueryLog());
//        var_dump(DB::getQueryLog());

        if (count($list) != 0) {
            //---------throw a exception
            DB::table('blacklist')->where('ip',$ip)->increment('times');
            if(config('checkip.THROWOUT'))
            throw new Exception('package report:ip is in blacklist.');
        }

    }
    /*
     * self::addBlacklist($ip);
     */
    public static function addBlacklist($ip){
        $whitelist=DB::table('whitelist')->where('ip',$ip)->get();
        if(count($whitelist)==0) {
            DB::table('blacklist')->insert(
                [
                    'ip' => $ip,
                    'times' => 1,
                    'created_at' => date('Y-m-d H:i:s', time()),
                    'updated_at' => date('Y-m-d H:i:s', time())
                ]
            );
        }
        if(config('checkip.THROWOUT'))
        throw new Exception('package report:ip black.');
    }
    /*
     * 在/app/Exceptions/Handler.php中调用此方法
     * 当遇到exception时，检查ip是否属于美国
     * 属于，则放行
     * 否则，加黑名单，并记录
     */
    public static function isNonUS($request,$exception){
        self::checkIP();
        $site_id=config('checkip.site_id');
        $country_list=explode(',',config('checkip.country_list'));
        $browser='null';
        $country_iso_code='null';
        $country_name='null';
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
            }
            if(isset($val['repeatpwd']))
            {
                $val['repeatpwd']='*****';
            }
            if(isset($val['password_confirmation']))
            {
                $val['password_confirmation']='*****';
            }
            $request_data=json_encode($val);
            break;
        }
        foreach ((array)($exception) as $key => $val){
            $status_code=$val;
            break;
        }
        $RequestIP=new RequestIP();
        $ip=$RequestIP->createFromGlobals()->getClientIp();
        //本地文件数据库
//        $reader = new Reader('D:\GeoLite2-Country.mmdb');
//        $country_isoCode=$reader->country($ip)->country->isoCode;
        //record request for future analysis
        $host=$request->getHost();
        $prefix=substr($host , 0 , strpos($host, '.'));
        $result=DB::table('geolite2_country_blocks_ipv4')->where('network', 'like', substr($ip , 0 , strpos($ip, '.')+1).'%')->get();
        if(count($result)>0){
            foreach ($result as $val){
                if(self::ip_in_network($ip, $val->network))
                {
                    $country=DB::table('geolite2_country_locations_en')->where('geoname_id',$val->geoname_id)->select('country_iso_code','country_name')->get();
                    $country_iso_code=$country[0]->country_iso_code;
                    $country_name=$country[0]->country_name;
                    break;
                }
            }
        }else{
            $country_iso_code=$country_name='Unmatched';
        }
//        var_dump($status_code);die;
        if(strpos($request->fullUrl(),'happymangocredit')!== false && $status_code ==0)
        {}else{
        if($request->getMethod() != 'get' && $request->getMethod() != 'GET'){
            json_decode($exception->getMessage ());
            if(json_last_error() == JSON_ERROR_NONE && $site_id==3){
                DB::table('exception_request')->insert(
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
                        'status' =>1,
                        'created_at' => date('Y-m-d H:i:s',time()),
                        'updated_at' => date('Y-m-d H:i:s',time())
                    ]
                );
            }else{
                DB::table('exception_request')->insert(
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
                        'created_at' => date('Y-m-d H:i:s',time()),
                        'updated_at' => date('Y-m-d H:i:s',time())
                    ]
                );
                if(!in_array($country_iso_code,$country_list) && DB::table('blacklist')->where('ip',$ip)->count()==0)
                {
                    self::addBlacklist($ip);
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
}


