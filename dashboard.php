<?php
include 'php/config.php';

# Query 
$select_films_sql = '	SELECT 	f.*, AVG(r.Rating) AS Average 
			FROM 	tblFilms AS f LEFT JOIN 
				tblRatings AS r ON r.FilmID = f.FilmID 
			WHERE 	(f.RecordStatusID = 1) and (r.RatingID IS NOT NULL)
			GROUP BY r.FilmID
			ORDER BY Average DESC
			LIMIT 10';
$select_films = $db->prepare($select_films_sql);
$select_films->execute() or die(print_r($db->errorInfo(), true));

echo '<div class="row">
	<div class="col-sm-8">
		<div class="centered col-sm-12">
		<img class="img-rounded img-responsive db-img" src="http://imgur.com/tWfRpki.jpg" />
		</div>
	</div> <!-- closing main body -->
	
	<!-- sidebar -->
	<div class="col-md-4">
';

echo '<div class="col-md-12">
	<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title">Highest Rated Films</h3>
	</div>
	<div class="panel-body">
	<table class="film_leaderboard">
		<tr>
			<td>Film</td><td>Year</td><td>Rating</td>
		</tr>';

$i = 0;
while ($rows = $select_films->fetch(PDO::FETCH_ASSOC)) {
	$title = $rows["Name"];
	$filmid = $rows["FilmID"];
	$filmyear = $rows["Year"];
	$average = $rows["Average"];
	
	echo '	<tr>
			<td><a href="films?id=' . $filmid . '">' . $title . '</a></td>
			<td>' . $filmyear . '</td>
			<td>' . round($average, 2) . '</td>
		</tr>
	';
}

echo '</table>
</div></div></div>';


echo '<div class="col-md-12">
	<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title">Most Active Reviewers</h3>
	</div>
	<div class="panel-body">
	<table class="film_leaderboard">
		<tr>
			<td>User</td><td>Reviews</td><td>Rating</td>
		</tr>';

$select_users_sql = '	SELECT 	u.*, AVG(r.Rating) AS Average, COUNT(r.Rating) AS Total 
			FROM 	tblUsers AS u LEFT JOIN 
				tblRatings AS r ON u.UserID = r.UserID 
			WHERE 	(u.RecordStatusID = 1) and (r.RecordStatusID = 1)
			GROUP BY r.UserID
			ORDER BY Total DESC
			LIMIT 10';
$select_users = $db->prepare($select_users_sql);
$select_users->execute() or die(print_r($db->errorInfo(), true));

while ($rows = $select_users->fetch(PDO::FETCH_ASSOC)) {
	$firstname = $rows["FirstName"];
	$lastname = $rows["LastName"];
	$average = $rows["Average"];
	$total = $rows["Total"];
	$userid = $rows["UserID"];
	
	echo '	<tr>
			<td><a href="users?id=' . $userid . '">' . $firstname . ' ' . $lastname . '</a></td>
			<td>' . $total . '</td>
			<td>' . round($average, 2) . '</td>
		</tr>
	';
}

echo '</table>
</div></div></div>';

echo '<div class="col-md-12">
	<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title">Highest Average Rating</h3>
	</div>
	<div class="panel-body">
	<table class="film_leaderboard">
		<tr>
			<td>User</td><td>Reviews</td><td>Rating</td>
		</tr>';

$select_users_sql = '	SELECT 	u.*, AVG(r.Rating) AS Average, COUNT(r.Rating) AS Total 
			FROM 	tblUsers AS u LEFT JOIN 
				tblRatings AS r ON u.UserID = r.UserID 
			WHERE 	(u.RecordStatusID = 1) AND (r.RecordStatusID = 1)
			GROUP BY r.UserID
			ORDER BY Average DESC
			LIMIT 10';
$select_users = $db->prepare($select_users_sql);
$select_users->execute() or die(print_r($db->errorInfo(), true));

while ($rows = $select_users->fetch(PDO::FETCH_ASSOC)) {
	$firstname = $rows["FirstName"];
	$lastname = $rows["LastName"];
	$average = $rows["Average"];
	$total = $rows["Total"];
	$userid = $rows["UserID"];
	
	echo '	<tr>
			<td><a href="users?id=' . $userid . '">' . $firstname . ' ' . $lastname . '</a></td>
			<td>' . $total . '</td>
			<td>' . round($average, 2) . '</td>
		</tr>
	';
}

echo '</table>
	</div></div></div>';



# ending sidebar html
echo '</div>';

require_once 'php/footer.php';
?>
