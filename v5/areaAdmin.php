<?php
include "api.php";

$SERVIDOR = "localhost";
$BANCO_DE_DADOS = "teste";
$USUARIO = "root";
$SENHA = "";

$TABELA_USUARIOS = "usuarios";
$TABELA_LOG = "log";

if (autenticacao(areaAtual(), $SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG)) {
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title></title>
	<style>
		table {
			width:100%;
		}
		table, th, td {
			border: 1px solid black;
			border-collapse: collapse;
		}
		th, td {
			padding: 15px;
			text-align: left;
		}
		tr:nth-child(even) {
			background-color: #eee;
		}
		tr:nth-child(odd) {
			background-color: #fff;
		}
		th {
			background-color: black;
			color: white;
		}
	</style>
</head>
<body>
	<p>Para receber um relatório de acessos digite o ID do usuário</p>
	ID:<input type="text" id="id_usuario">
	<input type="button" value="Buscar" onclick="buscar()"><br/><br/>
	<div id="msg"></div><br/><br/>
	<input type="button" value="Sair" onclick="sair()">

	<script type="text/javascript">
		const ARQUIVO_SAIR = "sair.php";
		const ARQUIVO_RELATORIO = "relatorio.php";
		const id_usuario = document.getElementById("id_usuario");
		const msg = document.getElementById("msg");

		function sair() {
			let xhttp = new XMLHttpRequest();

			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					saiu();
				}
			};
			
			xhttp.open("POST", ARQUIVO_SAIR, true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.send("msg=sair");
		}

		function buscar() {
			let xhttp = new XMLHttpRequest();

			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					relatorio(JSON.parse(this.responseText));
				}
			};
			
			xhttp.open("POST", ARQUIVO_RELATORIO, true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.send("id=" + id_usuario.value);
		}

		function saiu() {
			window.location.href = "login.php";
		}

		function relatorio(r) {
			let m = r.length;
			let tabela = "<table><tr><th>ID</th><th>ID USUÁRIO</th><th>IP USUÁRIO</th><th>INFO USUÁRIO</th><th>OPERACAO</th><th>DATA</th></tr>";

			for (let i = 0; i < m; i++) {
				tabela += "<tr><td>" + r[i]["id"] + "</td><td>" + r[i]["id_usuario"] + "</td><td>" + r[i]["ip_usuario"] + "</td><td>" + r[i]["info_usuario"] + "</td><td>" + r[i]["operacao"] + "</td><td>" + r[i]["data"] + "</td></tr>";
			}

			tabela += "</table>";

			msg.innerHTML = tabela;
		}
	</script>
</body>
</html>
<?php
}
?>