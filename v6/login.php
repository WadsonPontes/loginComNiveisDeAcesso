<?php
include "api.php";
include "constantes.php";

if (!seLogadoRedireciona($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS)) {
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title></title>
	<style type="text/css">
		body {
			width: 100vw;
		    height: 100vh;
		    margin: 0;
		    padding: 0;
		    background-color: #C8C8FF;
		    word-break: break-all;
		}

		::placeholder {
			color: #c0c4bc;
			opacity: 1; /* Firefox */
		}

		#email {
			position: fixed;
			width: 50vw;
			height: 10vh;
			top: 30vh;
			left: 25vw;
			border-radius: 25px;
			font-size: 6vh;
			color: #707670;
			text-align: center;
		}

		#senha {
			position: fixed;
			width: 50vw;
			height: 10vh;
			top: 50vh;
			left: 25vw;
			border-radius: 25px;
			font-size: 6vh;
			color: #707670;
			text-align: center;
		}

		#botao {
			position: fixed;
			width: 50vw;
			height: 10vh;
			top: 68vh;
			left: 25vw;
			font-size: 5vh;
			color: #505660;
			border-radius: 25px;
			background-color: #92a8d1;
		}

		#botao:hover {
			cursor: pointer;
		}

		#msg {
			position: fixed;
			width: 100vw;
			height: 8vh;
			top: 75vh;
			left: 0vw;
			color: #707670;
			text-align: center;
			font: 4vh arial;
		}

		#criar {
			position: fixed;
			width: 30vw;
			height: 8vh;
			top: 0vh;
			right: 1vw;
			color: #707670;
			text-align: right;
			font: 5vh arial;
		}
	</style>
</head>
<body>
	<input type="text" placeholder="exemplo@email.com" maxlength="60" id="email"><br/>
	<input type="password" placeholder="Sua senha" id="senha"><br/>
	<input type="button" value="Login" id="botao" onclick="login()">
	<a href="cadastro.php" id="criar">Criar conta</a>
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