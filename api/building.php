<?php

use system\Database;

header('Content-Type: application/json');

include("../system/Database.php");
include("../system/constants.php");

function getBuildings(Database $database)
{
    $maxItems = $database->getIntParam("maxItems");
    $offset = $database->getIntParam("offset");

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
function postBuilding(Database $database) {
    $database = new Database();
    $database->getPostParams();
    $name = $database->getStringParam("name");
    $streetAddress = $database->getStringParam("streetAddress");
    $postNumber = $database->getIntParam("postNumber");

    $sql = <<<SQL
        INSERT INTO building (name, streetAddress, postNumber)
        VALUES (:name, :streetAddress, :postNumber)
    SQL;
    $replacements = array(
        'name' => [$name, 'type' => PDO::PARAM_STR],
        'streetAddress' => [$streetAddress, 'type' => PDO::PARAM_STR],
        'postNumber' => [$postNumber, 'type' => PDO::PARAM_INT]
    );
    $result = $database->query($sql, $replacements);
    return json_encode(array(
       "status" => "Success"
    ));
}

$database = new Database();
$database->handleRequest('getBuildings', 'postBuilding');

