<?php
	require_once("../config.php");

	/* TO-DO: Reformat the data structure to save cookie storage
	space. Make it a dictionary, where each coordinate only appears
	once and use the shorter keys to build the transcripts.

	coor = { 'a': 1000, 'b': 2000, 'c': 2500, etc. }
	trs = { 't1': [[a, b], [c, d]] }

	Alternatively, I will just have to forget about the drawing-data
	cookie and query the database everytime if (re-)draw something.

	Maybe scaling the exon and CDS coordinates in this script would
	also make sense.

	UPDATE 10-10-2019: I probably forget about all that, I'm storing
	the data in the html now, so that should work I hope. */

	if (isset($_POST["gene"])) {

		$g_id = $_POST["gene"];
		$transcripts = array();
		$exonCoord = array();
		$cdsCoord = array();

		# Get some gene information
		try {
			$stmt = $db->prepare("SELECT chr, start, end, genomic_seq AS seq, strand FROM genes WHERE g_id = :g_id");
			$stmt->bindValue('g_id', $g_id);
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$gene = $stmt->fetch(PDO::FETCH_ASSOC);
			} else {
				echo json_encode(array( "okay" => False, "messages" => "Gene not found." ));
				exit;
			}
		} catch (PDOException $ex) {
			echo json_encode(array( "okay" => False, "messages" => "Gene not found." ));
			exit;
		}

		# Fetch exons
		try {

			$stmt = $db->prepare("SELECT t_id, start, end FROM exons WHERE t_id LIKE ? ORDER BY start");
			$stmt->bindValue(1, "%$g_id%", PDO::PARAM_STR);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				foreach ($stmt as $row) {

					# Add missing transcript identifiers
					if (!array_key_exists($row["t_id"], $transcripts)) { $transcripts[$row["t_id"]] = array("exons" => array(), "cds" => array()); }

					# Add coordinates
					array_push($transcripts[$row["t_id"]]["exons"], array( (int)$row["start"], (int)$row["end"]));

					# Push coordinates
					array_push($exonCoord, (int)$row["start"], (int)$row["end"]);
				}
			} else {
				echo json_encode(array( "okay" => False, "messages" => "Error fetching exons." ));
				exit;
			}

		} catch (PDOException $ex) {
			echo json_encode(array( "okay" => False, "messages" => $ex ));
			exit;
		}

		# Fetch CDS
		try {

			$stmt = $db->prepare("SELECT t_id, start, end FROM cds WHERE t_id LIKE ? ORDER BY start");
			$stmt->bindValue(1, "%$g_id%", PDO::PARAM_STR);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				foreach ($stmt as $row) {

					# Add coordinates
					array_push($transcripts[$row["t_id"]]["cds"], array( (int)$row["start"], (int)$row["end"]));

					# Push coordinates
					array_push($cdsCoord, (int)$row["start"], (int)$row["end"]);
				}
			} else {
				//echo json_encode(array( "okay" => False, "messages" => "Error fetching CDS." ));
				//exit;
			}

		} catch (PDOException $ex) {
			echo json_encode(array( "okay" => False, "messages" => $ex));
			exit;
		}

		# Reduce coordinates to unique elements only and sort
		$uniqExonCoord = array_unique($exonCoord);
		sort($uniqExonCoord);

		$uniqCdsCoord = array_unique($cdsCoord);
		sort($uniqCdsCoord);

		# Return JSON array
		$data = array(
			"okay" => True,
			"messages" => "Everything seems fine! :D",
			"transcripts" => $transcripts,
			"exonCoord" => $uniqExonCoord,
			"cdsCoord" => $uniqCdsCoord,
			"gene" => $gene
		);

		echo json_encode($data);
		exit;

	}
?>
