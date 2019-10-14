<?php
include "api.php";
include "constantes.php";

if (!isset($_POST["id"]))
	header("Location: login.php");
else if (autenticacao("Admin", $SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG))
	buscarRelatorio($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG);

?>