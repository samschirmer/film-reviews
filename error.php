<html>

<head>
	<title>This is a demo</title>
	<link rel="stylesheet" href="style.css" />
</head>

<body>

<?php
include 'php/config.php';

$error_type = $_GET["type"];

if ($error_type == "dupe_film") {
	$filmid = $_GET["id"];
	
	echo '<h1>This is a duplicate film name. Click <a href="films?id=' . $filmid . '">here</a> to view existing entry.</h1>';

} elseif ($error_type == "missing") {
	
	echo '<h1>This page don\'t exist.</h1>';
} else {
	echo '<h1>Something went wrong.</h1>';
}
?>

</body>
</html>
