<?php
include "api.php";

$SERVIDOR = "localhost";
$BANCO_DE_DADOS = "teste";
$USUARIO = "root";
$SENHA = "";

$TABELA_USUARIOS = "usuarios";
$TABELA_LOG = "log";

sair($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_LOG);

?>