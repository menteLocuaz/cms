<?php

declare(strict_types=1);

use App\Controllers\CurlController;
use App\Controllers\InstallController;
use App\Http\Security;

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

require_once dirname(__DIR__) . "/vendor/autoload.php";

Security::requireAdminAjax();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

function respondJson(array $payload, int $statusCode = 200): never
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

class ModulesAjax{

	/*=============================================
	Eliminar Módulo
	=============================================*/ 

	public $idModuleDelete;
	public $token; 

	public function deleteModule(){

		/*=============================================
		Validar columnas vinculadas al módulo
		=============================================*/

		$url = "columns?linkTo=id_module_column&equalTo=".base64_decode($this->idModuleDelete);
		$method = "GET";
		$fields = array();

		$getColumn = CurlController::request($url,$method,$fields);

		if($getColumn->status == 200){

			respondJson(["status" => 400, "error" => "El módulo tiene columnas vinculadas"], 400);

		}else{

			/*=============================================
			Traer la info del módulo para saber si es tabla
			=============================================*/

			$url = "modules?linkTo=id_module&equalTo=".base64_decode($this->idModuleDelete)."&select=type_module,title_module";
			$method = "GET";
			$fields = array();

			$module = CurlController::request($url,$method,$fields);

			if($module->status == 200){

				if($module->results[0]->type_module == "tables"){

					/*=============================================
					Eliminar la tabla de la BD en MySQL
					=============================================*/

					$tableToDrop = (string) $module->results[0]->title_module;
					if (Security::isValidIdentifier($tableToDrop)) {
						$sqlDestroyTable = "DROP TABLE ".$tableToDrop;
						$stmtDestroyTable = InstallController::connect()->prepare($sqlDestroyTable);
						$stmtDestroyTable->execute();
					}
				}
			}

			/*=============================================
			Eliminar el módulo
			=============================================*/

			$url = "modules?id=".base64_decode($this->idModuleDelete)."&nameId=id_module&token=".$this->token."&table=admins&suffix=admin";
			$method = "DELETE";
			$fields = array();

			$deleteModule = CurlController::request($url,$method,$fields);

			if($deleteModule->status == 200){

				respondJson(["status" => 200]);
			}

		}

	}

}

if(isset($_POST["idModuleDelete"])){

	$ajax = new ModulesAjax();
	$ajax -> idModuleDelete = $_POST["idModuleDelete"];
	$ajax -> token = $_POST["token"];
	$ajax -> deleteModule();
}