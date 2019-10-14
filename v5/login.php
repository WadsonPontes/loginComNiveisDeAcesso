<?php
include "api.php";

$SERVIDOR = "localhost";
$BANCO_DE_DADOS = "teste";
$USUARIO = "root";
$SENHA = "";

$TABELA_USUARIOS = "usuarios";
$TABELA_LOG = "log";

if (!seLogadoRedireciona($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS)) {
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title></title>
</head>
<body>
	Email:<input type="text" id="email"><br/>
	Senha:<input type="password" id="senha"><br/>
	<input type="button" value="Login" onclick="login()">
	<a href="cadastro.php">Criar conta</a>
	<p id="msg"></p>

	<script type="text/javascript">
		const ARQUIVO = "logar.php";
		const email = document.getElementById("email");
		const senha = document.getElementById("senha");
		const msg = document.getElementById("msg");

		function login() {
			let xhttp = new XMLHttpRequest();
			
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					retorno(JSON.parse(this.responseText));
				}
			};
			xhttp.open("POST", ARQUIVO, true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.send("email=" + encodeURIComponent(email.value) + "&senha=" + encodeURIComponent(senha.value));
		}

		function retorno(r) {
			if (r.msg == "Sucesso")
				window.location.href = r.pag;
			else
				msg.innerHTML = r.msg;
		}
	</script>
</body>
</html>
<?php
}
?>