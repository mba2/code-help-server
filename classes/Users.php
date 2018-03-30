<?php

require_once("App.php");
require_once("DB.php");


 class Users extends App {
     
    private $requiredParameters = array();
    private $missingParameters = array();
    
    private $requestMethod;
    private $ajaxParameters;
    private $urlParameters;
    
    public function __construct() { 
    }
    
    // PARENT METHODS
    // private function setRequestMethod() {}

    // private function getRequestMethod() {
    //     return $this->requestMethod;
    // }
    
    private function setUrlParameters() {
        return $this->urlParameters = array_change_key_case($_GET,CASE_UPPER);                                   // SET ALL POSSIBLE PARAMETERS PASSED BY URL 
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

    public function getUserInfo() {
				$user = json_decode( $this->urlParameters['USER'],true )[0];

				try {
            // START A CONNECTION
            $conn = (new DB())->connect();              
            // DEFINES A SQL STATEMENT
            $sql_selectUserInfo = "SELECT *
									FROM `CODE_HELP`.`CH_USERS`
													WHERE `USER_ID` IN ('{$user['uid']}')
													AND `ACTIVE` != '0';";
            // PREPARES THE QUERY
            $prepareSelect = $conn->prepare($sql_selectUserInfo);  
            // EXECUTE IT            
            if($prepareSelect->execute() && $prepareSelect->rowCount()) {
                    // IF THE QUERY RAN SUCCESSFULLY AND AT LEAST ONE RESULT WAS RETURNED...
                    $result = $prepareSelect->fetchAll(PDO::FETCH_ASSOC);   // FETCH THE RESULT IN AN ASSOCIATIVE ARRAY
										echo json_encode(
											array(
												'status' => 'existed',
												'user' => $user['uid']
											)
										);     
										$conn = null;
            }else {
                $conn = null;
                $this->createUser($user);
						}
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

    public function createUser($user) {
			try {
				// START A CONNECTION
				$conn = (new DB())->connect();              
				// DEFINES A SQL STATEMENT
				$sql_insertUser = "INSERT INTO 
					`CODE_HELP`.`CH_USERS` VALUES
						('{$user['uid']}','{$user['providerId']}','{$user['email']}','{$user['displayName']}','{$user['photoURL']}','{$user['phoneNumber']}',1)";
				// PREPARES THE QUERY
				$prepareSelect = $conn->prepare($sql_insertUser);  
				// EXECUTE IT            
				if($prepareSelect->execute() && $prepareSelect->rowCount()) {
						// IF THE QUERY RAN SUCCESSFULLY AND AT LEAST ONE RESULT WAS RETURNED...
						echo json_encode(
							array(
								'status' => 'created',
								'user' => $user['uid']
							)
						);     
						$conn = null;
				}
			}catch(PDOException $error){
				$stateError = $error->errorInfo[0];
				$codeError = $error->errorInfo[1];
				$specificMessage = $error->errorInfo[2];            
				echo $genericMessage = $error->getMessage();
				$conn = null;
			}
    }

    public function e_noUserFound() {
        echo json_encode(
            array(
                "request type" => $_SERVER['REQUEST_METHOD'], 
                "status" => "failure", 
                "message" => "Sorry, could not find any user on database",
                'code' => '001'
            )
        );
    }
   
    // RESPONSES
    public function response_GET() {
        /* 
        * SINCE IT'S A 'GET' REQUEST WE'RE GONNA NEED ONLY THE PARAMETERS PRESENT INSIDE THE $_GET SUPERGLOBAL. SO, SET THOSE...
        */ 
        $this->setUrlParameters();   
    
        /*  
        *   TERMINATE THIS FUNCTION IF:        *
        *       - MORE THAN ONE PARAMETERS WERE PASSED... 
        *       - AN ARGUMENT NAMED 'user' WASN'T PASSED... 
        */
        if( sizeof($this->urlParameters) > 1) {
            if( !key_exists("user",$this->urlParameters)) $this->e_invalidStructure();
        } 

        if( !$this->emptyParameters(false) ) 
            $this->getUserInfo();

    }

    public function response_POST() {
         /* 
        *  SET THE POSSIBLE GIVEN PARAMETERS INTO THE OBJECT...
        */ 
        $this->setAllParameters();   

        (array_key_exists("CREATE", $this->urlParameters)) 
        ? $this->createTeams() 
        : $this->insertTeams();   
        
    }

    public function response_PATCH() {
         /* 
        *  SET THE POSSIBLE GIVEN PARAMETERS INTO THE OBJECT...
        */ 
        $this->setAllParameters();   

        $this->updateTeams();
    }

    public function response_DELETE() {
        /* 
       *  SET THE POSSIBLE GIVEN PARAMETERS INTO THE OBJECT...
       */ 
       $this->setUrlParameters();   

       $this->removeTeams();
   }

    public function response() {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':  
                // CALL A CUSTOM RESPONSE FOR MADE FOR A 'GET' REQUEST
                $this->response_GET();
                break;
            case 'POST':;
                // CALL A CUSTOM RESPONSE FOR MADE FOR A 'POST' REQUEST
                $this->response_POST();
                break;
            case 'PATCH':;
                // CALL A CUSTOM RESPONSE FOR MADE FOR A 'PATCH' REQUEST
                $this->response_PATCH();
                break;
            case 'DELETE':;
                // CALL A CUSTOM RESPONSE FOR MADE FOR A 'DELETE' REQUEST
                $this->response_DELETE();
                break;
            default:
                $this->requestMethod = "GET";
                $this->response_GET();
        }
       
    }
    // PARENT METHODS
    
} 