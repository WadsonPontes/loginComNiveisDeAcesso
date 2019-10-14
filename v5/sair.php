<?php
include "api.php";

$SERVIDOR = "localhost";
$BANCO_DE_DADOS = "teste";
$USUARIO = "root";
$SENHA = "";

$TABELA_USUARIOS = "usuarios";
$TABELA_LOG = "log";

if (!isset($_POST["msg"]))
	header("Location: login.php");
else
	sair($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_LOG);

?>