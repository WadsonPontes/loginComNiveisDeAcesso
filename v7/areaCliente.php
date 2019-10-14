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
	<style type="text/css">
		body {
		    background-color: #C8C8FF;
		    word-break: break-all;
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
	<p id="msg">Área exclusiva para clientes. Seja bem-vindo!</p>
	<a href="javascript:sair()" id="sair">Sair</a>

	<script type="text/javascript">
		const ARQUIVO = "sair.php";

		function sair() {
			let xhttp = new XMLHttpRequest();

			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					retorno();
				}
			};
			
			xhttp.open("POST", ARQUIVO, true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.send("msg=sair");
		}

		function retorno() {
			window.location.href = "login.php";
		}
	</script>
</body>
</html>
<?php
}
?>