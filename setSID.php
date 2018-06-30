<?php

$sID = $_POST["sID"];
$REMOTE_ADDR = $_SERVER["REMOTE_ADDR"];
$HTTP_X_FORWARD_FOR = $_SERVER["HTTP_X_FORWARD_FOR"];

$servername = "";   //INSERT SERVERNAME
$username = "";     //INSERT USERNAME
$password = "";     //INSERT PASSWORD


try {
    $dbName = "XXX_sessions"; //INSERT DB NAME

    $conn_SID = new PDO("mysql:host=$servername;dbname=$dbName", $username, $password );
    $conn_SID->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $currentTime = time();
    $oldTime = $currentTime - 1800;
    $sql = "DELETE FROM activeSessions WHERE LAST_ACCESS<" . $oldTime;

    $conn_SID->exec($sql);

    $sql = "INSERT INTO activeSessions (sID, REMOTE_ADDR, HTTP_X_FORWARD_FOR, LAST_ACCESS) VALUES ('" . $sID . "','" . (string)$REMOTE_ADDR . "','" . (string)$HTTP_X_FORWARD_FOR . "','" . $currentTime . "')";
    setcookie("sciBowlSessionID", $sID, time() + 86400, "/");
    //echo "blah";

    $conn_SID->exec($sql);

}
catch(PDOException $e)
{
    echo $sql . "<br>" . $e->getMessage();
}

$conn_SID = null;

?>
