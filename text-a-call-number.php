<?php
session_start();
$referer = $_SERVER['HTTP_REFERER'];
$_SESSION['tacn'] = $referer;
?>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" integrity="sha384-xrRywqdh3PHs8keKZN+8zzc5TX0GRTLCcmivcbNJWm2rs5C8PRhcEn3czEjhAO9o" crossorigin="anonymous"></script>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>
<link rel="icon" 
      type="image/png" 
      href="images/favicon.png">
<title>Text a Call Number</title>
</head>
<?php 

// Set PHP Variables
$referer = $_SERVER['HTTP_REFERER'];
$pagetitle = "MUSCAT Plus: Text a Call Number"; // Page Title

// Include file with functions to query Alma API
require("alma-api-for-sms.php");

?>
<!-- Begin Page Editing Here -->
<div class="header" style="background: #002f6c;">
	<img style="padding: 10px;" style="width: 10%;" alt="Musselman Library" src="images/logo.png"/>
</div>
<div class="container">
<h1>MUSCAT Plus: Text a Call Number</h1>

<?php

// Get info posted from catalog
//$author = trim(htmlspecialchars($_GET["author"]));
$title = trim(htmlspecialchars($_GET["title"]));
$mms = trim(htmlspecialchars($_GET["mms"]));

echo '<form name="sms" method="get" action="send-sms.php" onsubmit="return CheckForm(this)">';

echo '<h2>Title</h2>';

// Remove semicolons from titles
echo '<p>' . $title . '</p>';

echo '<h2>Select Item Location</h2>';

// Create a list of holdings and ask user to choose
//--- Use mms to get info from Alma API ---//

// Get holdings info in XML format, and display choices for locations
if ($mms) {
	$xml = getAvailabilityInfo($mms);
	$bib = simplexml_load_string($xml);
	$locations = listAvailability($bib);
}

echo $locations . '<br />';

echo '<h3>Mobile Phone Number</h3>';

//
echo '<input type="text" name="number" size="15" id="number" /></p>';

echo '<p>';
echo '<input type="hidden" name="title" value="' . $title . '" />';
echo '<input type="hidden" name="mms" value="' . $mms . '" />';
//echo '<input type="submit" name="submit" value=" Send Message " id="inputFocusTarget" autofocus/><label for "submit"> <em>Standard messaging rates apply.</em></label></p></form>';
echo '<input type="submit" name="submit" value=" Send " id="inputFocusTarget" autofocus/></p></form>';
echo '<p>Standard messaging rates apply.</p>';
//echo '<p><a href="' . $referer . '">Return to MUSCAT Plus without texting the call number</a></p>';
echo '<p><a href="JavaScript:window.close()">Return to MUSCAT Plus without texting the call number</a></p>';

?>

</div>
</div>
</div>
</body>
</html>

<script language="javascript" type="text/javascript">

jQuery(document).ready(function() {
    console.log("ready!");
	jQuery("#email").hide();
});

// For Debugging
/*
var holdings = document.forms["sms"]["holdings"];
console.log("holdings: " + holdings);
var holdingsType = holdings.toString();
console.log("type: " + holdingsType);

if (holdingsType == '[object RadioNodeList]') {
	console.log("There is more than one choice");	
} else if (holdingsType == '[object HTMLInputElement]') {
	console.log("There is only one choice");	
	console.log("The choice is checked: " + holdings.checked);
} else {
	console.log("Something went wrong");	
}
*/

// Make the user choose a location
function validateRadio(radios) {
	
	var radiosType = radios.toString();
	console.log("type: " + radiosType);
	
	// There are multiple locations
	if (radiosType == '[object RadioNodeList]') {
		var length = radios.length;
		console.log('loop length: ' + length + '<br />');
		for (i = 0; i < length; ++ i) {
			console.log('loop length: ' + length + '<br />');
			console.log('trip through loop: ' + i + '<br />');
			if (radios [i].checked) return true;
			//break;
		}
	// There is only one location
	} else if (radiosType == '[object HTMLInputElement]') {
		if (radios.checked) {
			return true;	
		}
	}
	// No location chosen
    return false;
}


// Make sure all necessary fields are selected
function CheckForm(theForm) {
		
	var phone = theForm.number.value;
	var noMatch = !/^\d{10}$/.test(phone);
	var radio = validateRadio(document.forms["sms"]["holdings"]);
	console.log('radio ' + radio);
		
	if(noMatch) {
		alert('Please enter a valid phone number');
		theForm.number.focus();
		return false;
	}
	
	if(!radio) {
		alert('Please choose a location.'); 
		return false;
	}
	
	return true;
}


</script>


<!-- End Page Editing Here -->
