<?php
date_default_timezone_set("America/Fortaleza");
// error_reporting(0);
error_reporting(E_ALL);

$SERVIDOR = "localhost";
$BANCO_DE_DADOS = "teste";
$USUARIO = "root";
$SENHA = "";
$TABELA_USUARIOS = "usuarios";
$CONEXAO;

$operacao = $_POST["operacao"];

if ($operacao == "Cadastro")
	cadastrar();
else if ($operacao == "Login")
	login();

function conectar() {
	global $SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $CONEXAO;

	try {
		$CONEXAO = new PDO(sprintf("mysql:host=%s;dbname=%s;charset=utf8", $SERVIDOR, $BANCO_DE_DADOS), $USUARIO, $SENHA);
		$CONEXAO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$atualizacao = $CONEXAO->query("SET time_zone = '-03:00'");
	}
	catch (PDOException $e) {
		echo json_encode(array("msg" => "Falha na conexão: " . $e->getMessage()));
	}
}

function cadastrar() {
	global $SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $CONEXAO;

	$nome = $_POST["nome"];
	$email = $_POST["email"];
	$tel = $_POST["tel"];
	$nivel = $_POST["nivel"];
	$senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);

	try {
		conectar();

		$insercao = $CONEXAO->prepare("INSERT INTO $TABELA_USUARIOS VALUES (NULL, :nome, :email, :tel, :nivel, :senha)");
		$insercao->bindParam(':nome', $nome, PDO::PARAM_STR, 60);
    	$insercao->bindParam(':email', $email, PDO::PARAM_STR, 60);
    	$insercao->bindParam(':tel', $tel, PDO::PARAM_STR, 60);
    	$insercao->bindParam(':nivel', $nivel, PDO::PARAM_STR, 60);
    	$insercao->bindParam(':senha', $senha, PDO::PARAM_STR, 60);
    	$insercao->execute();
		$CONEXAO = null;
		echo json_encode(array("msg" => "Sucesso"));
	}
	catch (PDOException $e) {
		echo json_encode(array("msg" => "Falha na conexão: " . $e->getMessage()));
	}
}

function login() {
	global $SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $CONEXAO;

	$email = $_POST["email"];
	$senha = $_POST["senha"];

	try {
		conectar();

		$selecao = $CONEXAO->prepare("SELECT * FROM $TABELA_USUARIOS WHERE email = :email");
    	$selecao->bindParam(':email', $email, PDO::PARAM_STR, 60);
    	$selecao->execute();
    	$dados = $selecao->fetchAll();
		$CONEXAO = null;

		if (count($dados) == 0)
			echo json_encode(array("msg" => "Falha no login, email não cadastrado"));
		else {
			if (password_verify($senha, $dados[0]["senha"]))
				echo json_encode(array("msg" => "Sucesso no login"));
			else
				echo json_encode(array("msg" => "Falha no login, senha incorreta"));
		}
	}
	catch (PDOException $e) {
		echo json_encode(array("msg" => "Falha na conexão: " . $e->getMessage()));
	}
}

?>