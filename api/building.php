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
        'maxItems' => Database::getIntReplacement(Utility::limitMaxItems($maxItems)),
        'offset' => Database::getIntReplacement($offset ?? 0)
    );
    $result = $database->query($sql, $replacements);
    if (count($result) > 0) {
        return json_encode(array(
            "status" => "Success",
            "countOfBuildings" => count($result),
            "buildings" => $result
        ));
    } else {
        http_response_code(204);
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
        'name' => Database::getStringReplacement($name),
        'streetAddress' => Database::getStringReplacement($streetAddress),
        'postNumber' => Database::getIntReplacement($postNumber)
    );
    $result = $database->query($sql, $replacements);
    return json_encode(array(
       "status" => "Success"
    ));
}

$database = new Database();
$database->handleRequest('getBuildings', 'postBuilding');

