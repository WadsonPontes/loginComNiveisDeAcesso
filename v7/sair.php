<?php
include "api.php";
include "constantes.php";

if (!isset($_POST["msg"]))
	header("Location: login.php");
else
	sair($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_LOG);

?>