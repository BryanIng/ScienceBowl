<?php include "checkSID"; ?>

<html>
<head>
	<link rel="icon" href="images/backgroundSmall.png">
    <link rel="stylesheet" type="text/css" href="interactiveStyle.css" >
</head>
<body>
	<br>
	<button onclick="window.location.href='matchRecorder.php';" style="background-color: rgb(0,200,0);">
		Submit Another Round (DON'T press the back arrow)
	</button>
<?php
	$servername = "";   //INSERT SERVERNAME
    $username = "";     //INSERT USERNAME
    $password = "";     //INSERT PASSWORD

    try {
        $dbName = "XXX_match_records"; 
		$tbName = "test_" . date("Y_m_d") . "_" . time();
	
		$conn = new PDO("mysql:host=$servername;dbname=$dbName", $username, $password );
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		echo "Connected successfully <br>";
		
		$sql = "CREATE TABLE $tbName (
			Question INT(11),
			QuestionType VARCHAR(10),
			nAnswers INT(11),
			FirstAnswerTeam VARCHAR(1),
			FirstAnswerPlayer INT(11),
			FirstAnswerCorrect TINYINT(1),
			FirstAnswerInterrupt TINYINT(1),
			SecondAnswerTeam VARCHAR(1),
			SecondAnswerPlayer INT(11),
			SecondAnswerCorrect TINYINT(1),
			SecondAnswerInterrupt TINYINT(1)
		)";
		
		$conn->exec($sql);
		echo "Table '" . $tbName . "' created sucessfully <br> ";
		echo "<h3 style='text-align: center;'>The following table was submitted:</h3><br>";
		echo "<table><tbody>";
		echo "<tr><th>Number</th><th>Type</th><th>Category</th><th>Format</th><th>Team</th><th>Player #</th><th>Answer</th><th>Interrupt</th></tr>";
		
        $x = 0;
        $Q_NUMBER = 0;
		$nQuests = $_POST['nQuests'];
		while ($x < $nQuests) {
			$nAnswers = $_POST['nA' . (string)$x];
			$fAT = "NULL";
			$fAP = "NULL";
			$fA = "NULL";
			$fQI = "NULL";
			$sAT = "NULL";
			$sAP = "NULL";
			$sA = "NULL";
			$sQI = "NULL";

			if ( $nAnswers >= 1 ) {
				$fAT =  "'" . $_POST['fAT' . (string)$x] . "'";
				$fAP =  $_POST['fAP' . (string)$x];
				if ($_POST['fA' . (string)$x] === "correct") {
					$fA = "TRUE";
				}
				else {
					$fA = "FALSE";
				}
				if ($_POST['fQI' . (string)$x] === 'true') {
					$fQI = "TRUE";
				}
				else {
					$fQI = "FALSE";
				}

				echo "<tr><td> " . $_POST['qN' . (string)$x] . "</td>" .
					"<td> " . $_POST['qT' . (string)$x] . "</td>" .
					"<td> </td>" . 
					"<td> </td>" . 
					"<td>" . $fAT . "</td>" . 
					"<td>" . $fAP . "</td>" . 
					"<td>" . $fA . "</td>" .
					"<td>" . $fQI . "</td></tr>";

			}
			if ( $nAnswers >= 2 ) {
				$sAT =  "'" . $_POST['sAT' . (string)$x] . "'";
				$sAP =  $_POST['sAP' . (string)$x];
				if ($_POST['sA' . (string)$x] === "correct") {
					$sA = "TRUE";
				}
				else {
					$sA = "FALSE";
				}
				if ($_POST['sQI' . (string)$x] === 'true') {
					$sQI = "TRUE";
				}
				else {
					$sQI = "FALSE";
				}

				echo "<tr><td> " . $_POST['qN' . (string)$x] . "</td>" .
					"<td> " . $_POST['qT' . (string)$x] . "</td>" .
					"<td> </td>" . 
					"<td> </td>" . 
					"<td>" . $sAT . "</td>" . 
					"<td>" . $sAP . "</td>" . 
					"<td>" . $sA . "</td>" .
					"<td>" . $sQI . "</td></tr>";
			}
			$sql = "INSERT INTO $tbName VALUES  ('" .
				$_POST['qN' . (string)$x] . "', '" . 
				$_POST['qT' . (string)$x] . "', " . 
				$nAnswers . ', ' .
				$fAT . ', ' .
				$fAP . ', ' .
				$fA . ', ' .
				$fQI . ', ' .
				$sAT . ', ' .
				$sAP . ', ' .
				$sA . ', ' .
				$sQI . 
			")";
            $conn->exec($sql);
            $Q_NUMBER = $_POST['qN' . (string)$x];
			$x += 1;
		}

		echo "</tbody></table>";
        
        $sql = "INSERT INTO recordIndex VALUES ('" . $tblName . "','" .
            $_POST["player0"] . "', '" . 
            $_POST["player1"] . "', '" . 
            $_POST["player2"] . "', '" . 
            $_POST["player3"] . "', '" . 
            $_POST["player4"] . "', '" . 
            $_POST["player5"] . "', '" . 
            $_POST["player6"] . "', '" . 
            $_POST["player7"] . "', " . 
            $Q_NUMBER . ", " .
            $nAnswers . 
        ")";

        //$conn->exec($sql);
		
	}
	catch(PDOException $e)
	{
		echo $sql . "<br>" . $e->getMessage();
	}

	$conn->close();
?>
</body>
</html>
