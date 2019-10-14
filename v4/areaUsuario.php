<?php
include "api.php";

$SERVIDOR = "localhost";
$BANCO_DE_DADOS = "teste";
$USUARIO = "root";
$SENHA = "";

$TABELA_USUARIOS = "usuarios";
$TABELA_LOG = "log";


if (autenticado("Usuario", $SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $CONEXAO) == "Sucesso") {
	atualizarToken($_SESSION["id"], $CONEXAO, $TABELA_USUARIOS);
}
else
	header("Location: login.html");

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title></title>
</head>
<body>
	<p id="msg">Área exclusiva para usuários. Seja bem-vindo!</p>
	<input type="button" value="Sair" onclick="sair()">

	<script type="text/javascript">
		const ARQUIVO = "sair.php";
		const msg = document.getElementById("msg");

		function sair() {
			let xhttp = new XMLHttpRequest();

			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					console.log(this.responseText);
					retorno();
				}
			};
			
			xhttp.open("POST", ARQUIVO, true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.send();
		}

		function retorno() {
			window.location.href = "login.php";
		}
	</script>
</body>
</html>