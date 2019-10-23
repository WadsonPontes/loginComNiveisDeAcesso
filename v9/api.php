<?php

$SERVIDOR = "localhost";
$USUARIO = "root";
$SENHA = "";
$BANCO_DE_DADOS = "teste";

$TABELA_USUARIOS = "usuarios";
$TABELA_LOG = "log";

$CONEXAO;

conectar();

$IP = filtrar($_SERVER["REMOTE_ADDR"]);
$INFO = filtrar($_SERVER["HTTP_USER_AGENT"]);

echo var_dump(buscarUsuario(filtrar("\' OR 1 #")));

function conectar() {
	global $SERVIDOR, $USUARIO, $SENHA, $BANCO_DE_DADOS, $CONEXAO;

	$CONEXAO = mysqli_connect($SERVIDOR, $USUARIO, $SENHA, $BANCO_DE_DADOS) or exit;
	mysql_set_charset('utf8', $CONEXAO) or exit;
}

function fecharConexao() {
	global $CONEXAO;

	mysqli_close($CONEXAO);
}

function ultimoID() {
	global $CONEXAO;

	return mysqli_insert_id($CONEXAO);
}

function buscarUsuario($email) {
	global $CONEXAO, $TABELA_USUARIOS;

	$sql = "SELECT * FROM $TABELA_USUARIOS WHERE email = '$email'";
	$res = mysqli_query($CONEXAO, $sql);

	return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function inserirUsuario($nome, $email, $tel, $nivel, $senha) {
	global $CONEXAO, $TABELA_USUARIOS;

	$sql = "INSERT INTO $TABELA_USUARIOS (nome, email, tel, nivel, senha) VALUES ('$nome', '$email', '$tel', '$nivel', '$senha')";

	if (!mysqli_query($CONEXAO, $sql)) {
		gravarLOG(-1, mysqli_connect_error());
		exit;
	}
}


function gravarLOG($id, $msg) {
	global $CONEXAO, $TABELA_LOG;

	$sql = "INSERT INTO $TABELA_LOG (id_usuario, ip_usuario, info_usuario, operacao) VALUES ($id, '$IP', '$INFO', '$msg')";

	mysqli_query($CONEXAO, $sql) or exit;
}

function filtrar($texto) {
	global $CONEXAO;

	$texto = mysqli_real_escape_string($CONEXAO, $texto); // SQL Injection
	$texto = htmlspecialchars($texto); // XSS (Cross Site Scripting)

	return $texto;
}

?>