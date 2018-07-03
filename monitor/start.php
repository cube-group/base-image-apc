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
    /**
     * app name
     * @var string
     */
    private $appName = 'apc';
    /**
     * ding ding web hook url
     * @var mixed|null|string
     */
    private $ding = '';
    /**
     * app path
     * @var string
     */
    private $appPath = '';
    /**
     * 是否通过钉钉进行日志收集
     * @var bool
     */
    private $postOut = false;

    /**
     * CronTabMonitor constructor.
     */
    public function __construct()
    {
        error_reporting('E_ALL & ~E_NOTICE');

        echo "=========== CronTabMonitor ===========\n";

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
            $this->sendDing("\$APP_PATH/cron.json is nil.");
            exit("[ERROR] \$APP_PATH/cron.json is nil.\n");
        }
        echo "[CRON] cronContent: read success\n";

        if (!$cronJson = @json_decode($cronContent)) {
            $this->sendDing("can't json decode \$APP_PATH/cron.json");
            exit("[ERROR] can't json decode \$APP_PATH/cron.json\n");
        }
        echo "[CRON] cronJson: decode success\n";

        $this->postOut = Arrays::get($cronJson, 'postOut', false);
        if ($newDing = Arrays::get($cronJson, 'hook')) {
            $this->ding = $newDing;
        }

        $cronList = array();
        exec("crontab -l > /tmp/crontab.bak");
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
                exec("touch {$md5OutFile} {$md5ErrFile}");
                exec("echo \"{$time} {$value} >> {$md5OutFile} 2>> {$md5ErrFile}\" >> /tmp/crontab.bak");
                $cronList[] = $cronItem;
            }
            exec("crontab /tmp/crontab.bak && crond &");
        }

        //日志收集
        $outFile = '/cli-cron-out.log';
        while (true) {
            foreach ($cronList as $cron) {
                if ($err = system("cat {$cron['err']} && true > {$cron['err']}")) {
                    $this->sendDing("[TIME]\n{$cron['time']}\n[VALUE]\n{$cron['value']}\n[ERR]\n{$err}");
                }
                exec("cat {$cron['out']} >> {$outFile} && true > {$cron['out']}");
            }

            if ($this->postOut) {
                if ($out = system("cat {$outFile} && true > {$outFile}"))
                    $this->sendDing("[OUT]\n{$out}");
            } else {
                exec("true > {$outFile}");
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
        return gethostbyname(exec('hostname'));
    }

    private function sendDing($msg)
    {
        if ($this->ding) {
            $d = new LDing($this->ding);
            $d->send("[CRON-APC]\n[APP_NAME] {$this->appName}\n[NODE_IP] {$this->serverIp()}\n{$msg}\n");
        }
    }
}

new CronTabMonitor();