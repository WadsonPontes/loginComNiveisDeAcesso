<?php
include "api.php";
include "constantes.php";

if (!isset($_POST["email"]))
	header("Location: login.php");
else
	login($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG);

?>