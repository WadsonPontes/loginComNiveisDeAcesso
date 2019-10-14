<?php
session_start();

if (paginaAtual() == "api.php")
	header("Location: login.php");

// error_reporting(E_ALL);
error_reporting(0);

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

	try {
		gravarLOG($_SESSION["id"], "Saiu", $CONEXAO, $TABELA_LOG);
		session_destroy();
	}
	catch (PDOException $e) {
		retornarMensagem("Falha na conexão: ".$e->getMessage());
		return;
	}
}

function buscarRelatorio($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG) {
	$id = intval($_POST["id"]);

	$CONEXAO = conectar($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA);

	if ($CONEXAO == NULL)
		return;

	try {
		$busca = $CONEXAO->query("SELECT * FROM $TABELA_LOG WHERE id_usuario = $id");
		$log = $busca->fetchAll();

		gravarLOG($_SESSION["id"], "Requisição de relatório do usuário $id", $CONEXAO, $TABELA_LOG);
		echo json_encode($log);

		$CONEXAO = NULL;
	}
	catch (PDOException $e) {
		retornarMensagem("Falha na conexão: ".$e->getMessage());
		return;
	}
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

function paginaAtual() {
	return substr($_SERVER["REQUEST_URI"], strrpos($_SERVER["REQUEST_URI"], "/") + 1);
}

function areaAtual() {
	return substr($_SERVER["REQUEST_URI"], strrpos($_SERVER["REQUEST_URI"], "/") + 5, -4);
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

function validacaoAutenticacao($nivel_pag, $CONEXAO, $TABELA_USUARIOS) {
	$metodo = "aes-128-gcm";
	$chave = "7VAgEFROCBhVRS6h";

	if (!isset($_SESSION["id"]))
		return "É necessário fazer login: Área $nivel_pag";

	$busca = $CONEXAO->query("SELECT * FROM $TABELA_USUARIOS WHERE id = ".$_SESSION["id"]);
	$usuario = $busca->fetchAll();

	if (count($usuario) == 0)
		return "Falha na autenticação: Usuário não encontrado";

	$token_bd = openssl_decrypt($usuario[0]["token"], $metodo, $chave, 0, $_SESSION["iv"], $_SESSION["tag"]);

	if ($token_bd != $_SESSION["token"])
		return "Falha na autenticação: Sessão expirou";

	if ($nivel_pag != $usuario[0]["nivel"])
		return "Área restrita: ".$nivel_pag;

	return "Sucesso";
}

function buscarNivel($CONEXAO, $TABELA_USUARIOS) {
	if (!isset($_SESSION["id"]))
		return "Error";

	$busca = $CONEXAO->query("SELECT * FROM $TABELA_USUARIOS WHERE id = ".$_SESSION["id"]);
	$usuario = $busca->fetchAll();

	if (count($usuario) == 0)
		return "Error";

	return $usuario[0]["nivel"];
}

function seLogadoRedireciona($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS) {
	$CONEXAO = conectar($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA);

	if ($CONEXAO == NULL) {
		header("Location: login.php");
		return 0;
	}

	try {
		$nivel = buscarNivel($CONEXAO, $TABELA_USUARIOS);

		if ($nivel == "Error")
			return 0;

		$CONEXAO = NULL;

		header("Location: area".$nivel.".php");
		return 1;
	}
	catch (PDOException $e) {
		retornarMensagem("Falha no comando SQL: ".$e->getMessage());
		header("Location: login.php");
		return 0;
	}
}

function autenticacao($nivel_pag, $SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG) {
	$CONEXAO = conectar($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA);

	if ($CONEXAO == NULL) {
		header("Location: login.php");
		return 0;
	}

	try {
		$id = (isset($_SESSION["id"]) ? $_SESSION["id"] : -1);
		$situacao = validacaoAutenticacao($nivel_pag, $CONEXAO, $TABELA_USUARIOS);

		if ($situacao != "Sucesso") {
			gravarLOG($id, $situacao, $CONEXAO, $TABELA_LOG);
			avisarAdmin("Tentativa de acesso", $situacao, $CONEXAO, $TABELA_USUARIOS);
			session_destroy();
			header("Location: login.php");
			return 0;
		}
		
		gravarLOG($id, "Autenticado", $CONEXAO, $TABELA_LOG);
		atualizarToken($id, $CONEXAO, $TABELA_USUARIOS);

		$CONEXAO = NULL;
		return 1;
	}
	catch (PDOException $e) {
		retornarMensagem("Falha no comando SQL: ".$e->getMessage());
		header("Location: login.php");
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

function avisarAdmin($titulo, $msg, $CONEXAO, $TABELA_USUARIOS) {
	$busca = $CONEXAO->query("SELECT * FROM $TABELA_USUARIOS WHERE nivel = 'Admin'");
	$admin = $busca->fetchAll();

	$tam = count($admin);
	for ($i = 0; $i < $tam; $i++)
		mail($admin[$i]["email"], $titulo, $msg);
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