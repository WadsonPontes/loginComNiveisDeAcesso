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
	Nome:<input type="text" id="nome"><br/>
	Email:<input type="text" id="email"><br/>
	Telefone:<input type="text" id="tel"><br/>
	Senha:<input type="password" id="senha"><br/>
	Senha novamente:<input type="password" id="senha2"><br/>
	<input type="button" value="Cadastrar" onclick="cadastrar()">
	<a href="login.php">Fazer login</a>
	<p id="msg"></p>

	<script type="text/javascript">
		const ARQUIVO = "cadastrar.php";
		const nome = document.getElementById("nome");
		const email = document.getElementById("email");
		const tel = document.getElementById("tel");
		const senha = document.getElementById("senha");
		const senha2 = document.getElementById("senha2");
		const msg = document.getElementById("msg");

		function cadastrar() {
			let xhttp = new XMLHttpRequest();
			
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					retorno(JSON.parse(this.responseText));
				}
			};
			xhttp.open("POST", ARQUIVO, true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.send("nome=" + encodeURIComponent(nome.value) + "&email=" + encodeURIComponent(email.value) + "&tel=" + encodeURIComponent(tel.value) + "&senha=" + encodeURIComponent(senha.value) + "&senha2=" + encodeURIComponent(senha2.value));
		}

		function retorno(r) {
			if (r.msg == "Sucesso")
				window.location.href = "login.php";
			else
				msg.innerHTML = r.msg;
		}
	</script>
</body>
</html>
<?php
}
?>