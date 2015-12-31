<?php
include 'php/config.php';

if (isset($_GET['id'])) {

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
}

#####################################
#
#  Default; no filmid set
#
#####################################
else {

echo '<div class="row">
	<div class="col-xl-12">
	<table>
	<tr><td>Film Title</td><td>Year</td><td>Avg. Rating</td><td>Action</td></tr>';
# Getting film metadata
$select_films_sql = '	SELECT DISTINCT	FilmID, Name, Year
			FROM 		tblFilms
			WHERE		(RecordStatusID = 1)
			';
$select_films = $db->prepare($select_films_sql);
$select_films->execute() or die(print_r($db->errorInfo(), true));
while ($rows = $select_films->fetch(PDO::FETCH_ASSOC)) {
	$title = $rows["Name"];
	$filmyear = $rows["Year"];
	$filmid = $rows["FilmID"];

	$select_avg_sql = '	SELECT 	AVG(Rating) AS Average
				FROM 	tblRatings 
				WHERE	(FilmID = :filmid) AND (RecordStatusID = 1)
				';
	$select_avg = $db->prepare($select_avg_sql);
	$select_avg->execute(array(':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));
	while ($avg = $select_avg->fetch(PDO::FETCH_ASSOC)) {
	$average = round($avg["Average"], 2);
	}

	echo '<tr>
			<td><a href="films?id=' . $filmid . '">' . $title . '</a></td>
			<td>' . $filmyear . '</td>
			<td>' . $average . '</td>
			<td><a href="rate?id=' . $filmid . '">Rate</a></td>
		</tr>';
}
echo '</table>
</div></div>';
}


require_once 'php/footer.php';

?>
