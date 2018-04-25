<?php
header("Content-Type: text/html; charset=utf-8");
require "packages/PHPMailer/src/OAuth.php";
require "packages/PHPMailer/src/Exception.php";
require "packages/PHPMailer/src/POP3.php";
require "packages/PHPMailer/src/SMTP.php";
require "packages/PHPMailer/src/PHPMailer.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



$link = mysql_connect('192.168.1.45', 'root', 'root')
or die('1Не удалось соединиться: ' . mysql_error());
echo '2Соединение успешно установлено';

mysql_set_charset('utf8', $link);

mysql_select_db('PW_Comission_Items') or die('3Не удалось выбрать базу данных');

$date = date('i');
if ($date == 10 || $date == 30 || $date == 50) {
    $startDate = time();
    $startDate = date('Y-m-d', strtotime('-1 week', $startDate));

    $query = "SELECT * FROM `money` WHERE `date` > '{$startDate}'";
    $result2 = mysql_query($query) or die('Запрос не удался: ' . mysql_error());
    $lastUpdateArray = array();

    while ($line = mysql_fetch_array($result2, MYSQL_ASSOC)) {
        $lastUpdateArray[$line["server"]] = $line['date'];
    }

    $fail = false;
    $str = "";
    foreach ($lastUpdateArray as $server => $time) {
        $to_time = strtotime($time);
        $from_time = time();
        $lastUpdate = round(abs($to_time - $from_time) / 60);

        if ($lastUpdate > 60) {
            $fail = true;

            if ($str) {
                $str .= ", ";
            }

            $str .= $server . "({$lastUpdate})";
        }
    }

    if ($fail) {
        send("SERVER ERROR 60 MINUTE OUT", $str);
    }
}

$query = 'SELECT * FROM mail WHERE `post` = 0';
$result = mysql_query($query) or die('Запрос не удался: ' . mysql_error());

while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
    try {
        send($line["subject"], $line["text"]);

        $sql = "UPDATE mail SET post=1 WHERE id=" . $line["id"];

        mysql_query($sql);
    } catch (Exception $e) {

    }
}

mysql_free_result($result);
mysql_close($link);


function send ($subject, $text = "") {
    if ($text == "") {
        $text = " ";
    }
    $mail = new PHPMailer(true);
    $mail->CharSet = 'utf-8';// Passing `true` enables exceptions
    //Server settings
    $mail->SMTPDebug = 2;                                 // Enable verbose debug output
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'garen6666@gmail.com';                 // SMTP username
    $mail->Password = 'ddd1974aaad';                           // SMTP password
    $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465;                                    // TCP port to connect to

    //Recipients
    $mail->setFrom('garen6666@gmail.com');
    $mail->addAddress('garen6666@gmail.com');     // Add a recipient

    //$subject = mb_convert_encoding($subject, 'utf-8', 'cp1251');

    //$subject = iconv('CP1251', 'UTF-8', $subject);

    //Content
    //$mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body    = $text;
    //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
}
