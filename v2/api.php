<?php
date_default_timezone_set("America/Fortaleza");
// error_reporting(0);
error_reporting(E_ALL);

$SERVIDOR = "localhost";
$BANCO_DE_DADOS = "teste";
$USUARIO = "root";
$SENHA = "";
$TABELA_USUARIOS = "usuarios";
$TABELA_LOG = "log";
$CONEXAO;

$operacao = $_POST["operacao"];

if ($operacao == "Cadastro")
	cadastrar();
else if ($operacao == "Login")
	login();

function conectar() {
	global $SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG, $CONEXAO;

	try {
		$CONEXAO = new PDO(sprintf("mysql:host=%s;dbname=%s;charset=utf8", $SERVIDOR, $BANCO_DE_DADOS), $USUARIO, $SENHA, array(PDO::ATTR_PERSISTENT => true));
		$CONEXAO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$atualizacao = $CONEXAO->query("SET time_zone = '-03:00'");
	}
	catch (PDOException $e) {
		echo json_encode(array("msg" => "Falha na conexão: " . $e->getMessage()));
	}
}

function retirarEspacos(&$nome, &$email, &$tel) {
	$nome = trim($nome);
	$email = trim($email);
	$tel = trim($tel);
}

function validacao($nome, $email, $tel, $nivel, $senha, $senha2) {
	if (mb_strlen($nome) < 5) {
		echo json_encode(array("msg" => "Nome deve conter no mínimo 5 dígitos"));
		return 0;
	}

	else if (mb_strlen($nome) > 60) {
		echo json_encode(array("msg" => "Nome deve conter no máximo 60 dígitos"));
		return 0;
	}

	else if (!preg_match("/^[a-zA-Z ]*$/", $nome)) {
		echo json_encode(array("msg" => "O nome deve conter apenas letras e espaços"));
		return 0;
	}

	else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		echo json_encode(array("msg" => "Insira um email valido"));
		return 0;
	}

	else if (!preg_match("/^[0-9]*$/", $tel) || mb_strlen($tel) < 10 || mb_strlen($tel) > 13) {
		echo json_encode(array("msg" => "Digite apenas numeros no telefone incluindo o DDD"));
		return 0;
	}

	else if (mb_strlen($senha) < 8) {
		echo json_encode(array("msg" => "A senha deve conter no mínimo 8 dígitos"));
		return 0;
	}
	else if (!preg_match("/[a-z]/", $senha)) {
		echo json_encode(array("msg" => "A senha deve conter pelo menos uma letra minuscula"));
		return 0;
	}
	else if (!preg_match("/[A-Z]/", $senha)) {
		echo json_encode(array("msg" => "A senha deve conter pelo menos uma letra maiuscula"));
		return 0;
	}
	else if (!preg_match("/[0-9]/", $senha)) {
		echo json_encode(array("msg" => "A senha deve conter pelo menos um número"));
		return 0;
	}
	else if ($senha != $senha2) {
		echo json_encode(array("msg" => "Senhas não correspondem"));
		return 0;
	}

	return 1;
}

function cadastrar() {
	global $SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG, $CONEXAO;

	$nome = $_POST["nome"];
	$email = $_POST["email"];
	$tel = $_POST["tel"];
	$nivel = $_POST["nivel"];
	$senha = $_POST["senha"];
	$senha2 = $_POST["senha2"];

	retirarEspacos($nome, $email, $tel);

	if (!validacao($nome, $email, $tel, $nivel, $senha, $senha2)) {
		conectar();
		$log = $CONEXAO->query("INSERT INTO $TABELA_LOG VALUES (NULL, -1, '".$_SERVER["REMOTE_ADDR"]."', '".$_SERVER["HTTP_USER_AGENT"]."', 'Falha no cadastro', NOW())");
		return;
	}

	$senha = password_hash($senha, PASSWORD_DEFAULT);

	try {
		conectar();

		$insercao = $CONEXAO->prepare("INSERT INTO $TABELA_USUARIOS VALUES (NULL, :nome, :email, :tel, :nivel, :senha)");

		$insercao->bindParam(':nome', $nome, PDO::PARAM_STR, 60);
    	$insercao->bindParam(':email', $email, PDO::PARAM_STR, 60);
    	$insercao->bindParam(':tel', $tel, PDO::PARAM_STR, 60);
    	$insercao->bindParam(':nivel', $nivel, PDO::PARAM_STR, 60);
    	$insercao->bindParam(':senha', $senha, PDO::PARAM_STR, 60);

    	$insercao->execute();
    	$log = $CONEXAO->query("INSERT INTO $TABELA_LOG VALUES (NULL, ".$CONEXAO->lastInsertId().", '".$_SERVER["REMOTE_ADDR"]."', '".$_SERVER["HTTP_USER_AGENT"]."', 'Cadastro', NOW())");

		$CONEXAO = null;

		echo json_encode(array("msg" => "Sucesso"));
	}
	catch (PDOException $e) {
		echo json_encode(array("msg" => "Falha na conexão: " . $e->getMessage()));
	}
}

function login() {
	global $SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG, $CONEXAO;

	$email = $_POST["email"];
	$senha = $_POST["senha"];

	try {
		conectar();

		$selecao = $CONEXAO->prepare("SELECT * FROM $TABELA_USUARIOS WHERE email = :email");
    	$selecao->bindParam(':email', $email, PDO::PARAM_STR, 60);
    	$selecao->execute();
    	$dados = $selecao->fetchAll();

		if (count($dados) == 0) {
			$log = $CONEXAO->query("INSERT INTO $TABELA_LOG VALUES (NULL, -1, '".$_SERVER["REMOTE_ADDR"]."', '".$_SERVER["HTTP_USER_AGENT"]."', 'Falha no login: email incorreto', NOW())");
			echo json_encode(array("msg" => "Email não cadastrado"));
		}
		else if (!password_verify($senha, $dados[0]["senha"])) {
			$log = $CONEXAO->query("INSERT INTO $TABELA_LOG VALUES (NULL, ".$dados[0]["id"].", '".$_SERVER["REMOTE_ADDR"]."', '".$_SERVER["HTTP_USER_AGENT"]."', 'Falha no login: senha incorreta', NOW())");
			echo json_encode(array("msg" => "Senha incorreta"));
		}
		else
			echo json_encode(array("msg" => "Sucesso no login"));

		$CONEXAO = null;
	}
	catch (PDOException $e) {
		echo json_encode(array("msg" => "Falha na conexão: " . $e->getMessage()));
	}
}

?>