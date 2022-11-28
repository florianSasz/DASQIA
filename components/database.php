<?php
/**
 * class to access and use the database
 */

// https://stackoverflow.com/questions/7378814/are-php-include-paths-relative-to-the-file-or-the-calling-code
require_once(dirname(__FILE__)."/project_log_class.php");

// https://scheible.it/hash-algorithmus-argon2-in-php/
function passwordHashing(string $password) {
    $options = [  
        'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,  
        'time_cost' => PASSWORD_ARGON2_DEFAULT_TIME_COST,  
        'threads' => PASSWORD_ARGON2_DEFAULT_THREADS,  
    ];
    $passwordHash = password_hash($password, PASSWORD_ARGON2ID, $options);
    return $passwordHash;
}

function buildUpdateString(array $data) {
    $updateString = implode(" = ?, " , array_keys($data));
    $updateString .= " = ?";
    return $updateString;
}

function getAliasTable(string $userType, &$table, &$idType) {
    switch ($userType) {
        case "user":
            $table = "user_aliases";
            $idType = "userID";
            break;
        case "shadowuser":
            $table = "shadowuser_aliases";
            $idType = "shadowuserID";  
            break;
        default:
            throw new Exception("invalid user type");   
    }
}

class DatabaseAccess {
    private $pdo = null;
    private $user = "root";
    private $password = "Apfelbaum";
    private $dbName = "projektarbeit";
    private $host = "localhost";
    private $log = null;

    function __construct(bool $writeToProject=false) {
        $this->connectToDatabase();
        if ($writeToProject) {
            if (array_key_exists("projectID", $_SESSION)) {
                $this->log = new WriteProjectLog($_SESSION["user"]["email"], $_SESSION["projectID"]);
            } else {
                $this->log = new WriteProjectLog($_SESSION["user"]["email"]);
            }
        }
    }

    private function connectToDatabase() {
        // https://websitebeaver.com/php-pdo-vs-mysqli
        $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->dbName . ";charset=utf8mb4";
        $options = [
        PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->password, $options);
        } catch (Exception $e) {
            error_log($e->getMessage());
            exit('Something weird happened'); //something a user can understand
        }
    }

    function addNewUser(string $email, string $name, string $password) {
        $stmt = $this->pdo->prepare("INSERT INTO users (email, name, password) VALUES (?, ?, ?)");
        $stmt->execute([$email, $name, passwordHashing($password)]);
    }

    function getUser(string $email) {
        $stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `email` = ?;");
        $stmt->execute(array($email));
        $user = $stmt->fetchAll();
        if ($user) {            
            $user[0]["aliases"] = $this->getAliases($user[0]["id"], "user");
            return $user[0];
        }
        return false;
    }

    function getUserShort(string $email) {
        $stmt = $this->pdo->prepare("SELECT `id`, `email`  FROM `users` WHERE `email` = ?;");
        $stmt->execute(array($email));
        $user = $stmt->fetchAll();
        if ($user) {
            return $user[0];
        }
        return false;
    }

    function getUserProjectsNumber(int $userID) {
        $stmt = $this->pdo->prepare("SELECT COUNT(`projectID`) FROM `users_in_projects` WHERE `userID` = ?;");
        $stmt->execute(array($userID));
        return $stmt->fetchAll()[0]["COUNT(`projectID`)"];
    }

    function updatePassword(int $userID, string $newPassword) {
        $stmt = $this->pdo->prepare("UPDATE `users` SET `password`= ? WHERE `id`= ?");
        $stmt->execute([passwordHashing($newPassword), $userID]);
    }

    function updateName(int $userID, string $newName) {
        $stmt = $this->pdo->prepare("UPDATE `users` SET `name`= ? WHERE `id`= ?");
        $stmt->execute([$newName, $userID]);
    }

    function getAliases(int $userID, string $userType) {
        $table; $idType;
        getAliasTable($userType, $table, $idType);
        $stmt = $this->pdo->prepare("SELECT `alias` FROM `" . $table . "` WHERE `" . $idType . "` = ?;");
        $stmt->execute(array($userID));
        $aliases = $stmt->fetchAll();
        return array_column($aliases, "alias");
    }

    function addAliases(int $userID, array $aliases, string $userType) {
        $table; $idType;
        getAliasTable($userType, $table, $idType);
        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare("INSERT INTO `" . $table . "` (alias, " . $idType . ") VALUES (?, ?);");
        foreach ($aliases as $alias) {
            $stmt->execute(array($alias, $userID));
        }
        $this->pdo->commit();
    }

    function removeAliases(int $userID, array $aliases, string $userType) {
        $table; $idType;
        getAliasTable($userType, $table, $idType);
        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare("DELETE FROM `" . $table . "` WHERE `alias` = ? AND `" . $idType . "` = ?;");
        foreach ($aliases as $alias) {
            $stmt->execute(array($alias, $userID));
        }
        $this->pdo->commit();
    }

    function getUserProjects(int $userID) {
        $stmt = $this->pdo->prepare("SELECT `projectID`, `title`, `finished` FROM `users_in_projects` INNER JOIN `projects` 
         ON `users_in_projects`.`projectID` = `projects`.`id` WHERE `userID` = ? ;");
        $stmt->execute(array($userID));
        return $stmt->fetchAll();
    }

    function isUserInProject(int $userID, int $projectID) {
        $stmt = $this->pdo->prepare("SELECT * FROM `users_in_projects` WHERE `userID` = ? AND `projectID` = ?;");
        $stmt->execute([$userID, $projectID]);
        $result = $stmt->fetchAll();
        return (count($result) > 0) ? true : false;
    }
    
    function getProjectMembers(int $projectID) {
        $stmt = $this->pdo->prepare("SELECT `id`, `name`, `email` FROM `users` INNER JOIN `users_in_projects` 
         ON `users`.`id` = `users_in_projects`.`userID` WHERE `users_in_projects`.`projectID` = ?;");
        $stmt->execute(array($projectID));
        return $stmt->fetchAll();
    }
    
    function getProjectShadowMembers(int $projectID) {
        $stmt = $this->pdo->prepare("SELECT `id`, `name` FROM `shadowusers` WHERE `projectID` = ?;");
        $stmt->execute(array($projectID));
        return $stmt->fetchAll();
    }
    
    function createNewProject(string $title, string $description) {
        $stmt = $this->pdo->prepare("INSERT INTO `projects` (title, description, finished) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, false]);
        $projectID = $this->pdo->lastInsertId();
        $this->log->setFile($projectID);
        $this->log->writeToLog("'" . $title . "' project was created");
        return $projectID;
    }
    
    function addUserToProject(int $userID, int $newProjectID, string $email, bool $isLeader=false) {
        $stmt = $this->pdo->prepare("INSERT INTO `users_in_projects` (UserID, projectID, isLeader) VALUES (?, ?, ?)");
        $stmt->execute([$userID, $newProjectID, $isLeader]);
        $this->log->writeToLog("'" . $email . "' (user) was added to the project");
    }
    
    function addNewShadowUser(string $name, array $aliases, int $projectID) {
        $stmt = $this->pdo->prepare("INSERT INTO `shadowusers` (name, projectID) VALUES (?, ?)");
        $stmt->execute([$name, $projectID]);
        $this->log->writeToLog("'" . $name . "' (shadowuser) was added to the project");
        $newShadowMemberID = $this->pdo->lastInsertId();

        if ($aliases) {
            $this->addAliases($newShadowMemberID, $aliases, "shadowuser");
        }

        return $newShadowMemberID;
    }
    
    function removeShadowUserFromProject(string $name, int $projectID) { // vielleicht umschreiben, um nach der ID zu loeschen
        $stmt = $this->pdo->prepare("DELETE FROM `shadowusers` WHERE `name` = ? AND `projectID` = ?;");
        $stmt->execute([$name, $projectID]);
        $this->log->writeToLog("'" . $name . "' (shadowuser) was removed from the project");
    }

    function removeUserFromProject(int $userID, int $projectID, string $email) {
        if ($this->getProjectLeader($projectID)["id"] != $userID) {
            $stmt = $this->pdo->prepare("DELETE FROM `users_in_projects` WHERE `userID` = ? AND `projectID` = ?;");
            $stmt->execute(array($userID, $projectID));    
            $this->log->writeToLog("'" . $email . "' (user) was removed from the project");
        }
    }

    function getProjectData(int $projectID) {
        $stmt = $this->pdo->prepare("SELECT * FROM `projects` WHERE `id` = ?;");
        $stmt->execute(array($projectID));
        return $stmt->fetchAll()[0];
    }

    function getProjectsettings(int $projectID) {
        $projectSettings = array(
            "title" => null,
            "description" => null,
            "leader" => null,
            "status" => null,
            "registeredMembers" => null,
            "shadowMembers" => null
        );
        $stmt = $this->pdo->prepare("SELECT `title`, `description`, `finished` FROM `projects` WHERE `id` = ?;");
        $stmt->execute(array($projectID));
        $temp = $stmt->fetchAll()[0];
        $projectSettings["title"] = $temp["title"];
        $projectSettings["description"] = $temp["description"];
        $projectSettings["status"] = $temp["finished"];
        
        $projectSettings["registeredMembers"] = $this->getProjectMembers($projectID);
        $projectSettings["shadowMembers"] = $this->getProjectShadowMembers($projectID);
        $projectSettings["leader"] = $this->getProjectLeader($projectID);

        return $projectSettings;
    }
    
    function addNewResearchQuestion(int $projectID, string $question, string $description=null) {
        $stmt = $this->pdo->prepare("INSERT INTO research_questions (question, description, projectID) VALUES (?, ? ,?)");
        $stmt->execute([$question, $description, $projectID]);
        $this->log->writeToLog("'" . $question . "' (research question) was added to the project");
    }
    
    function getResearchQuestions(int $projectID) {
        $stmt = $this->pdo->prepare("SELECT `question`, `description`, `id` FROM `research_questions` WHERE `projectID` = ?;");
        $stmt->execute(array($projectID));
        return $stmt->fetchAll();
    }

    function getResearchQuestionByID(int $rqID) {
        $stmt = $this->pdo->prepare("SELECT `question`, `description` FROM `research_questions` WHERE `id` = ?;");
        $stmt->execute([$rqID]);
        return $stmt->fetchAll()[0]; 
    }
    
    function updateResearchQuestion(int $projectID, int $rqID, string $question, string $description) {
        $oldRQ = $this->getResearchQuestionByID($rqID);
        $updateString = "";
        $updateValues = [];
        if ($oldRQ["question"] != $question) {
            $updateString .= "`question` = ?";
            $updateValues[] = $question;
        }
        if ($oldRQ["description"] != $description) {
            if ($updateString) {
                $updateString .= ", ";
            }
            $updateString .= "`description` = ?";
            $updateValues[] = $description;
        }
        $updateValues[] = $projectID;
        $updateValues[] = $rqID;

        $stmt = $this->pdo->prepare("UPDATE `research_questions` SET " . $updateString . " WHERE `projectID` = ? AND `id` = ?;");
        $stmt->execute($updateValues);

        if (str_contains($updateString, "question")) {
            $this->log->writeToLog("'" . $oldRQ["question"] . "' (research question) was renamed to: '" . $question . "'");
        }
        if (str_contains($updateString, "description")) {
            $this->log->writeToLog("'" . $question . "' (research question) description was updated");
        }
    }

    function deleteResearchQuestion(int $projectID, int $researchQuestionID, string $question) {
        $stmt = $this->pdo->prepare("DELETE FROM `research_questions` WHERE `id` = ? AND `projectID` = ?;");
        $stmt->execute(array($researchQuestionID, $projectID));
        $this->log->writeToLog("'" . $question . "' (research question) was removed from the project");
    }
    
    function addDocument(string $type, string $title, string $interviewer, string $originalInterviewer, string $interviewDate, string $evaluator, string $origianlEvaluator, string $evaluationDate, int $codes, int $projectID) {
        $stmt = $this->pdo->prepare("INSERT INTO documents (type, title, interviewer, original_interviewer, interview_date, evaluator, original_evaluator, evaluation_date, codes, projectID)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$type, $title, $interviewer, $originalInterviewer, $interviewDate, $evaluator, $origianlEvaluator, $evaluationDate, $codes, $projectID]);
        $this->log->writeToLog("'" . $title . "' (document) was added to the project");
    }
    
    function getDocuments(int $projectID) {
        $stmt = $this->pdo->prepare("SELECT * FROM `documents` WHERE `projectID` = ?;");
        $stmt->execute(array($projectID));
        return $stmt->fetchAll();
    }

    function getDocumentsForCodesProcessing(int $projectID) {
        $stmt = $this->pdo->prepare("SELECT `title`, `id`, `codes` FROM `documents` WHERE `projectID` = ?;");
        $stmt->execute(array($projectID));
        return $stmt->fetchAll();
    }
    
    function updateDocument(int $projectID, int $documentID, array $elementsToUpdate, string $title) { 
        $updateString = buildUpdateString($elementsToUpdate);

        $stmt = $this->pdo->prepare("UPDATE documents SET " . $updateString . " WHERE `projectID` = ? AND `id` = ?;"); 
        $stmt->execute(array_merge(array_values($elementsToUpdate), array($projectID, $documentID)));
        $this->log->writeToLog("'" . $title. "' (document) was updatet, changes in: " . implode(", ", array_keys($elementsToUpdate)));
    }
    
    function deleteDocument(int $projectID, int $documentID, string $title) {
        $stmt = $this->pdo->prepare("DELETE FROM `documents` WHERE `id` = ? AND `projectID` = ?;");
        $stmt->execute(array($documentID, $projectID));
        $this->log->writeToLog("'" . $title . "' (document) was removed from the project");
    }
    
    function addCode(int $projectID, string $name, array $frequencyInDocuments, int $parentID=null) { // $frequencyInDocuments = [[string title, int frequency, int documentID], [...], ...]
        $stmt = $this->pdo->prepare("INSERT INTO `codes` (name, parentID, projectID) VALUES (?, ?, ?)");
        $stmt->execute([$name, $parentID, $projectID]);
        $databaseCodeID = $this->pdo->lastInsertId();
        
        foreach ($frequencyInDocuments as $frequency) {
            $this->addCodeToDocumentRelation($databaseCodeID, $frequency["databaseID"], $frequency["frequency"]);
        }
        $this->log->writeTolog("'" . $name . "' (code) was added to the project");
        return $databaseCodeID;
    }

    function deleteCode(int $projectID, string $codeName) {
    // when deleting a parent all childs will be deleted as well
        $stmt = $this->pdo->prepare("DELETE FROM `codes` WHERE `name` = ? AND `projectID` = ?;");
        $stmt->execute([$codeName, $projectID]);
        $this->log->writeToLog("'" . $codeName . "' (code) was removed from the project including all child codes");
    }
    
    // TOOD: vielleicht log schreiben? 
    function addCodeToDocumentRelation(int $codeID, int $documentID, int $frequency) {
        $stmt = $this->pdo->prepare("INSERT INTO `codes_in_documents` (codeID, documentID, frequency) VALUES (?, ?, ?)");
        $stmt->execute([$codeID, $documentID, $frequency]);
    }
    
    // TOOD: vielleicht log schreiben? 
    function updateCodeToDocumentRelation(int $codeID, int $documentID, int $newFrequncy) {
        $stmt = $this->pdo->prepare("UPDATE `codes_in_documents` SET `frequency` = ? WHERE `codeID` = ? AND `documentID` = ?;");
        $stmt->execute([$newFrequncy, $codeID, $documentID]);
    }

    // TOOD: vielleicht log schreiben? 
    function removeCodeToDocumentRelation(int $codeID, int $documentID) {
        $stmt = $this->pdo->prepare("DELETE FROM `codes_in_documents` WHERE `codeID` = ? AND `documentID` = ?;");
        $stmt->execute([$codeID, $documentID]);
    }
    
    function getCodesWithParent(int $projectID, bool $getAssignment) { 
        $stmt = $this->pdo->prepare("SELECT `codes`.`id`, `codes`.`name`, `cParent`.`id` AS `parentID` FROM `codes` LEFT JOIN `codes` `cParent` ON `codes`.`parentID` = `cParent`.`id` WHERE `codes`.`projectID` = ?;");
        $stmt->execute(array($projectID));

        if ($getAssignment) { // with assignment of codes to research questions
            $codes = $stmt->fetchAll();
            for ($i = 0; $i < count($codes); $i++) {
                $stmt = $this->pdo->prepare("SELECT `research_questionID` FROM `codes_to_research_questions` WHERE `codeID` = ?;");
                $stmt->execute([$codes[$i]["id"]]);
                $assignedResearchQuestions = $stmt->fetchAll();
                if (count($assignedResearchQuestions) > 0) {
                    foreach ($assignedResearchQuestions as $assignedResearchQuestion) {
                        $codes[$i]["researchQuestions"][] = $assignedResearchQuestion["research_questionID"];
                    }
                }
            }
            return $codes;
        }
        return $stmt->fetchAll();
    }

    function getCodeDocumentRelation(int $codeID) {
        $stmt = $this->pdo->prepare("SELECT `documents`.`title`, `frequency`, `documentID` AS `databaseID` FROM `codes_in_documents` INNER JOIN `documents`
         ON `codes_in_documents`.`documentID` = `documents`.`id` WHERE `codes_in_documents`.`codeID` = ?;");
        $stmt->execute(array($codeID));
        return $stmt->fetchAll();
    }

    function updateCodeParent(int $codeID, int $parentID) {
        $stmt = $this->pdo->prepare("UPDATE `codes` SET `parentID` = ? WHERE `id` = ?;");
        $stmt->execute([$parentID, $codeID]);
    }

    function getLeaderStatus(int $userID, int $projectID) {
        $stmt = $this->pdo->prepare("SELECT `isLeader` FROM `users_in_projects` WHERE `projectID` = ? AND `userID` = ?;");
        $stmt->execute(array($projectID, $userID));
        return $stmt->fetchAll()[0]["isLeader"];
    }

    function getProjectLeader(int $projectID) {
        $stmt = $this->pdo->prepare("SELECT `email`, `id` FROM `users` INNER JOIN `users_in_projects` ON `users`.`id` = `users_in_projects`.`userID` WHERE `projectID` = ? AND `isLeader` = 1;");
        $stmt->execute(array($projectID));
        return $stmt->fetchAll()[0];
    }

    function updateProject(int $projectID, array $data) { // $data = ["key" => value, ...] -> ["title" => newTitle, ...]
        $updateString = buildUpdateString($data);
        $values = array_values($data);
        $values[] = $projectID;
        $logEntries = [];
        foreach (array_keys($data) as $type) {
            switch ($type) {
                case "title":   
                    $logEntries[] = "project was renamed to: '" . $data[$type] . "'";
                    break;
                case "description":
                    $logEntries[] = "projects description was changed";        
                    break;
                case "finished":
                    $x = ($data[$type]) ? "finished" : "ongoing";
                    $logEntries[] = "project status was changed to: " . $x;
                    break;
            }
        }

        $stmt = $this->pdo->prepare("UPDATE `projects` SET " . $updateString . " WHERE `id`= ?");
        $stmt->execute($values);
        foreach ($logEntries as $entry) {
            $this->log->writeToLog($entry);
        }
    }

    function setNewProjectLeader(int $projectID, array $newLeader, array $oldLeader) {
        $values = array(
            array(0, $projectID, $oldLeader["id"]),
            array(1, $projectID, $newLeader["id"])
        );
        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare("UPDATE `users_in_projects` SET isLeader = ? WHERE `projectID` = ? AND  `userID`= ?;");

        foreach ($values as $row) {
            $stmt->execute($row);
        }
        $this->pdo->commit();
        $this->log->writeToLog("project leader was set to: " . $newLeader["email"]);
    }

    function deleteProject(int $projectID) {
        $this->log->deleteLogFile();
        $stmt = $this->pdo->prepare("DELETE FROM `projects` WHERE `id` = ?;");
        $stmt->execute([$projectID]);
    }

    function getProjectStatus(int $projectID) {
        $stmt = $this->pdo->prepare("SELECT `finished` FROM `projects` WHERE `id` = ?;");
        $stmt->execute([$projectID]);
        return $stmt->fetchAll()[0]["finished"];
    }

    // TODO: write log
    function addCodeToResearchQuestionRelation(int $codeID, int $researchQuestionID) {
        $stmt = $this->pdo->prepare("INSERT INTO `codes_to_research_questions` (research_questionID, codeID) VALUES (?, ?)");
        $stmt->execute([$researchQuestionID, $codeID]);
    }
    // TODO: write log
    function removeCodeToResearchQuestionRelation(int $codeID, int $researchQuestionID) {
        $stmt = $this->pdo->prepare("DELETE FROM `codes_to_research_questions` WHERE `codeID` = ? AND `research_questionID` = ?");
        $stmt->execute([$codeID, $researchQuestionID]);
    }

    function codeIdExists(int $codeID, int $projectID) {
        $stmt = $this->pdo->prepare("SELECT `name` FROM `codes` WHERE `id` = ? AND `projectID` = ?;");
        $stmt->execute([$codeID, $projectID]);
        return (count($stmt->fetchAll()) > 0) ? true : false;
    }

    function rqIDExists(int $rqID, int $projectID) {
        $stmt = $this->pdo->prepare("SELECT `question` FROM `research_questions` WHERE `id` = ? AND `projectID` = ?;");
        $stmt->execute([$rqID, $projectID]);
        return (count($stmt->fetchAll()) > 0) ? true : false;
    }

    function documentIDExists(int $documentID, int $projectID) {
        $stmt = $this->pdo->prepare("SELECT `title` FROM `documents` WHERE `id` = ? AND `projectID` = ?;");
        $stmt->execute([$documentID, $projectID]);
        return (count($stmt->fetchAll()) > 0) ? true : false;
    }

    function getDocumentCodes(int $documentID) {
        $stmt = $this->pdo->prepare("SELECT `codeID`, `frequency` FROM `codes_in_documents` WHERE `documentID` = ?;");
        $stmt->execute([$documentID]);
        return $stmt->fetchAll();
    }

    function getGraphSettings(int $projectID) {
        $stmt = $this->pdo->prepare("SELECT `x_axis`, `y_axis`, `x_resolution`, `y_resolution` , `axisFontSize`, `labelFontSize`, `vGridDivision`, `graphColor` FROM `projects` WHERE `id` = ?;");
        $stmt->execute([$projectID]);
        return $stmt->fetchAll()[0];
    }

    function setGraphSettings(int $projectID, array $change) {
        $updateString = buildUpdateString($change);
        $values = array_values($change);
        $values[] = $projectID;

        $stmt = $this->pdo->prepare("UPDATE `projects` SET " . $updateString . " WHERE `id`= ?");
        $stmt->execute($values);
        $this->log->writeToLog("graph settings were updated");
    }

    function resetPasswordToken(string $email) {
        $stmt = $this->pdo->prepare("DELETE FROM `password_reset` WHERE `email` = ?;");
        $stmt->execute([$email]);
    }

    function setPasswordToken(string $email, string $selector, string $token, string $expires) {
        $stmt = $this->pdo->prepare("INSERT INTO `password_reset` (email, selector, token, expires) VALUES (?, ?, ?, ?)");
        $stmt->execute([$email, $selector, passwordHashing($token), $expires]);
    }

    function getResetPassword(string $selector, string $date) {
        $stmt = $this->pdo->prepare("SELECT *  FROM `password_reset` WHERE `selector` = ? AND `expires` >= ?;");
        $stmt->execute([$selector, $date]);
        return $stmt->fetchAll()[0];
        if (isset($stmt->fetchAll()[0])) {
        } else {
            return false;
        }
    }
}
?>