<?php include "checkSID.php"; ?>

<!DOCTYPE html>
<head>
    <link rel="icon" href="images/backgroundSmall.png">
    <link rel="stylesheet" type="text/css" href="interactiveStyle.css" >
    <?php include "sheetsAPI.php" ?>
    <style>
        body {
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            margin: 0 0 0 0;
            z-index: 0;
        }
        #gameRunContainer {
            position: absolute;
            width: 100%;
            height: 100%;
        }
        #gameRunPlayers {
            position: relative;
            left: 10%;
            width: 80%;
            height: 25%;
        }
        .playerBox {
            padding: 10px;
            width: 12%;
        }
        .playerBoxContainer {
            position: relative;
            text-align: center;
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 5px 5px 0 rgba(0, 0, 0, 0.2);
        }
        .playerBoxContainer:hover {
            box-shadow: 0 10px 10px 0 rgba(0, 0, 0, 0.35);
        }
        .playerBoxContainer:active {
            box-shadow: 0 3px 3px 0 rgba(0, 0, 0, 0.15);
            background-color: #aaaaaa;

        }
        .playerBoxPicture {
            top: 0;
            width: 100%;
            height: 80%;
            background-image: url("images/person.svg");
        }
        .playerBoxText {
            position: absolute;
            top: 65%;
            left: 50%;
            transform: translate( -50%, -50% );
        }
        .playerBoxText p {
            font-size: 300%;
        }
        .button {
            background-color: #e0e0e0;
            box-shadow: 0 5px 5px 0 rgba(0, 0, 0, 0.2);
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            font-size: 2em;
            text-align: center;
            margin: 10px;
        }

        #questionCorrectSelectorContainer {
            pointer-events: none;
        }
        #continueToNextQuestionContainer {
        }

        #interruptButton {
            background-color: rgb(220, 220, 220); font-size: 1.5em;
        }
        #nextQuestionButton {
            background-color: rgb(204, 204, 204);
        }
        #previousQuestionButton {
            background-color:rgb(204, 204, 204);
        }
        #submitPanelContainer {
            display: none;
        }
    </style>
    <script>
        var usingQSetInfo = false;
        var questionSetInfo = [];
        function parseMatchText(textLines) {
            var l = textLines.length;
            var i = 0;
            while (i < l) {
                var text = textLines[i];
                if (text.search("QUESTION: ") != -1) {
                    var qNum = text.substr(10);
                    i++;
                    text = textLines[i];
                    var type = text.substr(10);
                    i++;
                    text = textLines[i];
                    var subj = text.substr(10);
                    i++;
                    text = textLines[i];
                    var form = text.substr(10);
                    i++;
                    text = textLines[i];
                    var quest = text.substr(10);
                    var choices = [0,0,0,0];

                    if (form == "multiple choice") {
                        i++;
                        choices[0] = textLines[i];
                        i++;
                        choices[1] = textLines[i];
                        i++;
                        choices[2] = textLines[i];
                        i++;
                        choices[3] = textLines[i];
                    }

                    i++;
                    text = textLines[i];
                    var answer = text.substr(10);
                    
                    var questionSetRoundInfo = [qNum, type, subj, form, quest, choices, answer];
                    questionSetInfo.push(questionSetRoundInfo);
                }
                i++;
            }
        }

        var allText;
        function readTextFile(url) {
            var rawFile = new XMLHttpRequest();
            rawFile.open("GET", url, true);
            rawFile.onreadystatechange = function() {
                if (rawFile.readyState === 4) {
                    allText = rawFile.responseText;
                    //document.getElementById("textSection").innerHTML = allText;
                    var lineText = allText.split("\n");
                    parseMatchText(lineText);

                    var displayBox = document.getElementById("questSetInfoText");
                    displayBox.style.color = "green";
                    displayBox.innerHTML = "Success!";
                    if (document.getElementById("questSetInfoCheck").checked == true) {
                        usingQSetInfo = true;
                    }
                }
            }
            rawFile.send();
        }
        function openRoundData(set, round) {
            var url = "https://scibowl.rf.gd/questionSets/scibowlpdfs/set" + String(set) + "round" + String(round) + ".txt";
            readTextFile(url);
        }

        function questSetCheckHandler() {
            var checkBox = document.getElementById("questSetInfoCheck");
            var container = document.getElementById("questSetInfoContainer");
            if (checkBox.checked == true) {
                container.style.display = "inherit";
            }
            else {
                container.style.display = "none";
                usingQSetInfo = false;
            }
        }

        function importQuestionInfo() {
            var form = document.getElementById("newRoundForm");
            var set = Number(form.elements["questionSet"].value);
            var round = Number(form.elements["roundNumber"].value);

            var displayBox = document.getElementById("questSetInfoText");
            var goodRound = false;
            if (set >= 1 && set <= 9) {
                if (set == 5 || set == 6) {
                    if (round >= 1 && round <= 15) {
                        goodRound = true;
                    }
                }
                else if (round >= 1 && round <= 17) {
                    goodRound = true;
                }
            }

            if (goodRound) {
                displayBox.style.color = "gray";
                displayBox.innerHTML = "Importing..."
                openRoundData(set, round);
            }
            else {
                displayBox.style.color = "red";
                displayBox.innerHTML = "Error: this set/round doesn't exist";
            }
            
        }
    </script>
    <script>
        var players = [];

        var nextQuestionButton;
        var previousQuestionButton;
        var correctButton;
        var incorrectButton;
        var interruptButton;
        var questionCorrectSelectors;
        var matchMode = false;
        
        var identifyButtonVariables = function () {
            nextQuestionButton = document.getElementById("nextQuestionButton");
            previousQuestionButton = document.getElementById("previousQuestionButton");
            correctButton = document.getElementById( "correctButton" );
            incorrectButton = document.getElementById( "incorrectButton" );
            interruptButton = document.getElementById("interruptButton");
            questionCorrectSelectors = document.getElementById("questionCorrectSelectorContainer");
        }

        var startNewRound = function(x) {
            var newRoundForm = document.getElementById("newRoundForm");

            var i = 1;
            while (i < 5) {
                var val = newRoundForm.elements["A" + i].value;
                newRoundForm.elements["A" + i].value = "";
                players.push(val);

                var playerBoxDiv = document.createElement("div");
                var playerBox = document.getElementById("playerBox_A" + i);
                playerBox.appendChild(playerBoxDiv);

                playerBoxDiv.innerHTML = val;
                playerBoxDiv.style.fontSize = "1.2em";

                i++;
            }
            var i = 1;
            while (i < 5) {
                var val = newRoundForm.elements["B" + i].value;
                newRoundForm.elements["B" + i].value = "";
                players.push(val);

                var playerBoxDiv = document.createElement("div");
                var playerBox = document.getElementById("playerBox_B" + i);
                playerBox.appendChild(playerBoxDiv);

                playerBoxDiv.innerHTML = val;
                playerBoxDiv.style.fontSize = "1.2em";

                i++;
            }
            
            var questSet = newRoundForm.elements["questionSet"].value;
            var roundNumber = newRoundForm.elements["roundNumber"].value;
            //var pdfurl = newRoundForm.elements["pdfurl"].value;
            if ( newRoundForm.elements["roundType"].value === "matchMode" ) {
                matchMode = true;
            }

            if (usingQSetInfo) {
                document.getElementById("displayQuestionInfo").style.display = "block";
                var qInf = questionSetInfo[0];
                document.getElementById("displayQuestion").innerHTML = qInf[2] + " " + qInf[3] + ": " + qInf[4];
                if (qInf[3] == "multiple choice") {
                    document.getElementById("displayAnswerChoices").innerHTML = qInf[5][0] + "<br>" + qInf[5][1] + "<br>" + qInf[5][2] + "<br>" + qInf[5][3];
                }
                else {
                    document.getElementById("displayAnswerChoices").innerHTML = "";
                }
                document.getElementById("displayAnswer").innerHTML = "Answer: " + qInf[6];
            }

            document.getElementById("startRoundPanelContainer").style.display = "none";
            identifyButtonVariables();
        }
    </script>
    <script>
        var questionInformation = [];
        var mode = "edit";
        var answerTry;

        var questionNumber = 1;
        var questionType = "toss-up";

        var thisQuestionInterrupt = false;
        var nAnswers = 0;
        var firstAnswerTeam = "none";
        var firstAnswer = "none";
        var firstAnswerPerson = "none";
        var firstQuestInterrupt = false;
        var secondAnswerTeam = "none";
        var secondAnswer = "none";
        var secondAnswerPerson = "none";
        var secondQuestInterrupt = false;

        //match mode variables
        var correctAnswerTeam = "none";
        var bonusTeam;

        var enablePlayerBoxes = function() {
            var boxes = document.getElementsByClassName("playerBoxContainer");
            var nBoxes = boxes.length;
            var i = 0;
            while (i < nBoxes) {
                boxes[i].style.pointerEvents = "auto";
                boxes[i].style.backgroundColor = "white";
                i++;
            }
        }
        var disablePlayerBoxes = function() {
            var boxes = document.getElementsByClassName("playerBoxContainer");
            var nBoxes = boxes.length;
            var i = 0;
            while (i < nBoxes) {
                boxes[i].style.pointerEvents = "none";
                boxes[i].style.backgroundColor = "#e0e0e0";
                i++;
            }
        }
        var disablePlayerBoxesTeam = function(team) {
            var boxes = document.getElementsByClassName("playerBoxContainer");
            var nBoxes;
            var i;
            if (team === "A") {
                i = 0;
                nBoxes = 4;
            }
            else if (team === "B") {
                i = 4;
                nBoxes = 8;
            }
            while (i < nBoxes) {
                boxes[i].style.pointerEvents = "none";
                boxes[i].style.backgroundColor = "#e0e0e0";
                i++;
            }
        }
        var playerBoxClicked = function(elemName, team, player) {
            identifyButtonVariables();
            if (nAnswers === 0) {
                firstAnswerTeam = team;
                firstAnswerPerson = player;
            }
            else {
                secondAnswerTeam = team;
                secondAnswerPerson = player;
            }
            nAnswers++;

            disablePlayerBoxes();
            var backColor;
            if (team === "A") { backColor = "#ff7070";}
            else { backColor = "#ffed51"}
            document.getElementById(elemName).style.backgroundColor = backColor;
            
            interruptButton.style.color = "black";
            interruptButton.style.backgroundColor = "rgb(204,204,204)";
            correctButton.style.color = "black";
            incorrectButton.style.color = "black";
            correctButton.style.backgroundColor = "rgb(170,210,170)";
            incorrectButton.style.backgroundColor = "rgb(210,170,170)";
            document.getElementById("questionCorrectSelectorContainer").style.pointerEvents = "auto";
            previousQuestionButton.innerHTML = "Reset";
            nextQuestionButton.style.pointerEvents = "none";
            nextQuestionButton.style.backgroundColor = "rgb(220,220,220)";
            nextQuestionButton.style.color = "gray";
        }

        var interruptButtonHandler = function( interruptButton ) {
            var button = document.getElementById(interruptButton);
            if (thisQuestionInterrupt) {
                button.style.backgroundColor = "rgb(204, 204, 204)";
                button.innerHTML = "Interrupt: No";
                thisQuestionInterrupt = false;
                if (nAnswers === 1) {
                    firstQuestInterrupt = false;
                }
                else if (nAnswers === 2) {
                    secondQuestInterrupt = false;
                }
            }
            else {
                button.style.backgroundColor = "#ff9438";
                button.innerHTML = "Interrupt: Yes";
                thisQuestionInterrupt = true;
                if (nAnswers === 1) {
                    firstQuestInterrupt = true;
                }
                else if (nAnswers === 2) {
                    secondQuestInterrupt = true;
                }
            }
        }

        var correctButtonHandler = function() {
            correctButton.style.backgroundColor = "rgb(0, 199, 0)";
            incorrectButton.style.backgroundColor = "rgb(204, 204, 204)";
            correctButton.style.color = "white";
            incorrectButton.style.color = "black";
            
            nextQuestionButton.innerHTML = "Next Question";
            nextQuestionButton.style.color = "white";
            nextQuestionButton.style.pointerEvents = "auto";
            nextQuestionButton.style.backgroundColor = "rgb(0,199,0)";
            previousQuestionButton.innerHTML = "Reset";

            if (nAnswers === 1) {
                firstAnswer = "correct";
                correctAnswerTeam = firstAnswerTeam;
                
            }
            else if (nAnswers === 2) {
                secondAnswer = "correct";
                correctAnswerTeam = secondAnswerTeam;
            }
        }
        var incorrectButtonHandler = function() {
            incorrectButton.style.backgroundColor = "rgb(255, 60, 60)";
            correctButton.style.backgroundColor = "rgb(204, 204, 204)";
            incorrectButton.style.color = "white";
            correctButton.style.color = "black";

            previousQuestionButton.innerHTML = "Reset";
            nextQuestionButton.style.pointerEvents = "auto";

            if (nAnswers === 1) {
                if (matchMode && questionType === "bonus") {
                    firstAnswer = "incorrect";
                    nextQuestionButton.innerHTML = "Next Question";
                    nextQuestionButton.style.color = "white";
                    nextQuestionButton.style.backgroundColor = "rgb(0,199,0)";
                    correctAnswerTeam = "none";
                }
                else {
                    firstAnswer = "incorrect";
                    nextQuestionButton.innerHTML = "Continue";
                    nextQuestionButton.style.color = "black";
                    nextQuestionButton.style.backgroundColor = "rgb(170,210,170)";
                    correctAnswerTeam = "none";
                }
            }
            else if (nAnswers === 2) {
                secondAnswer = "incorrect";
                nextQuestionButton.innerHTML = "Next Question";
                nextQuestionButton.style.color = "white";
                nextQuestionButton.style.backgroundColor = "rgb(0,199,0)";
                correctAnswerTeam = "none";
            }
        }

        var resetQuestion = function() {
            thisQuestionInterrupt = false;
            
            questionCorrectSelectors.style.pointerEvents = "none";

            interruptButton.innerHTML = "Interrupt: No";
            interruptButton.style.color = "gray";
            interruptButton.style.backgroundColor = "rgb(220,220,220)";
            correctButton.style.color = "gray";
            incorrectButton.style.color = "gray";
            correctButton.style.backgroundColor = "rgb(210,225,210)";
            incorrectButton.style.backgroundColor = "rgb(225,210,210)";

            nextQuestionButton.innerHTML = "No Answer";
            previousQuestionButton.innerHTML = "Back";
            nextQuestionButton.style.color = "black";
            previousQuestionButton.style.color = "black";
            nextQuestionButton.style.backgroundColor = "rgb(204,204,204)";
            previousQuestionButton.style.backgroundColor = "rgb(204,204,204)";
            nextQuestionButton.style.pointerEvents = "auto";
            enablePlayerBoxes();

            if (matchMode && questionType === "bonus") {
                var otherTeam = "A";
                if (bonusTeam === "A") {
                    otherTeam = "B";
                }
                disablePlayerBoxesTeam( otherTeam );
            }
        }

        var nextQuestion = function() {
            var thisQuestionInfo = { questionNumber, questionType, nAnswers, 
                                    firstAnswerTeam, firstAnswerPerson, firstAnswer, firstQuestInterrupt,
                                    secondAnswerTeam, secondAnswerPerson, secondAnswer, secondQuestInterrupt };
            questionInformation.push( thisQuestionInfo );

            if (questionType === "toss-up") {
                questionType = "bonus"
                if (matchMode && correctAnswerTeam === "none") {
                    questionType = "toss-up";
                    questionNumber++;
                }
                else if (matchMode) {
                    bonusTeam = correctAnswerTeam;
                }
            }
            else if (questionType === "bonus") {
                questionType = "toss-up";
                questionNumber++;
            }
            nAnswers = 0;
            firstAnswerTeam = "none";
            firstAnswer = "none";
            firstAnswerPerson = "none";
            firstQuestInterrupt = false;
            secondAnswerTeam = "none";
            secondAnswer = "none";
            secondAnswerPerson = "none";
            secondQuestInterrupt = false;
            correctAnswerTeam = "none";
            resetQuestion();

            answerTry = 1;

            document.getElementById("questionNumberHeading").innerHTML = "Question " + questionNumber + ": " + questionType;

            if (usingQSetInfo) {
                var qN = 2 * (questionNumber - 1);
                if (qN >= questionSetInfo.length) {
                    openSubmitScreen();
                    return;
                }

                if (questionType === "bonus") {
                    qN++;
                }
                var qInf = questionSetInfo[qN];
                document.getElementById("displayQuestion").innerHTML = qInf[2] + " " + qInf[3] + ": " + qInf[4];
                if (qInf[3] == "multiple choice") {
                    document.getElementById("displayAnswerChoices").innerHTML = qInf[5][0] + "<br>" + qInf[5][1] + "<br>" + qInf[5][2] + "<br>" + qInf[5][3];
                }
                else {
                    document.getElementById("displayAnswerChoices").innerHTML = "";
                }
                document.getElementById("displayAnswer").innerHTML = "Answer: " + qInf[6];
            }
        }

        var nextQuestionButtonHandler = function() {
            if (nAnswers === 2 || firstAnswer === "correct" || firstAnswerTeam === "none" || (answerTry === 2 && nAnswers === 1) || (matchMode && questionType === "bonus")) {
                nextQuestion();
            }
            if (nAnswers === 1 && firstAnswer === "incorrect" ) {
                resetQuestion();
                disablePlayerBoxesTeam( firstAnswerTeam );
                answerTry = 2;
            }
        }
        var enterReviewMode = function() {
            mode = "review";
            var questNum = questionNumber * 2 - 1;
            if (questionType === "bonus" ) {
                questNum += 1;
            }
            var questInf = questionInformation[questionNum];
            document.getElementById("questionCorrectSelectorContainer").style.pointerEvents = "none";
            if (questInf.nAnswers === 0) {
                disablePlayerBoxes();
            }
            if (questInf.nAnswers === 1) {
                playerBoxClicked( "playerBox_" + questInf.firstAnswerTeam + questInf.firstAnswerPerson, questInf.firstAnswerTeam, questInf.firstAnswerPerson );
            }
            if (questInf.nAnswers === 2) {
                playerBoxClicked( "playerBox_" + questInf.secondAnswerTeam + questInf.secondAnswerPerson, questInf.secondAnswerTeam, questInf.secondAnswerPerson );
            }
        }
        var previousQuestion = function() {
            if  (questionNumber >= 1 && questionType === "toss-up") {
                questionNumber -= 1;
            }
            else if (questionType === "bonus") {
                questionType =  "toss-up";
            }
        }
        var previousQuestionButtonHandler = function() {
            if (nAnswers === 1) {
                nAnswers = 0;
                firstAnswerTeam = "none";
                firstAnswer = "none";
                firstAnswerPerson = "none";
                answerTry = 1;
                resetQuestion();
            }
            else if (nAnswers === 2) {
                nAnswers = 1;
                secondAnswerTeam = "none";
                secondAnswer = "none";
                secondAnswerPerson = "none";
                answerTry = 1;
                resetQuestion();
                disablePlayerBoxesTeam( firstAnswerTeam );
            }
        }
    </script>
    <script>
        var addToForm = function( form, name, value ) {
            var input = document.createElement('input');
        	input.type = "hidden";
        	input.name = name;
        	input.value = value;
        	form.appendChild(input);
        }
        var submitQuestionInfo = function() {
        	var form = document.getElementById("submitQuestInfoForm");
        	var qNum = 0;
        	var nQuests = questionInformation.length;
        	addToForm( form, "nQuests", nQuests );
        	while ( qNum < nQuests ) {
        		addToForm( form, "qN" + qNum, questionInformation[qNum].questionNumber );
        		addToForm( form, "qT" + qNum, questionInformation[qNum].questionType );
        		addToForm( form, "nA" + qNum, questionInformation[qNum].nAnswers );
        		
        		addToForm( form, "fAT" + qNum, questionInformation[qNum].firstAnswerTeam );
        		addToForm( form, "fAP" + qNum, questionInformation[qNum].firstAnswerPerson );
        		addToForm( form, "fA" + qNum, questionInformation[qNum].firstAnswer );
        		addToForm( form, "fQI" + qNum, questionInformation[qNum].firstQuestInterrupt );
               		
        		addToForm( form, "sAT" + qNum, questionInformation[qNum].secondAnswerTeam );
        		addToForm( form, "sAP" + qNum, questionInformation[qNum].secondAnswerPerson );
        		addToForm( form, "sA" + qNum, questionInformation[qNum].secondAnswer );
        		addToForm( form, "sQI" + qNum, questionInformation[qNum].secondQuestInterrupt );
        		qNum += 1;
        	}

            var player = 0;
            while (player < 8) {
                addToForm( form, "player" + player, players[player] );
                player++;
            }

            if (includeSpreadsheetYN) {
                createSpreadsheet();
            }
            else {
                form.submit();
            }
        }
        var openSubmitScreen = function() {
            document.getElementById("submitPanelContainer").style.display = "inherit";
        }
        var cancelSubmitScreen = function() {
            document.getElementById("submitPanelContainer").style.display = "none";
        }
        var includeSpreadsheetYN = false;
        var includeYNSpreadsheetHandler = function() {
            if (!includeSpreadsheetYN) {
                document.getElementById("includeYNSpreadsheet").style.display = "inherit";
                document.getElementById("includeSpreadYNButton").style.backgroundColor = "#ff9438";
                document.getElementById("includeSpreadYNButton").innerHTML = "Create Spreadsheet: Yes"
                document.getElementById("includeSpreadYNButton").style.color = "black";
                document.getElementById("inputSpreadName").value = mySpreadsheetName;
                includeSpreadsheetYN = true;
            }
            else {
                document.getElementById("includeYNSpreadsheet").style.display = "none";
                document.getElementById("includeSpreadYNButton").style.backgroundColor = "rgb(200,200,200)";
                document.getElementById("includeSpreadYNButton").innerHTML = "Create Spreadsheet: No"
                includeSpreadsheetYN = false;
            }
        }
    </script>
</head>
<body>
    <div id="gameRunContainer">
        <div>
            <table style="position: relative; left: 10%; width: 80%; table-layout: fixed;">
                <tr>
                    <td style="width: 75%;">
                        <h1 id="questionNumberHeading" style="position: relative; left: 0; text-align: left; width: 80%; margin: 0;">
                            Question 1: toss-up
                        </h1>
                    </td>
                    <td>
                    	<form id="submitQuestInfoForm" action="matchRecordSubmit.php" method="post">
                        	<input id="questInfoSubmitInput" type="hidden" value="" name="questInfoSubmitted">
                        </form>
                        <button style="background-color: rgb(0,199,0)" onclick="openSubmitScreen()">
                        	Submit
                        </button>
                    </td>
                </tr>
            </table>
        </div>
        <div id="gameRunPlayers">
            <table style="width: 100%; height: 100%; ">
                <tr style="width: 100%; height: 100%;">
                    <td class="playerBox">
                        <div class="playerBoxContainer"  id="playerBox_A4" onclick="playerBoxClicked('playerBox_A4','A',4)">
                            <img src="images/person.svg" style="display: block; width: 100%; height: 85%;">
                            <div class="playerBoxText"> <p> A4 </p> </div>
                        </div>
                    </td>
                    <td class="playerBox">
                        <div class="playerBoxContainer" id="playerBox_A3" onclick="playerBoxClicked('playerBox_A3','A',3)">
                            <img src="images/person.svg" style="display: block; width: 100%; height: 85%;">
                            <div class="playerBoxText"> <p> A3 </p> </div>
                        </div>
                    </td>
                    <td class="playerBox">
                        <div class="playerBoxContainer" id="playerBox_A2" onclick="playerBoxClicked('playerBox_A2','A',2)">
                            <img src="images/person.svg" style="display: block; width: 100%; height: 85%;">
                            <div class="playerBoxText"> <p>A<sub style="font-size: 30%">captain</sub> </p> </div>
                        </div>
                    </td>
                    <td class="playerBox">
                        <div class="playerBoxContainer" id="playerBox_A1" onclick="playerBoxClicked('playerBox_A1','A',1)">
                            <img src="images/person.svg" style="display: block; width: 100%; height: 85%;">
                            <div class="playerBoxText"> <p> A1 </p> </div>
                        </div>
                    </td>
                    <td class="playerBox">
                        <div class="playerBoxContainer" id="playerBox_B1" onclick="playerBoxClicked('playerBox_B1','B',1)">
                            <img src="images/person.svg" style="display: block; width: 100%; height: 85%;">
                            <div class="playerBoxText"> <p> B1 </p> </div>
                        </div>
                    </td>
                    <td class="playerBox">
                        <div class="playerBoxContainer" id="playerBox_B2" onclick="playerBoxClicked('playerBox_B2','B',2)" >
                            <img src="images/person.svg" style="display: block; width: 100%; height: 85%;">
                            <div class="playerBoxText"> <p>B<sub style="font-size: 30%">captain</sub> </p> </div>
                        </div>
                    </td>
                    <td class="playerBox">
                        <div class="playerBoxContainer" id="playerBox_B3" onclick="playerBoxClicked('playerBox_B3','B',3)" >
                            <img src="images/person.svg" style="display: block; width: 100%; height: 85%;">
                            <div class="playerBoxText"> <p> B3 </p> </div>
                        </div>
                    </td>
                    <td class="playerBox">
                        <div class="playerBoxContainer" id="playerBox_B4" onclick="playerBoxClicked('playerBox_B4','B',4)" >
                            <img src="images/person.svg" style="display: block; width: 100%; height: 85%;">
                            <div class="playerBoxText"> <p> B4 </p> </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div id="questionCorrectSelectorContainer" >
            <table style="position: relative; left: 10%; width: 80%; table-layout: fixed;">
                <tr>
                    <td>
                        <button id="correctButton" style="background-color: rgb(210, 225, 210);  font-size: 1.5em; color: gray;" onclick="correctButtonHandler()" >
                            Correct
                        </button>
                    </td>
                    <td>
                        <button id="incorrectButton" style="background-color: rgb(225, 210, 210); font-size: 1.5em; color: gray;" onclick="incorrectButtonHandler()" >
                            Incorrect
                        </button>
                    </td>
                    <td></td>
                    <td>
                        <button id="interruptButton" onclick="interruptButtonHandler('interruptButton')" style="color: gray;" >
                            Interrupt: No
                        </button>
                    </td>
                </tr>
            </table>
        </div>
        <div id="continueToNextQuestionContainer">
            <table style="position: relative; left: 10%; width: 80%; table-layout: fixed;">
                <tr>
                    <td>
                        <button id="previousQuestionButton" onclick="previousQuestionButtonHandler()" >
                            Back
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                        
                    <td>
                        <button id="nextQuestionButton" onclick="nextQuestionButtonHandler()">
                            No Answer
                        </button>
                    </td>
                </tr>
            </table>
        </div>
        <div id="displayQuestionInfo" style="position: relative; left: 10%; width: 80%; display: none;">
            <h2 id="displayQuestion">

            </h2>
            <h2 id="displayAnswerChoices" style="position: relative; left: 10%;">

            </h2>
            <h2 id="displayAnswer">

            </h2>
        </div>
    </div>
    <div class="coverSheet" id="startRoundPanelContainer">
        <div class="floatCoverPanel">
            <div class="coverPanel">
                <br style="line-height: 0px; margin: 0;">
                <h1 style="margin: 0;">
                    Round Setup
                </h1>
                <div class="formText">
                    Enter Players:
                </div>
                <form id="newRoundForm">
                    <input type="text" name="A1" placeholder="A Team Player 1" value="">
                    <input type="text" name="A2" placeholder="A Team Captain" value="">
                    <input type="text" name="A3" placeholder="A Team Player 3" value="">
                    <input type="text" name="A4" placeholder="A Team Player 4" value="">
                    <input type="text" name="B1" placeholder="B Team Player 1" value="">
                    <input type="text" name="B2" placeholder="B Team Captain" value="">
                    <input type="text" name="B3" placeholder="B Team Player 3" value="">
                    <input type="text" name="B4" placeholder="B Team Player 4" value="" style="margin-bottom: 20px;">
                    <label class="checkContainer">
                        Import Question Set Information (Optional):
                        <input type="checkbox" id="questSetInfoCheck" onclick="questSetCheckHandler();">
                        <span class="checkmark2"></span>
                    </label>
                    <div id="questSetInfoContainer" style="display: none;">
                        <input type="text" name="questionSet" placeholder="Sample Question Set #" value="">
                        <input type="text" name="roundNumber" placeholder="Round Number" value="">
                        <!-- <input type="text" name="pdfurl" placeholder="URL of PDF question set"> -->
                        <div id="questSetInfoText" class="formText"></div>
                        <button style="background-color: rgb(170,170,170); color: black;" type="button" onclick="importQuestionInfo();">
                            Import Round Data
                        </button>
                    </div>
                <div class="formText">
                    Settings:
                </div>
                    <label class="radioContainer">
                        Match Mode (Auto-skip bonuses)
                        <input type="radio" name="roundType" value="matchMode" checked>
                        <span class="checkmark"></span>
                    </label>
                    <label class="radioContainer">
                        Fun Mode (All questions open)
                        <input type="radio" name="roundType" value="funMode">
                        <span class="checkmark"></span>
                    </label>
                </form>
                <br>
                <button style="background-color: rgb(0,199,0); font-size: 1.5em" onclick="startNewRound()">
                    Start Round
                </button>
            </div>
        </div>
    </div>
    <div class="coverSheet" id="submitPanelContainer">
        <div class="floatCoverPanel">
            <div class="coverPanel">
                <br style="line-height: 0px; margin: 0;">
                <h1 style="margin: 0;">
                    Submit Round
                </h1>
                <br>
                <div class="formText">
                    Complete The Following Fields and Submit:
                </div>
                <button style="background-color: rgb(200,200,200)" onclick="includeYNSpreadsheetHandler()" id="includeSpreadYNButton">
                    Create Spreadsheet: No
                </button>
                <div id="includeYNSpreadsheet" style="display:none;">
                    <div class="formText" style="text-align: center">
                        To Verify With Your Google Account: 
                    </div>
                    <div class="formText" style="font-size: 1em;">
                        1. Sign in with your gmail username and password <br>
                        2. You will recieve a page saying "This app isn't verified." The following steps will override this; ask the site owner if you'd like more details <br>
                        3. Press "Advanced" in the lower left corner <br>
                        4. Press "Go to rf.gd (unsafe)" in the lower left corner <br>
                    </div>
                    <div class="formText">
                        Spreadsheet Name: 
                    </div>
                    <form>
                        <input type="text" name="spreadsheetName" id="inputSpreadName">
                    </form>
                </div>
                <br>
                <table style="width: 100%; table-layout: fixed;">
                    <tr>
                        <td>
                            <button style="background-color: rgb(255,0,0); color: white;" onclick="cancelSubmitScreen()">
                                Cancel
                            </button>
                        </td>
                        <td>
                            <button style="background-color: rgb(0,199,0); color: white;" onclick="submitQuestionInfo()">
                                Submit
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>