<html>

<head>
	<title>This is a demo</title>
	<link rel="stylesheet" href="style.css" />
</head>

<body>

<?php
include 'php/config.php';

$tagid = $_GET['id'];
$usercount = 0;

##### QUERY #####
# Getting tag name and the number of non-deleted films in this category
# Setting values to $tagname and $num_films
$select_tagname_sql = '	SELECT 	t.TagName, COUNT(f.FilmID) AS NumFilmID
			FROM 	tbll_Tags AS t LEFT JOIN 
				tblFilmTags AS ft ON ft.TagID = t.TagID LEFT JOIN
				tblFilms AS f ON f.FilmID = ft.FilmID
			WHERE 	(t.TagID = :tagid) AND (f.RecordStatusID = 1)';
$select_tagname = $db->prepare($select_tagname_sql);
$select_tagname->execute(array(':tagid' => $tagid)) or die(print_r($db->errorInfo(), true));
	while ($rows = $select_tagname->fetch(PDO::FETCH_ASSOC)) {
		$tagname = $rows["TagName"];
		$num_films = $rows["NumFilmID"];
}

##### QUERY #####
# Getting number of users that have rated this tag's films
# Dumping to $num_users
$select_num_user_sql = 'SELECT 	DISTINCT u.UserID, u.FirstName, u.LastName 
			FROM 	tblUsers AS u LEFT JOIN
				tblRatings AS r ON r.UserID = u.UserID LEFT JOIN
				tblFilms AS f ON f.FilmID = r.FilmID LEFT JOIN
				tblFilmTags AS ft ON ft.FilmID = f.FilmID
			WHERE 	(ft.TagID = :tagid) AND 
				((u.RecordStatusID = 1) AND (r.RecordStatusID = 1) AND (f.RecordStatusID = 1) AND (ft.RecordStatusID = 1))
			ORDER BY u.UserID';
$select_num_user = $db->prepare($select_num_user_sql);
$select_num_user->execute(array(':tagid' => $tagid)) or die(print_r($db->errorInfo(), true));

$num_users = 0;
while ($rows = $select_num_user->fetch(PDO::FETCH_ASSOC)) {
	$userids[$num_users] = array(
		"userid" => $rows["UserID"],
		"firstname" => $rows["FirstName"],
		"lastname" => $rows["LastName"]
	);
	$num_users ++;
}

# Generating the actual table
# First, the headers...
# !!! IMPORTANT !!! Column-number-to-userid associations are stored in $column_order[$col_number]. 
#	IT'S FUCKING ZERO-INDEXED!
$column_order = [];
echo '<table class="tagtable"><tr><td>' . $tagname . '</td>';
foreach ($userids as $user) {
	echo '<td><a href="users?id=' . $user["userid"] . '">' . $user["firstname"]/* . " " . $user["lastname"]*/ . '</a></td>';
	array_push($column_order, $user["userid"]);
}

echo '<td>Average</td></tr>';

##### QUERY #####
# Getting user metadata, ratings, and filmid/title
$select_rows_sql = '	SELECT 	u.UserID, r.Rating, f.FilmID, f.Name
			FROM 	tblUsers AS u LEFT JOIN
				tblRatings AS r ON r.UserID = u.UserID LEFT JOIN
				tblFilms AS f ON f.FilmID = r.FilmID LEFT JOIN
				tblFilmTags AS ft ON ft.FilmID = f.FilmID
			WHERE 	(ft.TagID = :tagid) AND
				((u.RecordStatusID = 1) AND (r.RecordStatusID = 1) AND (f.RecordStatusID = 1) AND (ft.RecordStatusID = 1))
			ORDER BY f.FilmID';
$select_rows = $db->prepare($select_rows_sql);
$select_rows->execute(array(':tagid' => $tagid)) or die(print_r($db->errorInfo(), true));

# Continuing to build table
# This time, populating the movie titles and ratings
$ratings_counter = 0;
while ($rows = $select_rows->fetch(PDO::FETCH_ASSOC)) {
	$data[$ratings_counter] = array(
			"userid" => $rows["UserID"],
			"rating" => $rows["Rating"],
			"title" => $rows["Name"],
			"filmid" => $rows["FilmID"]
			);
	$ratings_counter ++;
}

# Pulling orphan films (no ratings)
$select_orphans_sql = '	SELECT 	f.FilmID, f.Name
			FROM	tblFilms AS f LEFT JOIN
				tblRatings AS r ON r.FilmID = f.FilmID LEFT JOIN
				tblFilmTags AS ft ON ft.FilmID = f.FilmID
			WHERE 	(ft.TagID = :tagid) AND (r.RatingID IS NULL) AND
				((f.RecordStatusID = 1) AND (ft.RecordStatusID = 1))
			ORDER BY f.FilmID';
$select_orphans = $db->prepare($select_orphans_sql);
$select_orphans->execute(array(':tagid' => $tagid)) or die(print_r($db->errorInfo(), true));

while ($rows = $select_orphans->fetch(PDO::FETCH_ASSOC)) {
	$data[$ratings_counter] = array(
			"userid" => "none",
			"title" => $rows["Name"],
			"filmid" => $rows["FilmID"],
			"rating" => "nope"
			);
	$ratings_counter ++;
}


################################
#
# FUNCTION: getAverage
# This function adds a column
# to the end of each row and
# calculates the average
#
################################
$all_avg = [];
function getAverage($curr_col, $max_cols, $row_rating) {
	global $all_avg;
	if ($curr_col == $max_cols) {
		$avg = round((array_sum($row_rating) / count($row_rating)), 2);
		$rounded_rating = round($avg * 2, 0) / 2;
		$decimal_flag = strlen(strval($rounded_rating));
		if ($decimal_flag > 1) {
			$str_rating = strval($rounded_rating);
			$base_rating = explode(".", $str_rating);
			$rating_css_class = $base_rating[0] . '_5';
		} else {
			$rating_css_class = $rounded_rating;
		}
		echo '<td class="rating_' . $rating_css_class  . '">';

		echo '<strong>' . $avg . '</strong></td>'; 
		array_push($all_avg, $avg);
		return 1;
	} else {
		return 0;
	}
}

# Magic happens here
$existing_rows = [];
$all_ratings = [];
$col_counter = 0;
$shitty_hack = 0; # fuck everything

while ($col_counter < count($column_order)) {
	foreach ($data as $row) {

		# Creating new table row and echoing film title/link in first column
		# Doesn't occur if the filmid is in $existing_rows[] to prevent dupes
		if ((! in_array($row["filmid"], $existing_rows))) {

			##########
			# hacky loop to fill in blank <td>s to end of row after last rating BEFORE making a new line
				while (($shitty_hack == 1) and ($col_counter < count($column_order))) {
					echo '<td class="rating_none">&nbsp;</td>';
					# checking for end-of-row and appending average column
					if ((count($column_order) - $col_counter == 1) and ($avg_flag == 0)) {
						$avg_flag = getAverage($column_order[$col_counter], count($column_order), $all_ratings);
					}
					$col_counter++;
				}	
				$shitty_hack = 1;
			# end hacky nonsense; back to new line detection
			##########

			$avg_flag = 0;
			$rowid = $row["filmid"];
			$all_ratings = [];
			$col_counter = 0;
			array_push($existing_rows, $rowid);

			echo '
			<tr>
			<td><a href="films?id=' . $row["filmid"] . '">' . $row["title"] . '</a></td>';
		}
		
		# hacking around some flaw in the code, probably. 
		if (! isset($column_order[$col_counter])) {break;}

		##########################################
		# Actually populating rest of the table
		##########################################

		# this row contains ratings, but none for this column; drop a &nbsp in a <td>
			if (($column_order[$col_counter] != $row["userid"]) and ($col_counter < count($column_order))) {
				while (($column_order[$col_counter] != $row["userid"]) and ($col_counter < count($column_order))) {
					echo '<td class="rating_none">&nbsp;</td>';
					# checking for end-of-row and appending average column
					if ((count($column_order) - $col_counter == 1) and ($avg_flag == 0)) {
						$avg_flag = getAverage($column_order[$col_counter], count($column_order), $all_ratings);
					}
					$col_counter ++;
					# wait, wait... there's more!
					if (! isset($column_order[$col_counter])) {break 2;}
				}
			}
		# every time I repeat this hack, I kick my dog
		if (! isset($column_order[$col_counter])) {break;}

		# if there IS a rating in this column (for this row)
			if (($column_order[$col_counter] == $row["userid"]) and ($col_counter < count($column_order))) {
				if (strlen($row["rating"]) > 1) {
					$base_rating = explode(".", $row["rating"]);
					$rating_css_class = $base_rating[0] . '_5';
				} else {
					$rating_css_class = $row["rating"];
				}
				echo '<td class="rating_' . $rating_css_class  . '">';
				echo $row["rating"];
				echo '</td>';
				array_push($all_ratings, $row["rating"]);
				# checking for end-of-row and appending average column
				if ((count($column_order) - $col_counter == 1) and ($avg_flag == 0)) {
					$avg_flag = getAverage($column_order[$col_counter], count($column_order), $all_ratings);
				}
				$col_counter ++;
			}	
	}
}	

# getting user averages
echo '<tr id="user_averages"><td>Average</td>';

for ($i = 0; $i < $num_users; $i++) {
	$curr_array = $userids[$i];
	$curr_id = $curr_array["userid"];

	$select_user_avg_sql = 'SELECT 	AVG(r.Rating) AS Average 
				FROM 	tblUsers AS u LEFT JOIN
					tblRatings AS r ON r.UserID = u.UserID LEFT JOIN
					tblFilms AS f ON f.FilmID = r.FilmID LEFT JOIN
					tblFilmTags AS ft ON ft.FilmID = f.FilmID
				WHERE 	(ft.TagID = :tagid) AND (u.UserID = :userid) AND 
					((u.RecordStatusID = 1) AND (r.RecordStatusID = 1) AND (f.RecordStatusID = 1) AND (ft.RecordStatusID = 1))
				ORDER BY u.UserID';
	$select_user_avg = $db->prepare($select_user_avg_sql);
	$select_user_avg->execute(array(':tagid' => $tagid, ':userid' => $curr_id)) or die(print_r($db->errorInfo(), true));
	
	while ($rows = $select_user_avg->fetch(PDO::FETCH_ASSOC)) {
		$avg = $rows["Average"];
	}
	
	echo '<td><strong>' . round($avg, 2) . '</strong></td>';
}
echo '<td><strong>' . round((array_sum($all_avg) / (count($all_avg))), 2)  . '</strong></td></tr>';

echo '</table>';
?>
</body>
</html>
