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
while ($rows = $select_films->fetch(PDO::FETCH_ASSOC)) {
	$title = $rows["Name"];
	$filmyear = $rows["Year"];
}

# getting existing tags for this filmid
$select_tags_sql = '	SELECT 	t.TagName, t.TagID 
			FROM 	tblFilmTags AS ft LEFT JOIN
				tbll_Tags AS t on t.TagID = ft.TagID
			WHERE 	(ft.FilmID = :filmid) AND (ft.RecordStatusID = 1)';
$select_tags = $db->prepare($select_tags_sql);
$select_tags->execute(array(':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));

$tags = [];
$tag_ids = [];
while ($rows = $select_tags->fetch(PDO::FETCH_ASSOC)) {
	array_push($tags, $rows["TagName"]);
	array_push($tag_ids, $rows["TagID"]);
}
	
echo '<h1>' . $title . ' - ' . $filmyear . '</h1>';
echo '<h2>Tags</h2>';
echo '<ul>';

# iterating through tags and populating list
for ($i = 0; $i < count($tags); $i++) {
	echo '<li><a href="tags?id=' . $tag_ids[$i] . '">' . $tags[$i] . '</a></li>';
}
echo '</ul>';

# Getting reviews
$select_reviews_sql = '	SELECT 	r.Rating, u.UserID, u.FirstName, u.LastName
			FROM 	tblRatings AS r LEFT JOIN 
				tblUsers AS u ON u.UserID = r.UserID
			WHERE 	(r.FilmID = :filmid) AND ((u.RecordStatusID = 1) AND (r.RecordStatusID = 1))';
$select_reviews = $db->prepare($select_reviews_sql);
$select_reviews->execute(array(':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));

echo '<h2>Ratings</h2>';
echo '<table>';
while ($rows = $select_reviews->fetch(PDO::FETCH_ASSOC)) {
	$userid = $rows["UserID"];
	$firstname = $rows["FirstName"];
	$lastname = $rows["LastName"];
	$rating = $rows["Rating"];
	
	echo '	<tr>
			<td><a href="users?id=' . $userid . '">' . $firstname . ' ' . $lastname . '</a></td>
			<td>' . $rating . '</td>
		</tr>
	';
}
echo '</table>';

echo '<h3><a href="rate?id=' . $filmid . '">Rate this film</a></h3>';
?>
</body>
</html>
