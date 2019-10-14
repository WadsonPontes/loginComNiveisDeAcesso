<?php
include "api.php";
include "constantes.php";

if (!isset($_POST["email"]))
	header("Location: cadastro.php");
else
	cadastrar($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG);

?>