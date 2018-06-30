<?php

if (!isset($_COOKIE["sciBowlSessionID"])) {
    header("Location:login.php?loginMessage=Unauthorized:%20Please%20Log%20In");
}

$sID = $_COOKIE["sciBowlSessionID"];
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

    $sql = "SELECT * FROM activeSessions WHERE sID='" . $sID . "'";

    $stmt = $conn_SID->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    if ( count($result) < 1 ) {
        header("Location:login.php");
    }
    if ( (string)$result[0]["REMOTE_ADDR"] === (string)$REMOTE_ADDR && (string)$result[0]["HTTP_X_FORWARD_FOR"] === (string)$HTTP_X_FORWARD_FOR ) {
        $currentTime = time();
        $sql = "UPDATE activeSessions SET LAST_ACCESS=" . $currentTime ." WHERE activeSessions WHERE sID='" . $sID . "'";
    }
    else {
        header("Location:login.php?loginMessage=Unauthorized:%20Please%20Log%20In");
    }

    $sql = "UPDATE activeSessions SET LAST_ACCESS=" . $currentTime . " WHERE sID='" . $sID . "'";
    $conn_SID->exec($sql);
}
catch(PDOException $e)
{
    echo $sql . "<br>" . $e->getMessage();
}

$conn_SID = null;

?>
