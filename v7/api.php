<?php
// Recuperar as variáveis de sessão
session_start();

// Expulsa quem tentar acessar a API diretamente
if (paginaAtual() == "api.php")
	header("Location: login.php");

// Esconde os erros para que não sejam mostrados em produção
error_reporting(0);

// Retorna o nível de acesso necessário para acessar a página
function areaAtual() {
	return substr($_SERVER["REQUEST_URI"], strrpos($_SERVER["REQUEST_URI"], "/") + 5, -4);
}

// Troca o token do banco de dados por outro novo e guarda em uma variável de sessão
function atualizarToken($id, $CONEXAO, $TABELA_USUARIOS) {
	$token = gerarToken();

	$_SESSION["id"] = $id;

	$atualizacao = $CONEXAO->query("UPDATE $TABELA_USUARIOS SET token = '$token' WHERE id = $id");
}

// Retorna 1 caso as dados do usuário batam com os do banco de dados
function autenticacao($nivel_pag, $SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG) {
	$CONEXAO = conectar($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA);

	if ($CONEXAO == NULL)
		return 0;

	try {
		$situacao = validacaoAutenticacao($nivel_pag, $CONEXAO, $TABELA_USUARIOS);

		if ($situacao == "É necessário fazer login: Área $nivel_pag") {
			gravarLOG(-1, $situacao, $CONEXAO, $TABELA_LOG);
			retornarMensagem($situacao);
			return 0;
		}

		if ($situacao != "Sucesso") {
			gravarLOG($_SESSION["id"], $situacao, $CONEXAO, $TABELA_LOG);
			avisarAdmin("Tentativa de acesso", $situacao, $CONEXAO, $TABELA_USUARIOS);
			gravarLOG($_SESSION["id"], "Saiu", $CONEXAO, $TABELA_LOG);
			session_destroy();
			retornarMensagem($situacao);
			return 0;
		}
		
		gravarLOG($_SESSION["id"], "Autenticado", $CONEXAO, $TABELA_LOG);
		atualizarToken($_SESSION["id"], $CONEXAO, $TABELA_USUARIOS);

		$CONEXAO = NULL;
		return 1;
	}
	catch (PDOException $e) {
		retornarMensagem("Falha no comando SQL: ".$e->getMessage());
		return 0;
	}
}

// Retorna 1 se o usuário tem um token e bate com o token do banco de dados, redireciona caso contrário
function autenticacaRedireciona($nivel_pag, $SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG) {
	$CONEXAO = conectar($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA);

	if ($CONEXAO == NULL) {
		header("Location: login.php");
		return 0;
	}

	try {
		$situacao = validacaoAutenticacao($nivel_pag, $CONEXAO, $TABELA_USUARIOS);

		if ($situacao == "É necessário fazer login: Área $nivel_pag") {
			gravarLOG(-1, $situacao, $CONEXAO, $TABELA_LOG);
			header("Location: login.php");
			return 0;
		}

		if ($situacao != "Sucesso") {
			gravarLOG($_SESSION["id"], $situacao, $CONEXAO, $TABELA_LOG);
			avisarAdmin("Tentativa de acesso", $situacao, $CONEXAO, $TABELA_USUARIOS);
			gravarLOG($_SESSION["id"], "Saiu", $CONEXAO, $TABELA_LOG);
			session_destroy();
			header("Location: login.php");
			return 0;
		}
		
		gravarLOG($_SESSION["id"], "Autenticado", $CONEXAO, $TABELA_LOG);
		atualizarToken($_SESSION["id"], $CONEXAO, $TABELA_USUARIOS);

		$CONEXAO = NULL;
		return 1;
	}
	catch (PDOException $e) {
		retornarMensagem("Falha no comando SQL: ".$e->getMessage());
		header("Location: login.php");
		return 0;
	}
}

// Manda email para todos os administradores avisando das tentativas de acesso indevidas
function avisarAdmin($titulo, $msg, $CONEXAO, $TABELA_USUARIOS) {
	$busca = $CONEXAO->query("SELECT * FROM $TABELA_USUARIOS WHERE nivel = 'Admin'");
	$admin = $busca->fetchAll();

	$tam = count($admin);
	for ($i = 0; $i < $tam; $i++)
		mail($admin[$i]["email"], $titulo, $msg);
}

// Retorna todos os dados do banco de dados que contenha o $campo igual a $valor
function buscar($CONEXAO, $TABELA, $campo, $valor) {
	$busca = $CONEXAO->prepare("SELECT * FROM $TABELA WHERE $campo = :valor");
	$busca->bindParam(':valor', $valor);
	$busca->execute();
	return $busca->fetchAll();
}

// Retorna o ID do último usuário cadastrado
function buscarID($CONEXAO) {
	$id = $CONEXAO->lastInsertId();
	$_SESSION["id"] = $id;
	return $id;
}

// Retorna nível de acesso do usuário atual
function buscarNivel($CONEXAO, $TABELA_USUARIOS) {
	if (!isset($_SESSION["id"]))
		return "Error";

	$busca = $CONEXAO->query("SELECT * FROM $TABELA_USUARIOS WHERE id = ".$_SESSION["id"]);
	$usuario = $busca->fetchAll();

	if (count($usuario) == 0)
		return "Error";

	return $usuario[0]["nivel"];
}

// Recupera e retorna todos os logs do banco de dados gerados por um determinado usuário
function buscarRelatorio($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG) {
	$id = intval($_POST["id"]);

	$CONEXAO = conectar($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA);

	if ($CONEXAO == NULL)
		return;

	try {
		$busca = $CONEXAO->query("SELECT * FROM $TABELA_LOG WHERE id_usuario = $id");
		$log = $busca->fetchAll();

		gravarLOG($_SESSION["id"], "Requisição de relatório do usuário $id", $CONEXAO, $TABELA_LOG);
		retornarRelatorioNivel($id, $log, $CONEXAO, $TABELA_USUARIOS);

		$CONEXAO = NULL;
	}
	catch (PDOException $e) {
		retornarMensagem("Falha na conexão: ".$e->getMessage());
		return;
	}
}

// Recupera e retorna os dados de um usuário do banco de dados
function buscarUsuario($email, $CONEXAO, $TABELA_USUARIOS) {
	$busca = $CONEXAO->prepare("SELECT * FROM $TABELA_USUARIOS WHERE email = :email");
	$busca->bindParam(':email', $email, PDO::PARAM_STR, 60);
	$busca->execute();
	return $busca->fetchAll();
}

// Cadastra um usuário no banco de dados ou retorna uma mensagem de erro
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

// Conecta no servidor e retorna uma referência para a conexão
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

// Retorna 1 se o usuário errou a senha pelo menos 3 vezes na última hora
function estaBloqueado($id, $CONEXAO, $TABELA_LOG) {
	$busca = $CONEXAO->query("SELECT * FROM $TABELA_LOG WHERE id_usuario = $id AND data >= NOW() - INTERVAL 1 HOUR AND operacao = 'Falha no login: Senha incorreta'");
	$tentativas = $busca->fetchAll();

	return count($tentativas) > 2;
}

// Retorna um novo token criado
function gerarToken() {
	$metodo = "aes-128-gcm";
	$chave = "7VAgEFROCBhVRS6h";

	$_SESSION["token"] = base64_encode(openssl_random_pseudo_bytes(60));
	$_SESSION["iv"] = openssl_random_pseudo_bytes(openssl_cipher_iv_length($metodo));

	$token = openssl_encrypt($_SESSION["token"], $metodo, $chave, 0, $_SESSION["iv"], $_SESSION["tag"]);

	return $token;
}

// Grava no banco de dados um determinado acontecimento
function gravarLOG($id, $msg, $CONEXAO, $TABELA_LOG) {
	$log = $CONEXAO->query("INSERT INTO $TABELA_LOG VALUES (NULL, $id, '".$_SERVER["REMOTE_ADDR"]."', '".$_SERVER["HTTP_USER_AGENT"]."', '$msg', NOW())");
}

// Armazena os dados de um novo usuário no banco de dados
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

// Verifica que os dados de um usuário batem com os do banco de dados e retorna uma mensagem
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

// Retorna o nome da página atual
function paginaAtual() {
	return substr($_SERVER["REQUEST_URI"], strrpos($_SERVER["REQUEST_URI"], "/") + 1);
}

// Retorna uma mensagem via json para uma requisição ajax
function retornarMensagem($msg) {
	echo json_encode(array("msg" => $msg));
}

// Retorna 2 mensagens via json para uma requisição ajax
function retornarMensagens($msg, $pag) {
	echo json_encode(array("msg" => $msg, "pag" => $pag));
}

// Retorna o log e o nível de acesso requerido via json para uma requisição ajax
function retornarRelatorioNivel($id, $log, $CONEXAO, $TABELA_USUARIOS) {
	$nivel;

	$busca = $CONEXAO->query("SELECT * FROM $TABELA_USUARIOS WHERE id = $id");
	$usuario = $busca->fetchAll();

	if (count($usuario) == 0)
		$nivel = "NULL";
	else
		$nivel = $usuario[0]["nivel"];

	echo json_encode(array("msg" => "Sucesso", "log" => json_encode($log), "nivel" => $nivel));
}

// Destrói todas as variáveis de sessão
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

// Retorna 1 e redireciona para a área específica do usuário caso esteja logado
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

// Trocar o nível de acesso de um usuário e retorna o novo nível de acesso
function trocarNivel($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG) {
	$id = intval($_POST["id"]);
	$nivel = trim($_POST["nivel"]);

	$CONEXAO = conectar($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA);

	if ($CONEXAO == NULL)
		return;

	try {
		$situacao = validarTrocaNivel($id, $nivel, $CONEXAO, $TABELA_USUARIOS);

		if ($situacao != "Sucesso") {
			gravarLOG($_SESSION["id"], "Falha na troca de nível de acesso de $id: $situacao", $CONEXAO, $TABELA_LOG);
			retornarMensagem($situacao);
			return;
		}

		$atualizacao = $CONEXAO->query("UPDATE $TABELA_USUARIOS SET nivel = '$nivel' WHERE id = $id");
		gravarLOG($_SESSION["id"], "Nível de acesso de $id trocado para $nivel", $CONEXAO, $TABELA_LOG);
		retornarMensagem($nivel);

		$CONEXAO = NULL;
	}
	catch (PDOException $e) {
		retornarMensagem("Falha na conexão: ".$e->getMessage());
		return;
	}
}

// Retorna uma mensagem de sucesso caso o usuário tenha um token e ele bata com o do banco de dados
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

// Retorna uma mensagem de sucesso caso o formulário tenha sido preenchido corretamente
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

// Retorna uma mensagem de sucesso caso os dados informados batam com os do banco de dados
function validacaoLogin($usuario, $senha, $CONEXAO, $TABELA_LOG) {
	if (count($usuario) == 0)
		return "Email não cadastrado";

	if (estaBloqueado($usuario[0]["id"], $CONEXAO, $TABELA_LOG))
		return "Error 403";

	if (!password_verify($senha, $usuario[0]["senha"]))
		return "Senha incorreta";

	return "Sucesso";
}

// Retorna uma mensagem de sucesso caso id e nível informados sejam validos
function validarTrocaNivel($id, $nivel, $CONEXAO, $TABELA_USUARIOS) {
	if ($nivel != "Usuario" && $nivel != "Cliente" && $nivel != "Financeiro" && $nivel != "TI" && $nivel != "Admin")
		return "Nível inexistente";

	$busca = $CONEXAO->query("SELECT * FROM $TABELA_USUARIOS WHERE id = $id");
	$usuario = $busca->fetchAll();

	if (count($usuario) == 0)
		return "Usuário inexistente";

	if ($id == $_SESSION["id"])
		return "Você não pode perder o nível de acesso de Admin";

	return "Sucesso";
}

?>