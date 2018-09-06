<?php
	require_once("../config.php");

	if (isset($_POST["gene"])) {

		$g_id = $_POST["gene"];
		$transcripts = array();
		$coord = array();

		$strand = $mysqli->query("SELECT strand FROM rtd2_transcripts WHERE g_id = '$g_id' GROUP BY strand")->fetch_object()->strand;

		# Fetch exons
		try {

			$stmt = $db->prepare("SELECT t_id, start, end FROM rtd2_exons WHERE t_id LIKE ? ORDER BY start");
			$stmt->bindValue(1, "%$g_id%", PDO::PARAM_STR);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				foreach ($stmt as $row) {

					# Add missing transcript identifiers
					if (!array_key_exists($row["t_id"], $transcripts)) { $transcripts[$row["t_id"]] = array(); }

					# Add coordinates
					array_push($transcripts[$row["t_id"]], array( (int)$row["start"], (int)$row["end"]));

					# Push coordinates
					array_push($coord, (int)$row["start"], (int)$row["end"]);
				}
			} else {
				echo json_encode(array( "okay" => False, "messages" => "Error fetching exons." ));
				exit;
			}

		} catch (PDOException $ex) {
			echo json_encode(array( "okay" => False, "messages" => $ex ));
			exit;
		}

		# Reduce coordinates to unique elements only and sort
		$uniq_coord = array_unique($coord);
		sort($uniq_coord);

		# Return JSON array
		echo json_encode(array(
			"okay" => True,
			"messages" => "Everything seems fine! :D",
			"transcripts" => $transcripts,
			"coordinates" => $uniq_coord,
			"gene" => array(
				"name" => $g_id,
				"strand" => $strand
			)
		));
		exit;

	}
?>