<?php
include 'php/config.php';

$add_type = $_GET['type'];

if ($add_type == "film") {

	echo '<h1>Add Film</h1>';
	echo '<form action="php/submit" method="POST">';
	echo '<input name="title" placeholder="Enter Title" />';
	echo '<input name="year" placholder="Year" />';
	echo '<input name="type" type="hidden" value="add_film" />';
	echo '<button type="submit">Submit</button>';
	echo '</form>';

} elseif ($add_type == "tag") {

	echo '<h1>Add Tag</h1>';
	echo '<form action="php/submit" method="POST">';
	echo '<input name="tag" placeholder="Enter Tag Name" />';
	echo '<input name="type" type="hidden" value="add_tag" />';
	echo '<button type="submit">Submit</button>';
	echo '</form>';
}

require_once 'php/footer.php';
?>
