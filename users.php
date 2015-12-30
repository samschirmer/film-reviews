<html>

<head>
	<title>This is a demo</title>
	<link rel="stylesheet" href="style.css" />
</head>

<body>

<?php
include 'php/config.php';

$userid = $_GET['id'];

# Getting user metadata
$select_user_sql = '	SELECT 	u.FirstName, u.LastName, t.TagName 
			FROM 	tblUsers AS u LEFT JOIN 
				tblUserTags AS ut ON ut.UserID = u.UserID LEFT JOIN
				tbll_Tags AS t on t.TagID = ut.TagID
			WHERE 	(u.UserID = :userid)';
$select_user = $db->prepare($select_user_sql);
$select_user->execute(array(':userid' => $userid)) or die(print_r($db->errorInfo(), true));

echo '<table>';
while ($rows = $select_user->fetch(PDO::FETCH_ASSOC)) {
	$firstname = $rows["FirstName"];
	$lastname = $rows["LastName"];
	$tags = $rows["TagName"];

	echo '	<tr>
			<td>' . $firstname . '</td>
			<td>' . $lastname . '</td>
			<td>' . $tags . '</td>
		</tr>
	';
}
echo '</table>';


# Getting reviews
$select_reviews_sql = '	SELECT 	r.Rating, f.FilmID, f.Name, f.Year 
			FROM 	tblRatings AS r LEFT JOIN 
				tblFilms AS f ON f.FilmID = r.FilmID
			WHERE 	(r.UserID = :userid) AND ((f.RecordStatusID = 1) AND (r.RecordStatusID = 1))';
$select_reviews = $db->prepare($select_reviews_sql);
$select_reviews->execute(array(':userid' => $userid)) or die(print_r($db->errorInfo(), true));

echo '<table>';
while ($rows = $select_reviews->fetch(PDO::FETCH_ASSOC)) {
	$filmid = $rows["FilmID"];
	$title = $rows["Name"];
	$filmyear = $rows["Year"];
	$rating = $rows["Rating"];
	
	echo '	<tr>
			<td><a href="films?id=' . $filmid . '">' . $title . '</a></td>
			<td>' . $filmyear . '</td>
			<td>' . $rating . '</td>
		</tr>
	';
}
echo '</table>';
?>
</body>
</html>
