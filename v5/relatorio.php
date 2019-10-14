<?php
include "api.php";

$SERVIDOR = "localhost";
$BANCO_DE_DADOS = "teste";
$USUARIO = "root";
$SENHA = "";

$TABELA_USUARIOS = "usuarios";
$TABELA_LOG = "log";

if (!isset($_POST["id"]))
	header("Location: login.php");
else if (autenticacao("Admin", $SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG))
	buscarRelatorio($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG);

?>