<?php

$db = new mysqli("127.0.0.1", 'root', '', 'birthdatabase');
$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');

if (!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $csvMimes)) {
    if (is_uploaded_file($_FILES['file']['tmp_name'])) {
        $csvFile = fopen($_FILES['file']['tmp_name'], 'r');
        fgetcsv($csvFile);
        while (($line = fgetcsv($csvFile)) !== FALSE) {
            $name = $db->real_escape_string($line[1]);
            $phone = $db->real_escape_string($line[2]);
            $email = $db->real_escape_string($line[3]);
            $birthday = $line[4];

            $prevQuery = "SELECT `ID` FROM `Users` WHERE `EmailAddress` = '$email' AND `PhoneNumber` = '$phone'";
            $prevResult = $db->query($prevQuery);

            if ($prevResult->num_rows > 0) $db->query("UPDATE `Users` SET `Name` = '$name', `Birthday` = '$birthday' WHERE `EmailAddress` = '$email' AND `PhoneNumber` = '$phone'");
            else $db->query("INSERT INTO `Users` VALUES (null, '$name', '$phone', '$email', '$birthday', 0)");
        }
        fclose($csvFile);
        $qstring = '?success=true';
    } else $qstring = '?success=false';
}

header("location:index.php$qstring");

?>