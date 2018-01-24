<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2018/1/18
 * Time: 下午 1:54
 */

namespace App\Plugins;



class FtpClient
{
    private $host;
    private $port;
    private $username;
    private $password;
    private $isSsl = true;
    private $client;

    public function __construct($host, $username, $password, $port = 21)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * 設定 ssl連線
     * @param $isSsl
     */
    public function setIsSsl($isSsl)
    {
        $this->isSsl = $isSsl;
    }

    /**
     * 啟動連線
     * @return bool
     */
    public function connect() {
        if ($this->isSsl) {
            $this->client = ftp_ssl_connect($this->host, $this->port);
        } else {
            $this->client = ftp_connect($this->host, $this->port);
        }
        return $this->login();
    }

    /**
     * 帳號登入
     * @return bool
     */
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

    /**
     * 關閉連線
     */
    public function close()
    {
        ftp_close($this->client);
    }

    /**
     * 放置檔案
     * @param $filePath
     * @param $toPath
     */
    public function putFile($filePath, $toPath)
    {
        if ($this->connect()) {
            $fp = fopen($filePath, 'r');
            ftp_fput($this->client, $toPath, $fp, FTP_BINARY);
            fclose($fp);
        }
        $this->close();

    }

    /**
     * 移動檔案
     * @param $filePath
     * @param $toPath
     */
    public function moveFile($filePath, $toPath)
    {
        if ($this->connect()) {
            ftp_rename($this->client, $filePath, $toPath);
        }
        $this->close();
    }

    /**
     * 新增目錄
     * @param $dir
     */
    public function mkDir($dir)
    {
        if ($this->connect()) {
            if(ftp_chdir($this->client, $dir) == false) {
                ftp_mkdir($this->client, $dir);
            }
        }
        $this->close();
    }
}