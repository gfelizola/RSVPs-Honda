<?php if (!defined('APPPATH')) exit('No direct script access allowed');

// reseta tabela
function importacao_reseta($tabela_nome)
{
	global $db_conn;
	
	$sql = "TRUNCATE {$tabela_nome}";
	$db_conn->query($sql);
	
	$sql = "ALTER TABLE {$tabela_nome} AUTO_INCREMENT = 0";
	$db_conn->query($sql);
	
	wc_log_write("Importacao: Tabela {$tabela_nome} resetada");
}

// mecanismo de importacao do csv
function importacao()
{
	wc_log_write("Importacao: Concessionarias iniciada.");
	
	global $db_conn;
	$documento = "importacao/concessionarias.csv";
	
	if(!file_exists($documento)) {
		//wc_log_write("{$documento} nao existe. Caminho nao encontrado");
		//wc_http_500();
		echo "{$documento} nao existe. Caminho nao encontrado";
		return;
	}
	
	if(($handle = fopen($documento, "r")) !== FALSE)
	{
		
		while (($dados = fgetcsv($handle, 500000, ";")) !== FALSE)
		{
			$grupo_nome_atual = trim($dados[0]);
			$nome_guerra = trim($dados[1]);
			$cod = trim($dados[2]);
			$tipo = trim($dados[3]);
			$num_participantes = trim($dados[4]);
			$tipo_num = MATRIZ;
			
			if(!$cod) continue; // pula linhas em branco
			
			if($tipo == 'F') {
				$tipo_num = FILIAL;
			}
			
			$insercao = array(
				'int_assistencias_cod_pk'					=>	$cod,
				'str_assistencias_grupo'					=>	$grupo_nome_atual,
				'str_assistencias_nome_guerra'				=>	$nome_guerra,
				'int_assistencias_tipo'						=>	$tipo_num,
				'int_assistencias_num_participantes'		=>	$num_participantes,
			);
			
			$sql = wc_sql_insert("tab_assistencias", $insercao);
			$db_conn->exec($sql);
			
			echo $sql."<br>";
		}
		
		fclose($handle);
	}
	
	wc_log_write("Importacao: Concessionarias finalizada.");
}

// mecanismo para geracao de dump para inserir MUNICIPIO e UF nos dados existentes
// cospe saida na tela mesmo
/*
function wc_view_importacao_uf($args, $ext)
{
	$documento = "../importacao/concessionarias_uf.csv";
	
	if(($handle = fopen($documento, "r")) !== FALSE)
	{
		
		while (($dados = fgetcsv($handle, 500000, ";")) !== FALSE)
		{
			$cod = trim($dados[0]);
			$municipio = trim($dados[3]);
			$uf = trim($dados[4]);
		
			if(!$cod) continue; // pula linhas em branco
			
			$dados = array(
				'str_assistencias_municipio'		=>	$municipio,
				'str_assistencias_uf'				=>	$uf,
			);
			
			$sql = wc_sql_update("tab_assistencias", $dados);
			$sql .= " WHERE int_assistencias_cod_pk = '{$cod}';";
			
			echo $sql."<br>";
		}
		
		fclose($handle);
	}
}

function wc_view_importacao_num_participantes($args, $ext)
{
	global $db_conn;
	$documento = "../importacao/concessionarias.csv";
	
	if(($handle = fopen($documento, "r")) !== FALSE)
	{
		while (($dados = fgetcsv($handle, 500000, ";")) !== FALSE)
		{
			$num_participantes = trim($dados[4]);
			$cod = trim($dados[2]);
			
			$dados = array(
				'int_assistencias_num_participantes'		=>	$num_participantes,
			);
			
			$sql = wc_sql_update("tab_assistencias", $dados);
			$sql .= " WHERE int_assistencias_cod_pk = '{$cod}';";
			
			echo $sql."<br>";
		}
		
		fclose($handle);
	}
}*/

function wc_view_limpar_base($args, $ext)
{
	if(isset($args[0]) && $args[0]) {
		$senha_limpesa = $args[0];
		echo $senha_limpesa;
		if( $senha_limpesa == 'Xa!Kds12' ){
			// Setar o tempo de execucao infinito
			ini_set('max_execution_time', 0);
			
			importacao_reseta("tab_inscricoes");
			importacao_reseta("tab_inscricoes_assistencias");
			importacao_reseta("tab_participantes");
			
			wc_http_redirect(wc_site_uri(""));
		} else {
			return wc_http_404();
		}
	} else {
		return wc_http_404();
	}
}

// funcao externa
/*
function wc_view_importacao_nova($args, $ext)
{
	// Setar o tempo de execucao infinito
	ini_set('max_execution_time', 0);
	
	importacao_reseta("tab_assistencias");
	importacao();
}*/
?>