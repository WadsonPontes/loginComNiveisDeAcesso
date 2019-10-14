<?php
include "api.php";

$SERVIDOR = "localhost";
$BANCO_DE_DADOS = "teste";
$USUARIO = "root";
$SENHA = "";

$TABELA_USUARIOS = "usuarios";
$TABELA_LOG = "log";

if (!isset($_POST["email"]))
	header("Location: login.php");
else
	login($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG);

?>