<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 2018/7/2
 * Time: 上午9:16
 */

use Myaf\Net\LDing;
use Myaf\Utils\Arrays;
use Myaf\Utils\FileUtil;

require __DIR__ . '/vendor/autoload.php';

/**
 * Class CronTabMonitor
 */
class CronTabMonitor
{
    private $appName = 'apc';
    private $outLog = '/tmp/php_crontab.log';
    private $errLog = '/tmp/php_crontab.err';
    private $ding = 'https://oapi.dingtalk.com/robot/send?access_token=86a6a6c2f0fde8811412b39739bed47155e88982ac6ddc203ddc43e9cb920287';
    private $appPath = '/Users/linyang/Workspace/GITHUB/base-image-apc';

    /**
     * 是否通过钉钉进行日志收集
     * @var bool
     */
    private $postOut = false;

    public function __construct()
    {
        error_reporting('E_ALL & ~E_NOTICE');

        if ($appName = getenv('APP_NAME')) {
            $this->appName = $appName;
        }
        echo "[CRON] appName: {$this->appName}\n";

        //ding talk hook url
        if ($ding = getenv('APP_MONITOR_HOOK')) {
            $this->ding = $ding;
        }
        echo "[CRON] ding: {$this->ding}\n";

        if ($appPath = realpath(getenv('APP_PATH'))) {
            $this->appPath = $appPath;
        }
        echo "[CRON] appPath: {$this->appPath}\n";

        if (!$cronContent = FileUtil::read($this->appPath . '/cron.json')) {
            $this->sendDing("can't find \$APP_PATH/cron.json");
            exit("can't find \$APP_PATH/cron.json\n");
        }
        echo "[CRON] cronContent: read success\n";

        if (!$cronJson = @json_decode($cronContent)) {
            $this->sendDing("can't json decode \$APP_PATH/cron.json");
            exit("can't json decode \$APP_PATH/cron.json\n");
        }
        echo "[CRON] cronJson: decode success\n";

        $this->postOut = Arrays::get($cronJson, 'postOut', false);
        if ($newDing = Arrays::get($cronJson, 'hook')) {
            $this->ding = $newDing;
        }

        $cronList = array();
        system("crontab -l > /tmp/crontab.bak");
        if ($list = Arrays::get($cronJson, 'jobs', array())) {
            foreach ($list as $key => $item) {
                if (!$time = Arrays::get($item, 'time')) {
                    continue;
                }
                if (!$value = Arrays::get($item, 'value')) {
                    continue;
                }
                $md5OutFile = "/tmp-php-cron-" . md5($time . $value) . ".log";
                $md5ErrFile = "/tmp-php-cron-" . md5($time . $value) . ".err";
                $cronItem = array('err' => $md5ErrFile, 'out' => $md5OutFile, 'time' => $time, 'value' => $value);
                system("touch {$md5OutFile} {$md5ErrFile}");
                system("echo \"{$time} {$value} >> {$md5OutFile} 2>> {$md5ErrFile}\" >> /tmp/crontab.bak");
                $cronList[] = $cronItem;
            }
            system("crontab /tmp/crontab.bak");
        }

        //日志收集
        $outFile = '/cli-cron-out.log';
        while (true) {
            foreach ($cronList as $cron) {
                if ($err = system("cat {$cron['err']} && true > {$cron['err']}")) {
                    $this->sendDing("[CRON-ERR] {$cron['time']} {$cron['value']} {$err}");
                }
                system("cat {$cron['out']} >> {$outFile} && true > {$cron['out']}");
            }

            if ($this->postOut) {
                $this->sendDing(system("cat {$outFile} && true > {$outFile}"));
            } else {
                system("true > {$outFile}");
            }

            sleep(30);
        }
    }

    /**
     * 获取外网IP-内网IP
     * @return mixed
     */
    private function serverIp()
    {
        return $_SERVER['REMOTE_ADDR'] . '-' . gethostbyname(exec('hostname'));
    }

    private function sendDing($msg)
    {
        if ($this->ding) {
            $d = new LDing($this->ding);
            $d->send("[{$this->appName}][{$this->serverIp()}] {$msg}");
        } else {
            echo "can't find env APP_MONITOR_HOOK, can't send ding.\n";
        }
    }
}

new CronTabMonitor();