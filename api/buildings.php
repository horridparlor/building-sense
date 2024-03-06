<?php

use system\Database;

header('Content-Type: application/json');

include("../system/Database.php");
include("../system/constants.php");

function getCards()
{
    $maxItems = Database::getIntParam("maxItems");
    $offset = Database::getIntParam("offset");
    
    
    $database = new Database();
    $sql = <<<SQL
        SELECT id, name, streetAddress, postNumber
        FROM building
        LIMIT :maxItems
        OFFSET :offset
    SQL;
    $replacements = array(
        'maxItems' => ['value' => Utility::limitMaxItems($maxItems), 'type' => PDO::PARAM_INT],
        'offset' => ['value' => $offset ?? 0, 'type' => PDO::PARAM_INT]
    );
    $result = $database->query($sql, $replacements);
    if (count($result) > 0) {
        return json_encode(array(
            "status" => "Success",
            "countOfBuildings" => count($result),
            "buildings" => $result
        ));
    } else {
        return json_encode(array("status" => "No buildings"));
    }
}

echo getCards();

?>


