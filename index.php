<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'helpers.php';
$db = new mysqli('127.0.0.1', 'root', '', 'birthdatabase');

if (isset($_POST['submit'])) {
    $name = $db->real_escape_string($_POST['name']);
    $phone = $db->real_escape_string($_POST['phone']);
    $email = $db->real_escape_string($_POST['email']);
    $birthday = $_POST['birthday'];

    $response = $db->query("INSERT INTO `Users` VALUES(null, '$name', '$phone', '$email', '$birthday', 0)");

    if ($response) header("location:index.php?success=true");
    else header("location:index.php?success=false");
}

$mail = new PHPMailer(true);
$dateToday = date('m-d');

$celebrantsToday = $db->query("SELECT * FROM `Users` WHERE `Birthday` LIKE '%$dateToday'")->fetch_all(MYSQLI_ASSOC);
foreach ($celebrantsToday as $celebrant) {
    if (!$celebrant['Status']) {
        $id = $celebrant['ID'];
        $phone = formatPhoneNumber($celebrant['PhoneNumber']);
        $logFile = fopen('email.log', 'a') or die("Failed to create file");
        $timeLog = strtoupper(date('[Y-m-d h:i:sa]'));
        $birthdayMessage = "Happy Birthday, " . $celebrant['Name'] . ".";

        try {
            $mail->IsSMTP();
            $mail->SMTPAuth   = true;
            $mail->Host       = "smtp.gmail.com";
            $mail->Port       = 587;
            $mail->Username   = "";
            $mail->Password   = "";
            $mail->SMTPSecure = 'tls';
            $mail->setFrom('', '');
            $mail->addAddress($celebrant['EmailAddress']);
            $mail->isHTML(true);
            $mail->Subject = 'Birthday email';
            $mail->Body = "$birthdayMessage <br /><br /><img src='cid:pic' alt='You' style='max-width: 100%; ' />";
            $mail->addEmbeddedImage('pic.png', 'pic');
            if ($mail->send()) {
                fwrite($logFile, "$timeLog\tBirthday email sent to " . $celebrant['EmailAddress'] . "\n");
                $db->query("UPDATE `Users` SET `Status` = 1 WHERE `ID` = '$id'");
                $birthdayMessage = urlencode($birthdayMessage);
                header("Location:https://api.whatsapp.com/send/?phone=$phone&text=$birthdayMessage&app_absent=0");
            } else fwrite($logFile, "$timeLog\tEmail was not sent to " . $celebrant['EmailAddress'] . ": $mail->ErrorInfo\n");
        } catch (Exception $e) {
            fwrite($logFile, "$timeLog\tEmail was not sent to " . $celebrant['EmailAddress'] . ": $mail->ErrorInfo\n");
        } finally {
            fclose($logFile);
        }
    }
}

// Reset status back to 0 after 1 day;
$users = $db->query("SELECT * FROM `Users`")->fetch_all(MYSQLI_ASSOC);
foreach ($users as $user) {
    $id = $user['ID'];
    $birthday = $user['Birthday'];
    if (date("m-d") > substr($birthday, 5)) $db->query("UPDATE `Users` SET `Status` = 0 WHERE `ID` = '$id'");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="notification.css" />
    <title></title>
</head>

<body>
    <form method="post" action="index.php" class="container horizontal-center">
        <div class="column">
            <div class="row">
                <div class="column">
                    <h4>Name</h4>
                    <input type="text" placeholder="Name" name="name" required />
                </div>
                <div class="column">
                    <h4>Phone Number</h4>
                    <input type="text" placeholder="Phone Number" name="phone" maxlength="14" pattern="^0?[\d]{10}$|^(\+)?[\d]{13}$" required />
                </div>
                <div class="column">
                    <h4>Email Address</h4>
                    <input type="email" placeholder="Email Address" name="email" required />
                </div>
                <div class="column">
                    <h4>Birthday</h4>
                    <input type="date" placeholder="Birthday" name="birthday" required />
                </div>
            </div>
            <div class="row">
                <button class="normal-btn" name="submit">Add</button>
            </div>
        </div>
    </form>

    <form class="container horizontal-center csv-form" action="csv.php" method="post" enctype="multipart/form-data">
        <div class="row">
            <label class="btn normal-outline-btn">Select CSV File<input type="file" name="file" accept=".csv" class="normal-btn" onchange="showFileName(this);" required /></label>
            <button class="normal-btn" type="submit">Upload</button>
            <label id="name-of-file"></label>
        </div>
    </form>

    <script src="notification.js"></script>
    <script>
        if (document.referrer) {
            switch (getParameter('success')) {
                case 'true':
                    showNotification(true, "Record(s) added successfully");
                    break;
                case 'false':
                    showNotification(false, "An error has occured");
                    break;
            }
        }

        function showFileName(input) {
            document.getElementById('name-of-file').textContent = input.files[0].name;
        }
    </script>
</body>

</html>