<?php
include 'php/config.php';

$edit_type = $_GET['type'];

if ($edit_type == "film") {
	$filmid = $_GET["id"];

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

	# getting unused tags to populate the dropdown to add one
	$select_unused_tags_sql = '	SELECT 	TagID, TagName 
					FROM 	tbll_Tags
					WHERE 	(TagID NOT IN (
							SELECT TagID 
							FROM tblFilmTags 
							WHERE (FilmID = :filmid) AND (RecordStatusID = 1)
						))';
	$select_unused_tags = $db->prepare($select_unused_tags_sql);
	$select_unused_tags->execute(array(':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));
		
	echo '<h1>' . $title . ' - ' . $filmyear . '</h1>';

	echo '<h1>Edit Film</h1>';
	echo '<form action="php/submit" method="POST">';
	echo '<input name="title" value="' . $title  . '" />';
	echo '<input name="year" value="' . $filmyear  . '" />';

	# need to fill in tags here
	echo '<h2>Tags</h2>';
	
	# iterating through existing tags
	$tag_count = 1;
	if ($tags[0] != null) {
		for ($i = 0; $i < count($tags); $i++) {
			echo '<select name="tag_' . ($i + 1) . '">';
			echo '<option value="' . $tag_ids[$i] . '">' . $tags[$i] . '</option>';
			echo '<option value="delete">Delete</option>';
			echo '</select><br />';
			$tag_count ++;
		}
	} else {$i = 0;}
	
	# option to add a new tag
	echo '<select name="tag_' . ($i + 1) . '">';
	echo '<option value="" selected>Add tag?</option>';
	# getting tags that aren't already selected
	while ($rows = $select_unused_tags->fetch(PDO::FETCH_ASSOC)) {
		echo '<option value="' . $rows["TagID"] . '">' . $rows["TagName"] . '</option>';
	}
	echo '</select>';

	echo '<input name="filmid" type="hidden" value="' . $filmid . '" />';
	echo '<input name="type" type="hidden" value="edit_film" />';
	echo '<input name="num_tags" type="hidden" value="' . $tag_count . '" />';
	echo '<button type="submit">Submit</button>';
	echo '</form>';

} 

#TODO: from here down

elseif ($edit_type == "tag") {
	$tagid = $_GET["id"];

	echo '<h1>Edit Tag</h1>';
	echo '<form action="php/submit" method="POST">';
	echo '<input name="tag" placeholder="Enter Tag Name" />';
	echo '<input name="type" type="hidden" value="edit_tag" />';
	echo '<button type="submit">Submit</button>';
	echo '</form>';

} elseif ($edit_type == "user") {
	$userid = $_GET["id"];
	
	echo '<h1>Edit User</h1>';
	echo '<form action="php/submit" method="POST">';
	echo '<input name="firstname" placeholder="First Name" />';
	echo '<input name="lastname" placeholder="Last Name" />';
	echo '<input name="type" type="hidden" value="edit_user" />';
	echo '<button type="submit">Submit</button>';
	echo '</form>';
}

require_once 'php/footer.php';
?>
