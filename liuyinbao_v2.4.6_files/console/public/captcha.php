<?php
// 登录验证码类
class Captcha {
    private $width = 120;
    private $height = 40;
    private $codeLength = 4;
    private $code;
    
    // 生成随机验证码
    public function generate() {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $this->code = '';
        for($i = 0; $i < $this->codeLength; $i++) {
            $this->code .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        // 存入SESSION
        session_start();
        $_SESSION['captcha_code'] = strtoupper($this->code);
        $_SESSION['captcha_time'] = time();
        return $this->code;
    }
    
    // 输出图片
    public function output() {
        header('Content-Type: image/png');
        
        $img = imagecreatetruecolor($this->width, $this->height);
        
        // 背景色
        $bg = imagecolorallocate($img, 243, 250, 254);
        imagefill($img, 0, 0, $bg);
        
        // 验证码文字
        $color = imagecolorallocate($img, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
        
        // 写入文字
        $font = 5;
        $x = 15;
        for($i = 0; $i < strlen($this->code); $i++) {
            imagestring($img, $font, $x, 12, $this->code[$i], $color);
            $x += 22;
        }
        
        // 添加干扰线
        for($i = 0; $i < 4; $i++) {
            $lineColor = imagecolorallocate($img, mt_rand(150, 200), mt_rand(150, 200), mt_rand(150, 200));
            imageline($img, mt_rand(0, $this->width), mt_rand(0, $this->height), 
                     mt_rand(0, $this->width), mt_rand(0, $this->height), $lineColor);
        }
        
        // 添加干扰点
        for($i = 0; $i < 60; $i++) {
            $pointColor = imagecolorallocate($img, mt_rand(150, 200), mt_rand(150, 200), mt_rand(150, 200));
            imagesetpixel($img, mt_rand(0, $this->width), mt_rand(0, $this->height), $pointColor);
        }
        
        imagepng($img);
        imagedestroy($img);
    }
    
    // 验证验证码
    public static function check($code) {
        session_start();
        if(!isset($_SESSION['captcha_code'])) {
            return false;
        }
        
        // 5分钟过期
        if(time() - $_SESSION['captcha_time'] > 300) {
            unset($_SESSION['captcha_code']);
            unset($_SESSION['captcha_time']);
            return false;
        }
        
        $result = strtoupper($code) === $_SESSION['captcha_code'];
        unset($_SESSION['captcha_code']);
        unset($_SESSION['captcha_time']);
        return $result;
    }
}

// 如果是获取验证码图片
if(isset($_GET['action']) && $_GET['action'] === 'captcha') {
    $captcha = new Captcha();
    $captcha->generate();
    $captcha->output();
    exit;
}