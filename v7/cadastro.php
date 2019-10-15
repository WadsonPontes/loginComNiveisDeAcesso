<?php
include "api.php";
include "constantes.php";

if (!seLogadoRedireciona($SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG)) {
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

		#nome {
			position: fixed;
			width: 50vw;
			height: 10vh;
			top: 5vh;
			left: 25vw;
			border-radius: 25px;
			font-size: 6vh;
			color: #707670;
			text-align: center;
		}

		#email {
			position: fixed;
			width: 50vw;
			height: 10vh;
			top: 20vh;
			left: 25vw;
			border-radius: 25px;
			font-size: 6vh;
			color: #707670;
			text-align: center;
		}

		#tel {
			position: fixed;
			width: 50vw;
			height: 10vh;
			top: 35vh;
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

		#senha2 {
			position: fixed;
			width: 50vw;
			height: 10vh;
			top: 65vh;
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
			top: 80vh;
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
			top: 88vh;
			left: 0vw;
			color: #707670;
			text-align: center;
			font: 4vh arial;
		}

		#login {
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
	<input type="text" placeholder="Seu nome" maxlength="60" id="nome"><br/>
	<input type="text" placeholder="exemplo@email.com" maxlength="60" id="email"><br/>
	<input type="text" placeholder="Telefone" maxlength="20" id="tel"><br/>
	<input type="password" placeholder="Uma senha" id="senha"><br/>
	<input type="password" placeholder="A senha novamente" id="senha2"><br/>
	<input type="button" value="Cadastrar" id="botao" onclick="cadastrar()">
	<a href="login.php" id="login">Fazer login</a>
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