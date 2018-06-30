<?php include "checkSID.php"; ?>

<script>
var mySpreadsheetName = "Match Record " + Date();

function makeApiCall() {
    var headerVals = {
        "values": [
        { "userEnteredValue": {
                "stringValue": "Number"
        }},
        { "userEnteredValue": {
                "stringValue": "Type"
        }},
        { "userEnteredValue": {
                "stringValue": "Category"
        }},
        { "userEnteredValue": {
                "stringValue": "Question Format"
        }},
        { "userEnteredValue": {
                "stringValue": "Person"
        }},
        { "userEnteredValue": {
                "stringValue": "Team"
        }},
        { "userEnteredValue": {
                "stringValue": "Player #"
        }},
        { "userEnteredValue": {
                "stringValue": "Correct"
        }},
        { "userEnteredValue": {
                "stringValue": "Interrupt"
        }},
        ]
    };
    var spreadData = [
        headerVals
    ];

    var qNum = 0;
    var nQuests = questionInformation.length;
    while ( qNum < nQuests ) {
        if ( questionInformation[qNum].nAnswers > 0 ) {
            var answerPerson = questionInformation[qNum].firstAnswerTeam;
            if (answerPerson === "A") {
                answerPerson = 0;
            }
            else {
                answerPerson = 4;
            }
            answerPerson += questionInformation[qNum].firstAnswerPerson - 1;
            var person = players[answerPerson];
            var interrupt = "";
            if (questionInformation[qNum].firstQuestInterrupt) {
                interrupt = "YES";
            }

            var category = "";
            var format = "";
            if (usingQSetInfo) {
                var qN = 2 * ( questionInformation[qNum].questionNumber - 1 );
                if ( questionInformation[qNum].questionType == "bonus" ) {
                    qN++;
                }
                if ( qN < questionSetInfo.length) {
                    category = questionSetInfo[qN][2];
                    format = questionSetInfo[qN][3];
                }
            }

            var rowData = {
                "values": [
                    { "userEnteredValue": {
                        "numberValue": questionInformation[qNum].questionNumber
                    }},
                    { "userEnteredValue": {
                        "stringValue": questionInformation[qNum].questionType
                    }},
                    { "userEnteredValue": {
                        "stringValue": category
                    }},
                    { "userEnteredValue": {
                        "stringValue": format
                    }},
                    { "userEnteredValue": {
                        "stringValue": person
                    }},
                    { "userEnteredValue": {
                        "stringValue": questionInformation[qNum].firstAnswerTeam
                    }},
                    { "userEnteredValue": {
                        "numberValue": questionInformation[qNum].firstAnswerPerson
                    }},
                    { "userEnteredValue": {
                        "stringValue": questionInformation[qNum].firstAnswer
                    }},
                    { "userEnteredValue": {
                        "stringValue": interrupt
                    }}
                ]
            };
            spreadData.push(rowData);
        }
        
        if ( questionInformation[qNum].nAnswers > 1 ) {
            var answerPerson = questionInformation[qNum].secondAnswerTeam;
            if (answerPerson === "A") {
                answerPerson = 0;
            }
            else {
                answerPerson = 4;
            }
            answerPerson += questionInformation[qNum].secondAnswerPerson - 1;
            var person = players[answerPerson];
            var interrupt = "";
            if (questionInformation[qNum].secondQuestInterrupt) {
                interrupt = "YES";
            }

            var category = "";
            var format = "";
            if (usingQSetInfo) {
                var qN = 2 * ( questionInformation[qNum].questionNumber - 1 );
                if ( questionInformation[qNum].questionType == "bonus" ) {
                    qN++;
                }
                if ( qN < questionSetInfo.length) {
                    category = questionSetInfo[qN][2];
                    format = questionSetInfo[qN][3];
                }
            }

            var rowData = {
                "values": [
                    { "userEnteredValue": {
                        "numberValue": questionInformation[qNum].questionNumber
                    }},
                    { "userEnteredValue": {
                        "stringValue": questionInformation[qNum].questionType
                    }},
                    { "userEnteredValue": {
                        "stringValue": category
                    }},
                    { "userEnteredValue": {
                        "stringValue": format
                    }},
                    { "userEnteredValue": {
                        "stringValue": person
                    }},
                    { "userEnteredValue": {
                        "stringValue": questionInformation[qNum].secondAnswerTeam
                    }},
                    { "userEnteredValue": {
                        "numberValue": questionInformation[qNum].secondAnswerPerson
                    }},
                    { "userEnteredValue": {
                        "stringValue": questionInformation[qNum].secondAnswer
                    }},
                    { "userEnteredValue": {
                        "stringValue": interrupt
                    }}
                ]
            };
            spreadData.push(rowData);
        }
        qNum += 1;
    }

    var spreadsheetBody = {
        // TODO: Add desired properties to the request body.
        "properties": {
            "title": mySpreadsheetName
        },
        "sheets": [
            {
                "data": [
                {
                    "startRow": 1,
                    "startColumn": 1,
                    "rowData": spreadData
                }
                ]
            }
        ]
    };

    var request = gapi.client.sheets.spreadsheets.create({}, spreadsheetBody);
    request.then(function(response) {
        // TODO: Change code below to process the `response` object:
        console.log(response.result);
    }, function(reason) {
        console.error('error: ' + reason.result.error.message);
    });

    handleSignOutClick();
}

function initClient() {
    var API_KEY = '[INSERT API KEY HERE]';  // TODO: Update placeholder with desired API key.

    var CLIENT_ID = '[INSERT CLIENT ID HERE]';  // TODO: Update placeholder with desired client ID.

    var SCOPE = 'https://www.googleapis.com/auth/spreadsheets';

    gapi.client.init({
        'apiKey': API_KEY,
        'clientId': CLIENT_ID,
        'scope': SCOPE,
        'discoveryDocs': ['https://sheets.googleapis.com/$discovery/rest?version=v4'],
    }).then(function() {
        gapi.auth2.getAuthInstance().isSignedIn.listen(updateSignInStatus);
        updateSignInStatus(gapi.auth2.getAuthInstance().isSignedIn.get());
    });
}

function handleClientLoad() {
    gapi.load('client:auth2', initClient);
}

function updateSignInStatus(isSignedIn) {
    if (isSignedIn) {
    makeApiCall();
    var form = document.getElementById("submitQuestInfoForm");
    form.submit();
    }
}

function createSpreadsheet(event) {
    gapi.auth2.getAuthInstance().signIn();
}

function handleSignOutClick(event) {
    gapi.auth2.getAuthInstance().signOut();
}
</script>
<script async defer src="https://apis.google.com/js/api.js"
    onload="this.onload=function(){};handleClientLoad()"
    onreadystatechange="if (this.readyState === 'complete') this.onload()">
</script>
