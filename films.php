<?php
include 'php/config.php';

if (isset($_GET['id'])) {

$filmid = $_GET['id'];

# Getting film metadata
$select_films_sql = '	SELECT 	f.Name, f.Year, t.TagName, t.TagID, f.CreatedDT 
			FROM 	tblFilms AS f LEFT JOIN 
				tblFilmTags AS ft ON ft.FilmID = f.FilmID LEFT JOIN
				tbll_Tags AS t on t.TagID = ft.TagID
			WHERE 	(f.FilmID = :filmid)';
$select_films = $db->prepare($select_films_sql);
$select_films->execute(array(':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));
while ($rows = $select_films->fetch(PDO::FETCH_ASSOC)) {
	$title = $rows["Name"];
	$filmyear = $rows["Year"];
	$ugly_date = $rows["CreatedDT"];
}

# formatting date to not suck
$bad_date = explode(' ',$ugly_date);
$broken_date = explode('-', $bad_date[0]);
$createddt = $broken_date[1] . '/' . $broken_date[2] . '/' . $broken_date[0];


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
	
# Getting film metadata
$select_ratings_sql = '	SELECT 	Rating 
			FROM 	tblRatings 
			WHERE 	(FilmID = :filmid) AND (RecordStatusID = 1)';
$select_ratings = $db->prepare($select_ratings_sql);
$select_ratings->execute(array(':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));
$rating_counter = 0;
$rating_sum = 0;
while ($rows = $select_ratings->fetch(PDO::FETCH_ASSOC)) {
	$rating_sum += $rows["Rating"];	
	$rating_counter ++;
}

echo '<div class="row">
	<div class="col-sm-6">
		<div class="panel panel-primary">
			<div class="panel-heading">
			<h1 class="panel-title">' . $title  . ' - ' . $filmyear . '</h1>
			</div>
		<div class="panel-body">
			<table>
				<tr><td>Date Added</td><td># Reviews</td><td>Avg. Rating</td></tr>
				<tr>	
					<td>' . $createddt . '</td>
					<td>' . $rating_counter . '</td>
					<td><strong>' . round(($rating_sum / $rating_counter), 2) . '</strong></td>
				</tr>
			</table>';


# buttons row
echo '<div class="row">
	<div class="col-sm-6">';
# rating button
echo '<div class="centered review_button">
	<a href="rate?id=' . $filmid . '"><button class="btn btn-primary btn-lg">Rate this film</button></a>
</div> </div>';
# edit button
echo '<div class="col-sm-6">
	<div class="centered review_button">
	<a href="edit?id=' . $filmid . '"><button class="btn btn-primary btn-lg">Edit this film</button></a>
</div></div>';

# ending row
echo '</div>';



# ending user stats box
echo '		</div>
		</div>';



echo'		<div class="panel panel-primary">
			<div class="panel-heading">
			<h1 class="panel-title">Tags</h1>
			</div>
		<div class="panel-body">
			<table class="film_tags_table">';	

				# iterating through tags and populating list
				for ($i = 0; $i < count($tags); $i++) {
					echo '<tr><td><a href="tags?id=' . $tag_ids[$i] . '">' . $tags[$i] . '</a></td></tr>';
				}
echo'			</table>';
echo '		</ul>';
# ending tags box
echo '		</div>
		</div>';

# closing column
echo '	</div>';



echo '<div class="col-sm-6">
		<div class="panel panel-primary">
			<div class="panel-heading">
			<h1 class="panel-title">All Ratings</h1>
			</div>
		<div class="panel-body">
			<table>
				<tr><td>User</td><td>Rating</td></tr>';

# Getting reviews
$select_reviews_sql = '	SELECT 	r.Rating, u.UserID, u.FirstName, u.LastName
			FROM 	tblRatings AS r LEFT JOIN 
				tblUsers AS u ON u.UserID = r.UserID
			WHERE 	(r.FilmID = :filmid) AND ((u.RecordStatusID = 1) AND (r.RecordStatusID = 1))';
$select_reviews = $db->prepare($select_reviews_sql);
$select_reviews->execute(array(':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));

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

# closing panel body and panel div
echo '</div></div>';
# closing column and row
echo '</div></div>';

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
