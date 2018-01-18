<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2018/1/18
 * Time: 下午 1:54
 */

namespace App\Plugins;


use Illuminate\Support\Facades\Log;

class FtpClient
{
    private $host;
    private $port;
    private $username;
    private $password;
    private $client;

    public function __construct($host, $username, $password, $port = 21)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;


    }

    public function connect() {
        $this->client = ftp_ssl_connect($this->host, $this->port);
        return $this->login();
    }

    public function login()
    {
        $isLogin = false;
        if ($this->client) {
            $isLogin = ftp_login($this->client, $this->username, $this->password);
        }
        if ($isLogin) {
            //忽略server private ip, 使用外部ip
            ftp_set_option($this->client, FTP_USEPASVADDRESS, false);
            //設定被動模式
            ftp_pasv($this->client, true);
        }
        return $isLogin;
    }

    public function close()
    {
        ftp_close($this->client);
    }

    public function putFile($filePath, $toPath)
    {
        if ($this->connect()) {
            Log::debug('filePath:' . $filePath . ', toPath:' .  $toPath);
            $fp = fopen($filePath, 'r');
            ftp_fput($this->client, $toPath, $fp, FTP_BINARY);
            fclose($fp);
        }
        $this->close();

    }
}