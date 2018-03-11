<?php

class StaticFileServer extends HttpComponent {
    public static $PATH = '/';
    public static $indexFile = 'index.html';
    public static $siteFilesDir = __DIR__ . '/html';
    
    private $headerHtml;
    private $footerHtml;
    private $ip;
    private $port;
    private $host;
    
    public function onLoad($ip, $port, $host) {
        $this->headerHtml = file_get_contents(StaticFileServer::$siteFilesDir . '/header.html');
        $this->footerHtml = file_get_contents(StaticFileServer::$siteFilesDir . '/footer.html');
        $this->ip = $ip;
        $this->port = $port;
        $this->host = $host;
    }

    public function onRequest($con, $request, $response) {
        if (!$con->isSecure()) {
            $response->setStatusCode(301);
            $response->setHeader("Location", "https://" . $this->host . $request->getPath());
            $response->write("");
            return true;
        }
        
        if (substr($request->getPath(), -1) == '/') {
            $requestedFilePath = StaticFileServer::$siteFilesDir . $request->getPath() . StaticFileServer::$indexFile;
        } else {
            $requestedFilePath = StaticFileServer::$siteFilesDir . $request->getPath();
        }
        
        if (!file_exists($requestedFilePath)) {
            $requestedFilePath .= '.html';
            
            if (!file_exists($requestedFilePath)) {
                $requestedFilePath = StaticFileServer::$siteFilesDir . '/404.html';
            }
        }

        if (file_exists($requestedFilePath)) {
            if (substr($requestedFilePath, -5) == '.html') {
                $response->write($this->headerHtml . file_get_contents($requestedFilePath) . $this->footerHtml);
            } else {
                $response->write(file_get_contents($requestedFilePath));
            }
            
            return true;
        }

        return false;
    }
}
