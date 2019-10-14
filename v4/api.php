<?php
session_start();
if (!isset($_POST["email"]) && !isset($_SESSION["token"])) header("Location: login.html");

date_default_timezone_set("America/Fortaleza");
error_reporting(E_ALL);
// error_reporting(0);

function cadastrar($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG) {
	$nome = trim($_POST["nome"]);
	$email = trim($_POST["email"]);
	$tel = trim($_POST["tel"]);
	$nivel = "Usuario";
	$senha = $_POST["senha"];
	$senha2 = $_POST["senha2"];

	$CONEXAO = conectar($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA);

	if ($CONEXAO == NULL)
		return;

	try {
		$situacao = validacaoCadastro($nome, $email, $tel, $senha, $senha2);

		if ($situacao != "Sucesso") {
			gravarLOG(-1, "Falha no cadastro: $situacao", $CONEXAO, $TABELA_LOG);
			retornarMensagem($situacao);
			return;
		}

		$token = gerarToken();
		inserirUsuario($nome, $email, $tel, $nivel, $senha, $token, $CONEXAO, $TABELA_USUARIOS);
    	$id = buscarID($CONEXAO);

    	gravarLOG($id, "Cadastro", $CONEXAO, $TABELA_LOG);
    	retornarMensagem("Sucesso");

		$CONEXAO = NULL;
	}
	catch (PDOException $e) {
		retornarMensagem("Falha no comando SQL: ".$e->getMessage());
	}
}

function login($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG) {
	$email = trim($_POST["email"]);
	$senha = $_POST["senha"];

	$CONEXAO = conectar($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA);

	if ($CONEXAO == NULL)
		return;

	try {
		$usuario = buscarUsuario($email, $CONEXAO, $TABELA_USUARIOS);
		$id = count($usuario) > 0 ? $usuario[0]["id"] : -1;

		$situacao = validacaoLogin($usuario, $senha, $CONEXAO, $TABELA_LOG);

		if ($situacao != "Sucesso") {
			gravarLOG($id, "Falha no login: $situacao", $CONEXAO, $TABELA_LOG);
			retornarMensagem($situacao);
			return;
		}

		gravarLOG($id, "Login", $CONEXAO, $TABELA_LOG);
		atualizarToken($id, $CONEXAO, $TABELA_USUARIOS);
		retornarMensagens("Sucesso", "area".$usuario[0]["nivel"].".php");

		$CONEXAO = NULL;
	}
	catch (PDOException $e) {
		retornarMensagem("Falha no comando SQL: ".$e->getMessage());
	}
}

function sair($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_LOG) {
	$CONEXAO = conectar($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA);

	if ($CONEXAO == NULL)
		return;

	gravarLOG($_SESSION["id"], "Saiu", $CONEXAO, $TABELA_LOG);
	session_destroy();
}

function conectar($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA) {
	try {
		$CONEXAO = new PDO(sprintf("mysql:host=%s;dbname=%s;charset=utf8", $SERVIDOR, $BANCO_DE_DADOS), $USUARIO, $SENHA);
		$CONEXAO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$atualizacao = $CONEXAO->query("SET time_zone = '-03:00'");
		return $CONEXAO;
	}
	catch (PDOException $e) {
		retornarMensagem("Falha na conexão: ".$e->getMessage());
		return NULL;
	}
}

function validacaoCadastro($nome, $email, $tel, $senha, $senha2) {
	if (mb_strlen($nome) < 5)
		return "Nome deve conter no mínimo 5 dígitos";

	if (mb_strlen($nome) > 60)
		return "Nome deve conter no máximo 60 dígitos";

	if (!preg_match("/^[a-zA-Z ]*$/", $nome))
		return "O nome deve conter apenas letras e espaços";

	if (!filter_var($email, FILTER_VALIDATE_EMAIL))
		return "Insira um email valido";

	if (!preg_match("/^[0-9]*$/", $tel) || mb_strlen($tel) < 10 || mb_strlen($tel) > 13)
		return "Digite apenas números no telefone incluindo o DDD";

	if (mb_strlen($senha) < 8)
		return "A senha deve conter no mínimo 8 dígitos";

	if (!preg_match("/[a-z]/", $senha))
		return "A senha deve conter pelo menos uma letra minuscula";

	if (!preg_match("/[A-Z]/", $senha))
		return "A senha deve conter pelo menos uma letra maiuscula";

	if (!preg_match("/[0-9]/", $senha))
		return "A senha deve conter pelo menos um número";

	if ($senha != $senha2)
		return "Senhas não correspondem";

	return "Sucesso";
}

function gerarToken() {
	$metodo = "aes-128-gcm";
	$chave = "7VAgEFROCBhVRS6h";

	$_SESSION["token"] = base64_encode(openssl_random_pseudo_bytes(60));
	$_SESSION["iv"] = openssl_random_pseudo_bytes(openssl_cipher_iv_length($metodo));

	$token = openssl_encrypt($_SESSION["token"], $metodo, $chave, 0, $_SESSION["iv"], $_SESSION["tag"]);

	return $token;
}

function atualizarToken($id, $CONEXAO, $TABELA_USUARIOS) {
	$token = gerarToken();

	$_SESSION["id"] = $id;

	$atualizacao = $CONEXAO->query("UPDATE $TABELA_USUARIOS SET token = '$token' WHERE id = $id");
}

function autenticado($nivel, $SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, &$CONEXAO) {
	$metodo = "aes-128-gcm";
	$chave = "7VAgEFROCBhVRS6h";

	$CONEXAO = conectar($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA);

	if ($CONEXAO == NULL)
		return;

	try {
		$busca = $CONEXAO->query("SELECT * FROM $TABELA_USUARIOS WHERE id = ".$_SESSION["id"]);
		$usuario = $busca->fetchAll();

		$token = openssl_decrypt($usuario[0]["token"], $metodo, $chave, 0, $_SESSION["iv"], $_SESSION["tag"]);

		if ($token != $_SESSION["token"])
			return "Falha na autenticação";

		if ($nivel != $usuario[0]["nivel"])
			return "Área restrita";

		return "Sucesso";
	}
	catch (PDOException $e) {
		retornarMensagem("Falha no comando SQL: ".$e->getMessage());
		return 0;
	}
}

function buscarID($CONEXAO) {
	$id = $CONEXAO->lastInsertId();
	$_SESSION["id"] = $id;
	return $id;
}

function gravarLOG($id, $msg, $CONEXAO, $TABELA_LOG) {
	$log = $CONEXAO->query("INSERT INTO $TABELA_LOG VALUES (NULL, $id, '".$_SERVER["REMOTE_ADDR"]."', '".$_SERVER["HTTP_USER_AGENT"]."', '$msg', NOW())");
}

function retornarMensagem($msg) {
	echo json_encode(array("msg" => $msg));
}

function retornarMensagens($msg, $pag) {
	echo json_encode(array("msg" => $msg, "pag" => $pag));
}

function inserirUsuario($nome, $email, $tel, $nivel, $senha, $token, $CONEXAO, $TABELA_USUARIOS) {
	$insercao = $CONEXAO->prepare("INSERT INTO $TABELA_USUARIOS VALUES (NULL, :nome, :email, :tel, :nivel, :senha, :token)");

	$senha = password_hash($senha, PASSWORD_DEFAULT);

	$insercao->bindParam(':nome', $nome, PDO::PARAM_STR, 60);
	$insercao->bindParam(':email', $email, PDO::PARAM_STR, 60);
	$insercao->bindParam(':tel', $tel, PDO::PARAM_STR, 60);
	$insercao->bindParam(':nivel', $nivel, PDO::PARAM_STR, 60);
	$insercao->bindParam(':senha', $senha, PDO::PARAM_STR, 60);
	$insercao->bindParam(':token', $token, PDO::PARAM_STR, 256);

	$insercao->execute();
}

function buscarUsuario($email, $CONEXAO, $TABELA_USUARIOS) {
	$busca = $CONEXAO->prepare("SELECT * FROM $TABELA_USUARIOS WHERE email = :email");
	$busca->bindParam(':email', $email, PDO::PARAM_STR, 60);
	$busca->execute();
	return $busca->fetchAll();
}

function estaBloqueado($id, $CONEXAO, $TABELA_LOG) {
	$busca = $CONEXAO->query("SELECT * FROM $TABELA_LOG WHERE id_usuario = $id AND data >= NOW() - INTERVAL 1 HOUR AND operacao = 'Falha no login: Senha incorreta'");
	$tentativas = $busca->fetchAll();

	return count($tentativas) > 2;
}

function avisarAdmin($CONEXAO, $TABELA_USUARIOS) {
	$busca = $CONEXAO->query("SELECT * FROM $TABELA_USUARIOS WHERE nivel = 'Admin'");
	$admin = $busca->fetchAll();

	$tam = count($admin);
	for ($i = 0; $i < $tam; $i++)
		mail($admin[$i]["email"], "Error 403", "Alguém tentou fazer login e errou a senha mais de 3 vezes.");
}

function validacaoLogin($usuario, $senha, $CONEXAO, $TABELA_LOG) {
	if (count($usuario) == 0)
		return "Email não cadastrado";

	if (estaBloqueado($usuario[0]["id"], $CONEXAO, $TABELA_LOG))
		return "Error 403";

	if (!password_verify($senha, $usuario[0]["senha"]))
		return "Senha incorreta";

	return "Sucesso";
}

?>