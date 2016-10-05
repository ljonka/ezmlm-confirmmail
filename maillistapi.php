<?php
/* make subscribe / unsubscribe functions with mailconfirmation available for user 
 * ezmlm server must be on same server (localhost)
*/

/*
 * This should be changed depending on your server config
 */
$strDefaultMailSuffix = "@transition-regensburg.de";
$strConfirmLink = "https://zettel.apus.uberspace.de/maillistapi.php?type=confirm&hash=%s";
$strListPath = "/home/zettel/mailinglisten/%s"; //this is realy server dependend
$strConfirmDBPath = "/home/zettel/workspace/leon/mailinglistenconfirm.db"; //should be outside of webroot for security reason
$strMailSubject = "Anfrage für Mailingliste bestätigen";

$strMailSubMessage = "Hallo,

dies ist eine Sicherheitsueberpruefung zum abonnieren der Mailingsliste '%s', zum Bestaetigen gehe bitte auf den folgenden Link:

%s

Nach der Bestaetigung kannst du Mails an die Liste verschicken.
";

$strMailUnsubMessage = "Hallo,

dies ist eine Sicherheitsueberpruefung zur Abbestellung der Mailingliste '%s', zur Bestaetigung gehe bitte auf den folgenden Link: 

%s
";

$arrLists = array(
        'bikesharing-alle',
        'kuefa-alle', 
        'repaircafe-alle', 
        'technologie-alle',
        'feste-ladenhelfer'=>'feste-ladenhelfer@wechselwelt.org',  
        'oase-alle',  
        'solawir-liste'=>'liste@solawir.de', 
        'test-texte',
        'food-coop-alle', 
        'oeffentlichkeit',  
        'strassenfest',          
        'upcycling',
        'gardening-alle',
        'organisation',
        'strassenfest-teilnehmer',
        'wechselwelt-alle',
        'herz-seele-alle',  
        'regio-team-alle',
        'technolgie-alle'
);

/*
 * End of individual config, the rest should work as is in most cases
 */


$strCommandSubscribe = "ezmlm-sub %s %s";
$strCommandUnsubscribe = "ezmlm-unsub %s %s";
$strTableName =  "confirm";
$strCreateTable = sprintf( "
	CREATE TABLE IF NOT EXISTS %s ( 
		request_date TEXT DEFAULT CURRENT_TIMESTAMP, 
		confirm_date TEXT DEFAULT NULL, 
		type TEXT NOT NULL,
		mail TEXT NOT NULL, 
		list TEXT NOT NULL, 
		hash TEXT NOT NULL
	)", $strTableName );
$strInsertTable = "INSERT INTO %s (type, mail, list, hash) VALUES ('%s', '%s', '%s', '%s')";
$strSelectTable = "SELECT * FROM %s WHERE hash = '%s'";
$strUpdateTable = "UPDATE %s SET confirm_date = '%s' WHERE hash = '%s'";

$db = new SQLite3( $strConfirmDBPath );
$db->exec( $strCreateTable );

//get request type: subscribe / unsubscribe / confirm
$strType = (isset($_GET["type"])) ? htmlspecialchars($_GET["type"]) : null;
$strMail = (isset($_GET["mail"]))?htmlspecialchars($_GET["mail"]):null;
$strList = (isset($_GET["list"]))?htmlspecialchars($_GET["list"]):null;
$strHash = (isset($_GET["hash"]))?htmlspecialchars($_GET["hash"]):null;

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

if($strType == "subscribe"){
	$strHash = generateRandomString();
	$db->exec(sprintf($strInsertTable, $strTableName, $strType, $strMail, $strList, $strHash));
	$strListMail = (isset($arrLists[$strList])) ? $arrLists[$strList] : $strList . $strDefaultMailSuffix;
	mail($strMail, $strMailSubject, sprintf($strMailSubMessage, $strListMail, sprintf($strConfirmLink, $strHash)));	
	echo "Confirmation mail send";
}
elseif($strType == "unsubscribe"){
	$strHash = generateRandomString();
        $db->exec(sprintf($strInsertTable, $strTableName, $strType, $strMail, $strList, $strHash));
        mail($strMail, $strMailSubject, sprintf($strMailUnsubMessage, $strList, sprintf($strConfirmLink, $strHash)));
	echo "Confirmation mail send";
}
else if($strType == "confirm" && $strHash != null && strlen($strHash) >= 6){
	$results = $db->query(sprintf($strSelectTable, $strTableName, $strHash));
	$iRows = 0;
	while ($row = $results->fetchArray()) {
    		if($row['confirm_date'] == ""){
			$db->exec(sprintf($strUpdateTable, $strTableName, date('c'), $strHash));
			if($row["type"] == "subscribe"){
				exec(sprintf($strCommandSubscribe, sprintf($strListPath, $row["list"]), $row["mail"]));
				echo "Subscribed";
			}else if($row["type"] == "unsubscribe"){
				exec(sprintf($strCommandUnsubscribe, sprintf($strListPath, $row["list"]), $row["mail"]));
                                echo "Unsubscribed";
			}
		}else{
			echo "Your request was already confirmed.";
		}
		$iRows++;
	}
	if($iRows == 0){
		echo "Wrong hash.";
	}
}
