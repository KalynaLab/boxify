<?php
    require_once("../config.php");
    $searchTerm = $_POST['term'];

    $stmt = $db->prepare("SELECT g_id FROM genes WHERE g_id LIKE ? ORDER BY g_id ASC LIMIT 5");
    $stmt->bindValue(1, "%$searchTerm%", PDO::PARAM_STR);
    $stmt->execute();

    $genes = array();
    if ($stmt->rowCount() > 0) {
        echo '<ul>';
        foreach ($stmt as $row) {
            echo '<li class="search-suggestion">' . $row['g_id'] . '</li>';
        }
        echo '</ul>';
    }

?>