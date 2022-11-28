<?php
/** 
 * processes data when new graph settings are submitted 
 */
require_once "./database.php";
require_once "./redirect_user.php";
require_once "./string_utilities.php";

function processData(DatabaseAccess $database, array $databaseSettings) {
    $change = array();
    foreach (array_keys($_POST) as $key) {
        switch ($key) {
            case "xLabel":
                if ($databaseSettings["x_axis"] != $_POST["xLabel"]) {
                    $change["x_axis"] = $_POST["xLabel"];
                }
                break;
            case "yLabel":
                if ($databaseSettings["y_axis"] != $_POST["yLabel"]) {
                    $change["y_axis"] = $_POST["yLabel"];
                }
                break;
            case "xResolution":
                if (is_numeric($_POST["xResolution"])) {
                    if ($databaseSettings["x_resolution"] != $_POST["xResolution"] && $_POST["xResolution"] >= 0) {
                        $change["x_resolution"] = $_POST["xResolution"];
                    }
                }
                break;
            case "yResolution":
                if (is_numeric($_POST["yResolution"])) {
                    if ($databaseSettings["y_resolution"] != $_POST["yResolution"] && $_POST["yResolution"] >= 0) {
                        $change["y_resolution"] = $_POST["yResolution"];
                    }
                }
                break;
            case "axisFont":
                if (is_numeric($_POST["axisFont"])) {
                    if ($databaseSettings["axisFontSize"] != $_POST["axisFont"] && $_POST["axisFont"] >= 0) {
                        $change["axisFontSize"] = $_POST["axisFont"];
                    }
                }
                break;
            case "labelFont":
                if (is_numeric($_POST["labelFont"])) {
                    if ($databaseSettings["labelFontSize"] != $_POST["labelFont"] && $_POST["labelFont"] >= 0) {
                        $change["labelFontSize"] = $_POST["labelFont"];
                    }
                }
                break;
            case "gridDivision":
                if (is_numeric($_POST["gridDivision"])) {
                    if ($databaseSettings["vGridDivision"] != $_POST["gridDivision"] && $_POST["gridDivision"] >= 0) {
                        $change["vGridDivision"] = $_POST["gridDivision"];
                    }
                }
                break;
            case "vGridDivisionAuto":
                if ($databaseSettings["vGridDivision"] != 0) {
                    $change["vGridDivision"] = 0;
                }
            case "graphColor":
                if (isHexColor($_POST["graphColor"])) {
                    if ($databaseSettings["graphColor"] != $_POST["graphColor"]) {
                        $change["graphColor"] = $_POST["graphColor"];
                    }
                }
        }
    }
    if (count($change) > 0) {
        $database->setGraphSettings($_SESSION["projectID"], $change);
    }
}

if (isset($_POST["navigationGraphSettings"]) && $_POST["navigationGraphSettings"] === "save") {
    session_start();
    $database = new DatabaseAccess(true);
    $settings = $database->getGraphSettings($_SESSION["projectID"]);
    processData($database, $settings);
}
RedirectUser::returnToProjectPage();
?>