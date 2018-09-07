<?php
namespace models;

class User extends Base{
    
    function adduser($email,$password){
        $stmt = self::$pdo->prepare("INSERT INTO users(email,password) values(?,?)");
        $res = $stmt->execute([$email,$password]);
        
        $code = md5(rand(1,999999));
        $redis = \libs\Redis::getInstance();

        $value = json_encode([
            'email'=>$email,
            'password'=>$password
        ]);
        $key = "email_user:{$code}";
        $redis->setex($key,5000,$value);

        $name = explode("@",$email);
        $from = [$email,$name[0]];

        $message = [
            'title' => '来自圈圈丸的信~',
            'content' => "点击激活VIP账号特权 ==> ：<br> <a href='http://localhost:9999/user/active?code={$code}'>http://localhost:9999/user/active?code={$code}</a>。",
            'from' => $from,
        ];
        
        $message = json_encode($message);

        $redis->lpush('email',$message);

        echo '邮件已发送至邮箱,请前往激活后登陆~';
    }

    function tologin($user,$pwd){
        $stmt = self::$pdo->prepare("SELECT * FROM users WHERE email=? AND password=?");
        $stmt->execute([
            $user,
            $pwd
            ]);
        $state = $stmt->fetch();
        if($state){
            $_SESSION['id']=$state['id'];
            $_SESSION['email']=$state['email'];
            return TRUE;
        }else{
            return FALSE;
        }
    }

}

