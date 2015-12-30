<html>

<head>
	<title>This is a demo</title>
	<link rel="stylesheet" href="style.css" />
</head>

<body>

<?php
include 'php/config.php';

$filmid = $_GET['id'];

# Getting film metadata
$select_films_sql = '	SELECT 	f.Name, f.Year, t.TagName, t.TagID 
			FROM 	tblFilms AS f LEFT JOIN 
				tblFilmTags AS ft ON ft.FilmID = f.FilmID LEFT JOIN
				tbll_Tags AS t on t.TagID = ft.TagID
			WHERE 	(f.FilmID = :filmid)';
$select_films = $db->prepare($select_films_sql);
$select_films->execute(array(':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));

$tags = [];
$tag_ids = [];
while ($rows = $select_films->fetch(PDO::FETCH_ASSOC)) {
	$title = $rows["Name"];
	$filmyear = $rows["Year"];
	array_push($tags, $rows["TagName"]);
	array_push($tag_ids, $rows["TagID"]);
}
	
echo '<h1>' . $title . ' - ' . $filmyear . '</h1>';

# Getting users
$select_users_sql = '	SELECT 	u.UserID, u.FirstName, u.LastName
			FROM 	tblUsers AS u  
			WHERE 	(u.RecordStatusID = 1)
			ORDER BY u.UserID';
$select_users = $db->prepare($select_users_sql);
$select_users->execute() or die(print_r($db->errorInfo(), true));

echo '
<table>';
$firstnames = [];
$lastnames = [];
$userids = [];
while ($rows = $select_users->fetch(PDO::FETCH_ASSOC)) {
	array_push($userids, $rows["UserID"]);
	array_push($firstnames, $rows["FirstName"]);
	array_push($lastnames, $rows["LastName"]);
}	

# Getting reviews
$select_reviews_sql = '	SELECT 	u.UserID, r.Rating
			FROM 	tblRatings AS r LEFT JOIN
				tblUsers AS u ON u.UserID = r.UserID  
			WHERE 	(r.FilmID = :filmid) AND
				((u.RecordStatusID = 1) AND (r.RecordStatusID = 1))';
$select_reviews = $db->prepare($select_reviews_sql);
$select_reviews->execute(array(':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));

$rating_userids = [];
$ratings = [];
while ($rows = $select_reviews->fetch(PDO::FETCH_ASSOC)) {
	$ratings[$rows["UserID"]] = $rows["Rating"];
}

echo '
<form action="submit.php" method="POST">';

$last_id = 0;
for ($i = 0; $i < count($userids); $i++) {
	echo '<tr>';
	echo '
	<td><a href="users?id=' . $userids[$i] . '">' . $firstnames[$i] . ' ' . $lastnames[$i] . '</a></td>';

	# input for form
	if (isset($ratings[$userids[$i]])) {
		echo '
		<td>
		<select name="' . $userids[$i] . '">
			<option selected value="' . $ratings[$userids[$i]] . '">' . $ratings[$userids[$i]] . '</option>
			<option value="">Delete</option>
			<option value="0">0</option>
			<option value="0.5">0.5</option>
			<option value="1">1</option>
			<option value="1.5">1.5</option>
			<option value="2">2</option>
			<option value="2.5">2.5</option>
			<option value="3">3</option>
			<option value="3.5">3.5</option>
			<option value="4">4</option>
			<option value="4.5">4.5</option>
			<option value="5">5</option>
		</select>
		</td>';
		echo '</tr>';
	} else {
		echo '
		<td>
		<select name="' . $userids[$i] . '">
			<option selected value=""></option>
			<option value="0">0</option>
			<option value="0.5">0.5</option>
			<option value="1">1</option>
			<option value="1.5">1.5</option>
			<option value="2">2</option>
			<option value="2.5">2.5</option>
			<option value="3">3</option>
			<option value="3.5">3.5</option>
			<option value="4">4</option>
			<option value="4.5">4.5</option>
			<option value="5">5</option>
		</select>
		</td>';
		echo '</tr>';
	}

	if ($userids[$i] > $last_id) {
		$last_id = $userids[$i];
	}
}
echo '
</table>';
echo '
<input type="hidden" name="submit_type" value="edit_rating" />
<input type="hidden" name="filmid" value="' . $filmid . '" />
<input type="hidden" name="last_id" value="' . $last_id . '" />
';
echo '<button type="submit">Submit</button>
	</form>';
?>
</body>
</html>
