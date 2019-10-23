<?php

$IP = filtrar($_SERVER["REMOTE_ADDR"]);
$INFO = filtrar($_SERVER["HTTP_USER_AGENT"]);

$SERVIDOR = "localhost";
$USUARIO = "root";
$SENHA = "";
$BANCO_DE_DADOS = "teste";

$TABELA_USUARIOS = "usuarios";
$TABELA_LOG = "log";

$CONEXAO = conectar();

function conectar() {
	global $CONEXAO;

	$CONEXAO = mysqli_connect($SERVIDOR, $USUARIO, $SENHA, $BANCO_DE_DADOS) or exit;
}

function fecharConexao() {
	global $CONEXAO;

	mysqli_close($CONEXAO);
}

function inserirUsuario($nome, $email, $tel, $nivel, $senha) {
	global $CONEXAO, $TABELA_USUARIOS;

	$sql = "INSERT INTO $TABELA_USUARIOS (nome, email, tel, nivel, senha) VALUES ('$nome', 'email', 'tel', 'nivel', 'senha')";

	if (!mysqli_query($CONEXAO, $sql)) {
		gravarLOG(-1, mysqli_error($CONEXAO));
		exit;
	}
}

function gravarLOG($id, $msg) {
	global $CONEXAO, $TABELA_LOG;

	$sql = "INSERT INTO $TABELA_LOG (id_usuario, ip_usuario, info_usuario, operacao) VALUES ($id, '$IP', '$INFO', '$msg')";

	if (!mysqli_query($CONEXAO, $sql)) or exit;
}

?>