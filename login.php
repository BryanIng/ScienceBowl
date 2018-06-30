<?php
$loginMessage = "";

if ( $_GET["loginMessage"] === "loginAttempt" ) {

    $submitUsername = $_POST["uHash"];
    $submitPassword = $_POST["pHash"];

    $servername = "";   //INSERT SERVERNAME
    $username = "";     //INSERT USERNAME
    $password = "";     //INSERT PASSWORD

    try {
        $dbName = "XXX_accounts";

        $conn = new PDO("mysql:host=$servername;dbname=$dbName", $username, $password );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT * FROM users WHERE username ='" . $submitUsername . "'";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        if ( count($result) > 0 ) {
            $row = $result[0];
            if ($row["password"] === $submitPassword) {
                include "setSID.php";
                header("Location:matchRecorder.php");
            }
            else {
                $loginMessage = "Username or Password is Incorrect";
            }
        }
        else {
            $loginMessage = "Username or Password is Incorrect";
        }
    }
    catch(PDOException $e)
    {
        echo $sql . "<br>" . $e->getMessage();
    }

    $conn = null;
}
else {
    if (isset($_GET["loginMessage"])) {
        $loginMessage = $_GET["loginMessage"];
    }
}

?>

<head>
    <link rel="icon" href="images/backgroundSmall.png">
    <link rel="stylesheet" type="text/css" href="interactiveStyle.css" >
    <style>
        #loginPanel {
            position: absolute;
            top: 10%;
            right: 20%;
            width: 60%;
            background-color: #f0f0f0;
            box-shadow: 0 25px 25px 0 rgba(0, 0, 0, 0.5);
        }
    </style>
    <script src="sha3.js">
    </script>
    <script src="aes.js"></script>
    <script>
        var loginButtonClick = function() {
            var visForm = document.getElementById("visibleForm");
            var submitForm = document.getElementById("submitForm");
            
            var un = visForm.elements["username"].value;
            var pw = visForm.elements["password"].value;
            visForm.elements["username"].value = "";
            visForm.elements["password"].value = "";

            submitForm.elements["uHash"].value = hashToString(sha3(stringToData(un),256));
            submitForm.elements["pHash"].value = hashToString(sha3(stringToData(pw),256));

            var d = new Date();
            var sIDseed = un + pw + String(d.getTime());
            var sID = hashToString(sha3(stringToData(sIDseed),512));
            sessionStorage.sciBowlSessionID = sID;
            submitForm.elements["sID"].value = sID;

            submitForm.submit();
        }
    </script>
</head>
<body>
    <div class="backgroundSheetFull">
        
    </div>
    <div class="coverSheet" style="width: 100%; height: 100%; background: rgba(0,0,0,0.2);" >
        <div class="floatCoverPanel">
                <div class="coverPanel" >
                    <br style="line-height: 0px; margin: 0;">
                    <h1>
                        Science Bowl Data Tracker v 0.1
                    </h1>
                    <div class="formText">
                        Please Enter Your Login Credentials:
                    </div>
                    <form id="visibleForm">
                        <input type="text" name="username" placeholder="Username" value="">
                        <input type="password" name="password" placeholder="Password" value="">
                    </form>
                    <form id="submitForm" action="login.php?loginMessage=loginAttempt" method="post">
                        <input type="hidden" name="uHash" value="">
                        <input type="hidden" name="pHash" value="">
                        <input type="hidden" name="sID" value="">
                    </form>
                    <div class="formText" style="text-align: center; color: red;" >
                    <?php
                    echo $loginMessage;
                    ?>
                    </div>
                    <button style='background-color: rgb(0,199,0);' onclick='loginButtonClick()'>
                        Login
                    </button>
                </div>
            </div>
    </div>
</body>
