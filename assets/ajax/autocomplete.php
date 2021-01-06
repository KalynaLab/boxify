<?php
    require_once("../config.php");
    $searchTerm = $_POST['term'];

    $stmt = $db->prepare("SELECT g_id FROM genes WHERE g_id LIKE ? ORDER BY g_id ASC LIMIT 5");
    $stmt->bindValue(1, "%$searchTerm%", PDO::PARAM_STR);
    $stmt->execute();

    $genes = array();
    if ($stmt->rowCount() > 0) {
        echo '<div class="list-group" style="margin-top: -16px;">';
        foreach ($stmt as $row) {
            echo '<a href="#" class="list-group-item list-group-item-action search-suggestion">' . $row['g_id'] . '</a>';
        }
        echo '</div>';
    }

?>