<?php

require_once("App.php");
require_once("DB.php");

class Languages extends App {
	private $requiredParameters = array();
	private $missingParameters = array();
	
	private $requestMethod;
	private $ajaxParameters;
	private $urlParameters;

	// MYSQL ERROR LIST
	private $MYSQL_DUPLICATED_KEY = 1062;
	private $MYSQL_FOREIGN_KEY_FAILS = 1452;
	

	private $selectStmt = "";
	
	public function __construct() { }

	// PARENT METHODS
	// private function setRequestMethod() {}

	// private function getRequestMethod() {
	//     return $this->requestMethod;
	// }
	
	private function setUrlParameters() {
			return $this->urlParameters = $_GET;                                   // SET ALL POSSIBLE PARAMETERS PASSED BY URL 
	}
	
	private function setAjaxParameters() {
			return $this->ajaxParameters = file_get_contents('php://input');       // SET ALL POSSIBLE PARAMETERS PASSED BY AN AJAX CALL
	}
	
	private function setAllParameters() {
			$this->setUrlParameters();          // SET ALL POSSIBLE PARAMETERS PASSED BY URL 
			$this->setAjaxParameters();         // SET ALL POSSIBLE PARAMETERS PASSED BY AN AJAX CALL
	}

	private function checkRequiredParemetes() {
			// RETRIEVE ALL  MISSING REQUIRED PARAMETERS
	}

	/**
	 *  @param  
	 *      $wu (boolean) : indicates if the user needs to be warned that it haven't passed any parameter ($wu means 'warnUser')
	 */
	private function emptyParameters($wu) {
			if( empty( $this->urlParameters) && empty($this->ajaxParameters) ) {
					if($wu) $this->e_emptyParameters();     // WARN THE USER THAT IT HAVEN'T PASSED ANY PARAMETER          
					return true;                            // INDICATES THAT NONE PARAMETERS HAVE BEEN PASSED
			}
			return false; // INDICATES THAT AT LEAST ONE PARAMETER HAS BEEN PASSED
	}

	public function setSelectStmt() {
			// IF AN ARGUMENT NAMED 'SHOWTEAMS' WAS PASSED..
			if (array_key_exists("SHOWTEAMS", $this->urlParameters))
			
			{
					$this->selectStmt = "SELECT 
																	G.`ID`,
																	G.`NAME`,
																	T.`NAME` AS `BELONGS TO`
																	FROM `SG_GROUPS` AS G
																	INNER JOIN `SG_TOURNAMENTS` AS T 
																	ON G.`TOURNAMENT_ID` = T.`ID`
																	WHERE G.`ACTIVE` != 0
																	ORDER BY(G.`ID`)"; 
			}
			else 
			{
															// $this->selectStmt =  "SELECT `ID`,`NAME` FROM `SG_GROUPS`
															//                         WHERE `ID` IN ({$placeholders})
															//                         AND `ACTIVE` != '0';"; 

			} 
	}

	public function getSpecificGroups() {
			$teamsInfo = json_decode( $this->urlParameters['info'],true );
			$teamsInfo = array_change_key_case($teamsInfo,CASE_UPPER);

			try {
					
					$conn = (new DB())->connect();  // START A CONNECTION
					
					$ids = $teamsInfo['ID'];   // STORE THE ARRAY CONTAINING ALL DESIRED ID'S  
					
					$placeholders = implode(",", array_fill(0, count($ids),"?")); // CREATE THE PLACEHOLDERS TO BE USED WITH ->bindValue()

						// DEFINES A SQL STATEMENT
					if (array_key_exists("showTeams", $this->urlParameters)) 
					{
							$this->selectStmt =  
									"SELECT 
									`TOR`.`ID` AS 'tournamentId',
									`TOR`.`NAME` AS 'tournament',
									`G`.`ID` AS 'groupId',
									`G`.`NAME` AS 'group',
									`TE`.`FULLNAME` AS 'team',
									`TE`.`SHORTNAME` AS 'shortname',
									`TE`.`FLAG` AS 'flag'
											FROM `SG_GROUP_FORMATIONS`AS `F`
											INNER JOIN `SG_GROUPS` AS `G` ON(`G`.`ID` = `F`.`GROUP_ID`)
											INNER JOIN `SG_TOURNAMENTS` AS `TOR` ON(`G`.`TOURNAMENT_ID` = `TOR`.`ID`)
											INNER JOIN `SG_TEAMS` AS `TE` ON(`TE`.`ID` = `F`.`TEAM_ID`)
											WHERE G.`ID` IN ({$placeholders})";
					}
					else 
					{
							$this->selectStmt =  
									"SELECT 
											G.`ID`,
											G.`NAME`,
											T.`NAME` AS `BELONGS TO`
											FROM `SG_GROUPS` AS G
											INNER JOIN `SG_TOURNAMENTS` AS T ON G.`TOURNAMENT_ID` = T.`ID`
													WHERE G.`ID` IN ({$placeholders})";
					}
					
					$prepareSelect = $conn->prepare($this->selectStmt);  // PREPARES THE QUERY
			
					// EXECUTE IT AND IF THE QUERY RAN SUCCESSFULLY AND AT LEAST ONE RESULT WAS RETURNED...           
					if($prepareSelect->execute($ids) && $prepareSelect->rowCount()) {

							$result = $prepareSelect->fetchAll(PDO::FETCH_ASSOC);     // FETCH THE RESULT IN AN ASSOCIATIVE ARRAY
			
							echo json_encode($result);  // AND PRINT IT
					}
					else $this->e_noGroupsFound();
			}
			catch(PDOException $error){
					$stateError = $error->errorInfo[0];
					$codeError = $error->errorInfo[1];
					$specificMessage = $error->errorInfo[2];            
					echo $genericMessage = $error->getMessage();
			}
	}

	private function retrieveAllLanguages() {
		$getData = array_change_key_case( $this->urlParameters, CASE_UPPER );
		$userId = json_decode( $getData['USER'],true );

		try {
			$conn = (new DB())->connect();   
			
			$sql_selectStmt = "SELECT * FROM `CH_USERS` 
				WHERE `USER_ID` = :USER_ID";

			$prepareSelect = $conn->prepare($sql_selectStmt); 
			$prepareSelect->bindParam(':USER_ID', $userId); 

			if(
				$prepareSelect->execute() && 
				!$prepareSelect->rowCount()
			){
				$this->e_userNotFound();
				$conn = null;
				exit();
			}

			$sql_selectStmt = "SELECT * FROM `CH_LANGUAGES` 
				WHERE `USER_ID` = :USER_ID";
	
			$prepareSelect = $conn->prepare($sql_selectStmt); 
			$prepareSelect->bindParam(':USER_ID', $userId);   

			if(
				$prepareSelect->execute() && 
				$prepareSelect->rowCount()
			){
				$result = $prepareSelect->fetchAll(PDO::FETCH_ASSOC);
				echo json_encode($result);
			}
			else {
				$this->e_noLanguagesAvaliable();
			}
			$conn = null;
		}catch(PDOException $error){
			$stateError = $error->errorInfo[0];
			$codeError = $error->errorInfo[1];
			$specificMessage = $error->errorInfo[2];            
			echo $genericMessage = $error->getMessage();
			$conn = null;
			// if($codeError == $this->MYSQL_DUPLICATED_KEY) $this->e_DK();
			// if($codeError == $this->MYSQL_FOREIGN_KEY_FAILS ) $this->e_FK();
		}
	}

	public function addUserLanguage() {
		echo "add";
			$info = json_decode( $this->urlParameters['add'], true );
			
			// try {
			// 		$conn = (new DB())->connect();
			// 		// CREATE A INSERT STMT 
			// 		$sql_insertStmt =    
			// 				"INSERT INTO `CODE_HELP`.`CH_LANGUAGES`
			// 								(
			// 									`USER_ID`,
			// 									`LANGUAGE_NAME`
			// 								)
			// 						VALUES 
			// 								( 
			// 									:USER_ID, 
			// 									:LANGUAGE_NAME
			// 								)";            
			// 		// PREPARE THE QUERY
			// 		$sql_insertPrepare = $conn->prepare($sql_insertStmt);   
					
			// 		$userId = $info['user'];
			// 		$languageName = $info['language'];

			// 		$sql_insertPrepare->bindParam(':USER_ID' , $userId);         
			// 		$sql_insertPrepare->bindParam(':LANGUAGE_NAME', $languageName);   
			// 		$sql_insertPrepare->execute(); 

			// 		$this->s_insert();
			// }catch(PDOException $error){
			// 		$stateError = $error->errorInfo[0];
			// 		$codeError = $error->errorInfo[1];
			// 		$specificMessage = $error->errorInfo[2];            

			// 		if($codeError == $this->MYSQL_DUPLICATED_KEY) $this->e_alreadyExistingLanguage();
			// 		if($codeError == $this->MYSQL_FOREIGN_KEY_FAILS ) $this->e_nonExistingUser();
			// }
	}

	public function updateLanguages() {
		$rawInfo = json_decode( $this->urlParameters['update'], true ); // DECODE THE JSON INTO AN ARRAY
		
		try{        
			$conn = (new DB())->connect();   

			$sql_prepare = $conn->prepare(
				"UPDATE `CH_LANGUAGES` 
					SET `LANGUAGE_NAME` = ? 
					WHERE LANGUAGE_ID = ?"
			);

			$hasUnalteredRow = false;
			foreach($rawInfo as $language) {
				$sql_prepare->execute(array($language['newValue'], $language['langID']));
					
				if(!$sql_prepare->rowCount())
					$hasUnalteredRow = true;
			}

			if($hasUnalteredRow) {
				$this->e_UnalteredUpdate();
				exit();
			}

			$this->s_update(); // OUTPUT THE SUCCESS RESULT   
			$conn = null;
		}
		catch(PDOException $error){
			echo "Message: {$error->getMessage()}";
			echo "\nCode: {$error->getCode()}";
		}
	}

	public function updateGroups() {
			$groupsInfo = json_decode( $this->urlParameters['info'], true ); // DECODE THE JSON INTO AN ARRAY
			
			try{        
					$conn = (new DB())->connect();   
			
					foreach($groupsInfo as &$group) {
							/**
							 * CONVERTS ALL KEYS TO UPPERCASE. 
							 * THE REASON IS THAT ALL COLUMN'S NAMES ON DATABASE ARE CURRENTLY IN UPPERCASE
							*/   
							$group = array_change_key_case($group,CASE_UPPER);        
							/**
							 * RETURNS ALL PROPERTIES OF THE ARRAY THAT'S GIVEN BY THE USER BUT THE 'ID' PROPERTY. T
							 * THIS NEW ARRAY WILL CONTAIN ALL FIELDS (AND ITS VALUES) THAT SHOULD BE UPDATED. 
							*/  
							$dataToUpdate = array_slice($group,1);

							$group["prepareParams"] = array_slice($group,0);
					
							/**
							 * GERERATES SQL SYNTAX FORMAT AND STORE THEM INTO THE TEMPORARY ARRAY
							 */                        
							$temp = [];      // A TEMPORARY ARRAY TO STORE THE DATA THAT MUST BE UPDATED IN A SQL SYNTAX FORMAT   
							
							foreach($dataToUpdate as $field => $key) {
									$temp[] = " $field = :{$field}"; 
							}                
							/**
							 * - CONVERTS THE ARRAY INTO A STRING, WITH ITS ITEMS SEPARETED BY COMMA 
							 * - REMOVES A WHITESPACE AT THE STRING'S START
							*/ 
							$group["placeholders"] = ltrim(implode(",",$temp));  
							/**
							 * - CREATES A STRING TO BE SET AS CONTENT OF A PREPARE STATEMENT
							*/ 
							$group["prepareStmt"] = "UPDATE `SG_GROUPS` SET " . $group["placeholders"] . " WHERE (ID = :ID AND ACTIVE != 0)";
															
							$prepareUpdate = $conn->prepare($group["prepareStmt"]); // SET THE PREPARE STATEMENT
							// EXECUTE IT
							if( $prepareUpdate->execute($group["prepareParams"]) ) 
							{
									if( !$prepareUpdate->rowCount() ) 
									{
											$this->e_updates();
											exit();
									}
									
							}       
									

					}
					$this->s_update(); // OUTPUT THE SUCCESS RESULT   
			}catch(PDOException $error){
					$stateError = $error->errorInfo[0];
					$codeError = $error->errorInfo[1];
					$specificMessage = $error->errorInfo[2];            
					echo $genericMessage = $error->getMessage();

					if($codeError == $this->MYSQL_DUPLICATED_KEY) $this->e_alreadyExistingGroup();
					// if($codeError == $this->MYSQL_FOREIGN_KEY_FAILS ) $this->e_nonExistingTournament();
			}
	}

	public function removeGroups() {
			$groupsInfo = json_decode( $this->urlParameters['info'], true ); // DECODE THE JSON INTO AN ARRAY
			$groupsInfo = array_change_key_case($groupsInfo,CASE_UPPER);      // CONVERT THE KEYS TO UPPERCASE

			/**
			 * BUILDS THE PARAMETERS STRUCUTURE TO BE USED IN A PREPARE STATEMENT
			*/
			$ids = $groupsInfo['ID'];                                        // CONVERT THE GIVEN ID'S TO A SQL SYNTAX FORMAT
			$placeholders = implode(",", array_fill(0,count($ids),"?") );   // PREPARE PLACEHOLDERS

			try {
					$conn = (new DB())->connect(); 
					$prepareDelete = $conn->prepare("UPDATE `SG_GROUPS` SET ACTIVE = 0 
																																	WHERE ID IN ( {$placeholders} ) ");
					
					if($prepareDelete->execute($ids)) {
							if($prepareDelete->rowCount() ) $this->s_remove();
							else $this->e_noGroupsRemoved();
					}
					

			}catch(PDOException $error) {
					$stateError = $error->errorInfo[0];
					$codeError = $error->errorInfo[1];
					$specificMessage = $error->errorInfo[2];            
					echo $genericMessage = $error->getMessage();

					// if($codeError == $this->MYSQL_DUPLICATED_KEY) $this->e_alreadyExistingGroup();
					// if($codeError == $this->MYSQL_FOREIGN_KEY_FAILS ) $this->e_nonExistingTournament();
			} 
	}

	// ERRORS
	public function e_invalidStructure() {
			echo json_encode(
							array(
									"request type" => $_SERVER['REQUEST_METHOD'], 
									"status" => "failure", 
									"message" => "Sorry, your request doesn't fit any valid structure. Try something like this: ?user=1",
									'code' => '001'
							)
			);
			exit();
	}

	public function e_userNotFound() {
		echo json_encode(
			array(
				"request type" => $_SERVER['REQUEST_METHOD'], 
				"status" => "failure", 
				"message" => "User not found!!",
				'code' => '002'
			)
		);
		exit();
	}

	public function e_noLanguagesAvaliable() {
		echo json_encode(
			array(
				"request type" => $_SERVER['REQUEST_METHOD'], 
				"status" => "failure", 
				"message" => "No avaliable languages yet.",
				'code' => '003'
			)
		);
		exit();
	}

	public function e_UnalteredUpdate() {
		echo json_encode(
			array(
				"request type" => $_SERVER['REQUEST_METHOD'], 
				"status" => "warning", 
				"message" => "Your update command worked, but not all entries were update!",
				'code' => '004'
			)
		);
		exit();
	}

	public function e_emptyParameters() {
		echo json_encode(
			array(
					"request type" => $_SERVER['REQUEST_METHOD'], 
					"status" => "failure", 
					"message" => "You have not passed any parameter",
					'code' => '005'
			)
		); 
	}

	public function e_updates() {
			echo json_encode(
					array(
							"request type" => $_SERVER['REQUEST_METHOD'], 
							"status" => "failure", 
							"message" => "No record(s) updated. Check for ALL GROUPS you want to remove: if the 'identification' that was passed is correct OR if the group was removed OR if the field's content you want to update are the same than the previous ones.",
							'code' => '004',
							'active groups' => $this->getAllGroups()
					)
			);
			exit();
	}

	public function e_noGroupsRemoved() {
			echo json_encode(
					array(
							"request type" => $_SERVER['REQUEST_METHOD'], 
							"status" => "failure", 
							"message" => "Sorry, could not find any Group to be removed from database",
							'code' => '005'
					)
			);
			exit();
	}

	public function e_alreadyExistingLanguage() {
			echo json_encode(
					array(
							"request type" => $_SERVER['REQUEST_METHOD'], 
							"status" => "failure", 
							"message" => "This language already exists!",
							'code' => '006'
					)
			);
			exit();
	}

	public function e_nonExistingUser() {
			echo json_encode(
					array(
							"request type" => $_SERVER['REQUEST_METHOD'], 
							"status" => "failure", 
							"message" => "This user doesn't exist!",
							'code' => '007'
					)
			);
			exit();
	}



	// RESPONSES USERS FEEDBACK
	public function s_insert() {
			echo json_encode(
					array(
							"request type" => $_SERVER['REQUEST_METHOD'], 
							"status" => "success", 
							"message" => "Language successfully created!",
							'code' => '101'
					)
			);
			exit();
	}
	
	public function s_update() {
		echo json_encode(
			array(
				"request type" => $_SERVER['REQUEST_METHOD'], 
				"status" => "success", 
				"message" => "Language(s) successfully updated!",
				'code' => '101'
			)
		);
	}

	public function s_delete() {
			echo json_encode(
					array(
							"request type" => $_SERVER['REQUEST_METHOD'], 
							"status" => "success", 
							"message" => "Tournament(s) successfully deleted. Plis bilivi mi!",
							'code' => '102'
					)
			);
	}

	public function s_remove() {
			echo json_encode(
					array(
							"request type" => $_SERVER['REQUEST_METHOD'], 
							"status" => "success", 
							"message" => "Group(s) successfully removed. Plis bilivi mi!",
							'code' => '103'
					)
			);
	}
	
	
	
	// RESPONSES

	public function response_GET() {
			/* 
					* SINCE IT'S A 'GET' REQUEST WE'RE ONLY GONNA NEED PARAMETERS PRESENT INSIDE THE $_GET SUPERGLOBAL. SO, SET THOSE...
			*/ 
			$this->setUrlParameters();  
	
			/*  
					*   TERMINATE THIS FUNCTION IF:        
					*       - MORE THAN ONE PARAMETERS WERE PASSED... 
					*       - AN ARGUMENT NAMED 'info' WASN'T PASSED... 
			*/
			if( sizeof($this->urlParameters) > 1 || sizeof($this->urlParameters) < 1) {
					if( 
							!key_exists("info",$this->urlParameters) ||
							!key_exists("showTeams",$this->urlParameters)
					) $this->e_invalidStructure();
			} 


			if( 
					$this->emptyParameters(false) || 
					!key_exists("user", array_change_key_case($this->urlParameters,CASE_UPPER))
			){
				$this->retrieveAllLanguages();
			}
	}

	public function response_POST() {
				/* 
			*  SET THE POSSIBLE GIVEN PARAMETERS INTO THE OBJECT...
			*/ 
			$this->setAllParameters(); 
			$this->addUserLanguage();
	}

	public function response_PATCH() {
				/* 
			*  SET THE POSSIBLE GIVEN PARAMETERS INTO THE OBJECT...
			*/ 
			$this->setAllParameters();   

			$this->updateLanguages();
	}

	public function response_DELETE() {
			/* 
			*  SET THE POSSIBLE GIVEN PARAMETERS INTO THE OBJECT...
			*/ 
			$this->setUrlParameters();   

			$this->removeGroups();
	}

	public function response() {
		// print_r($_SERVER);
		// exit();
		switch ($_SERVER['REQUEST_METHOD']) {
			case 'GET':  
					// CALL A CUSTOM RESPONSE FOR MADE FOR A 'GET' REQUEST
					$this->response_GET();
					break;
			case 'POST':
			// CALL A CUSTOM RESPONSE FOR MADE FOR A 'POST' REQUEST
			$this->response_POST();
				break;
			case 'PATCH':
					// CALL A CUSTOM RESPONSE FOR MADE FOR A 'PATCH' REQUEST
					$this->response_PATCH();
					break;
			case 'DELETE':
					// CALL A CUSTOM RESPONSE FOR MADE FOR A 'DELETE' REQUEST
					$this->response_DELETE();
					break;
			case 'OPTIONS':
				break;
			default:
				echo 'default';
				$this->requestMethod = "GET";
				$this->response_GET();
		}	
	}
	// PARENT METHODS


}