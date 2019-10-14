<?php
include "api.php";
include "constantes.php";

if (autenticacaRedireciona(areaAtual(), $SERVIDOR, $BANCO_DE_DADOS, $USUARIO, $SENHA, $TABELA_USUARIOS, $TABELA_LOG)) {
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title></title>
	<style>
		body {
		    background-color: #C8C8FF;
		    word-break: break-all;
		}

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
		#sair {
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
	<p>Para receber um relatório de acessos ou alterar o nível de acesso digite o ID do usuário</p>
	ID:<input type="text" id="id_usuario">
	<input type="button" value="Buscar" onclick="buscar()">

	<input type="button" value="Usuario" disabled onclick="trocarNivel(this)" id="b_usuario">
	<input type="button" value="Cliente" disabled onclick="trocarNivel(this)" id="b_cliente">
	<input type="button" value="Financeiro" disabled onclick="trocarNivel(this)" id="b_financeiro">
	<input type="button" value="TI" disabled onclick="trocarNivel(this)" id="b_ti">
	<input type="button" value="Admin" disabled onclick="trocarNivel(this)" id="b_admin">

	<br/><br/><div id="msg"></div><br/><br/>
	<a href="javascript:sair()" id="sair">Sair</a>

	<script type="text/javascript">
		const ARQUIVO_SAIR = "sair.php";
		const ARQUIVO_RELATORIO = "relatorio.php";
		const ARQUIVO_TROCAR_NIVEL = "trocarNivel.php";
		const id_usuario = document.getElementById("id_usuario");
		const msg = document.getElementById("msg");
		const b_usuario = document.getElementById("b_usuario");
		const b_cliente = document.getElementById("b_cliente");
		const b_financeiro = document.getElementById("b_financeiro");
		const b_ti = document.getElementById("b_ti");
		const b_admin = document.getElementById("b_admin");

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

		function trocarNivel(botao) {
			let xhttp = new XMLHttpRequest();

			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					receberMudanca(JSON.parse(this.responseText));
				}
			};
			
			xhttp.open("POST", ARQUIVO_TROCAR_NIVEL, true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.send("id=" + id_usuario.value + "&nivel=" + botao.value);
		}

		function saiu() {
			window.location.href = "login.php";
		}

		function relatorio(r) {
			if (r.msg != "Sucesso")
				window.location.href = "login.php";
			else {
				let log = JSON.parse(r.log);
				let m = log.length;
				let tabela = "<table><tr><th>ID</th><th>ID USUÁRIO</th><th>IP USUÁRIO</th><th>INFO USUÁRIO</th><th>OPERACAO</th><th>DATA</th></tr>";

				for (let i = 0; i < m; i++) {
					tabela += "<tr><td>" + log[i]["id"] + "</td><td>" + log[i]["id_usuario"] + "</td><td>" + log[i]["ip_usuario"] + "</td><td>" + log[i]["info_usuario"] + "</td><td>" + log[i]["operacao"] + "</td><td>" + log[i]["data"] + "</td></tr>";
				}

				tabela += "</table>";

				msg.innerHTML = tabela;

				trocarBotoes(r.nivel);
			}
		}

		function receberMudanca(r) {
			if (r.msg == "Nível inexistente" || r.msg == "Usuário inexistente" || r.msg == "Você não pode perder o nível de acesso de Admin")
				msg.innerHTML = r.msg + "<br/>" + msg.innerHTML;
			else if (r.msg != "Usuario" && r.msg != "Cliente" && r.msg != "Financeiro" && r.msg != "TI" && r.msg != "Admin")
				window.location.href = "login.php";
			else
				trocarBotoes(r.msg);
		}

		function trocarBotoes(nivel) {
			if (nivel == "NULL") {
				b_usuario.disabled = true;
				b_cliente.disabled = true;
				b_financeiro.disabled = true;
				b_ti.disabled = true;
				b_admin.disabled = true;
			}
			else if (nivel == "Usuario") {
				b_usuario.disabled = true;
				b_cliente.disabled = false;
				b_financeiro.disabled = false;
				b_ti.disabled = false;
				b_admin.disabled = false;
			}
			else if (nivel == "Cliente") {
				b_usuario.disabled = false;
				b_cliente.disabled = true;
				b_financeiro.disabled = false;
				b_ti.disabled = false;
				b_admin.disabled = false;
			}
			else if (nivel == "Financeiro") {
				b_usuario.disabled = false;
				b_cliente.disabled = false;
				b_financeiro.disabled = true;
				b_ti.disabled = false;
				b_admin.disabled = false;
			}
			else if (nivel == "TI") {
				b_usuario.disabled = false;
				b_cliente.disabled = false;
				b_financeiro.disabled = false;
				b_ti.disabled = true;
				b_admin.disabled = false;
			}
			else if (nivel == "Admin") {
				b_usuario.disabled = false;
				b_cliente.disabled = false;
				b_financeiro.disabled = false;
				b_ti.disabled = false;
				b_admin.disabled = true;
			}
		}
	</script>
</body>
</html>
<?php
}
?>