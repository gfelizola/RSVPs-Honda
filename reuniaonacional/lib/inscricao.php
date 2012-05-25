<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/* retorna dados da concessionaria pelo seu código */
function inscricao_concessionaria_por_codigo($codigo)
{
	global $db_conn;
	
	$sql = "SELECT * FROM tab_assistencias WHERE int_assistencias_cod_pk = '{$codigo}' LIMIT 1";
	
	$sth = $db_conn->prepare($sql);
	$sth->execute();
	$res = $sth->fetch();
	$sth->closeCursor();
	
	return $res;
}

/* funcao para o validador do formulario de inscricao
 * valida se o codigo da concessionaria é valido
 */
function inscricao_valida_cod($codigo)
{
	if(wc_validation_required($codigo))
	{
		return (inscricao_concessionaria_por_codigo($codigo) ? TRUE : FALSE);
	}
}

function inscricao_valida_cod_usado($codigo)
{
	if(wc_validation_required($codigo))
	{
		$inscricao_cod = inscricao_cod_por_concessionaria($codigo);
		
		if($inscricao_cod) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
}

/* resgata o numero da inscricao por codigo da concessionaria */
function inscricao_cod_por_concessionaria($concessionaria_cod)
{
	global $db_conn;
	
	$sql = "SELECT int_inscricoes_assistencias_inscricao_id_fk FROM tab_inscricoes_assistencias WHERE int_inscricoes_assistencias_assistencia_id_fk = '{$concessionaria_cod}' LIMIT 1";
	
	
	$sth = $db_conn->prepare($sql);
	$sth->execute();
	$res = $sth->fetch();
	$sth->closeCursor();
	
	if(isset($res['int_inscricoes_assistencias_inscricao_id_fk']) && $res['int_inscricoes_assistencias_inscricao_id_fk'])
	{
		return $res['int_inscricoes_assistencias_inscricao_id_fk'];
	}
	else
	{
		return NULL;
	}
}

/* salva inscricao e retorna ID */
function inscricao_salva_nova()
{
	global $db_conn;
	
	$dados = array(
		'int_inscricoes_id_pk'	=>	NULL,
	);
	
	$sql = wc_sql_insert("tab_inscricoes", $dados);
	$db_conn->exec($sql);
	
	return $db_conn->lastInsertId();
}

/* associa concessionaria a uma inscricao */
function inscricao_associa_concessionaria($inscricao_numero, $concessionaria_cod)
{
	global $db_conn;
	
	$dados = array(
		'int_inscricoes_assistencias_assistencia_id_fk'		=>	$concessionaria_cod,
		'int_inscricoes_assistencias_inscricao_id_fk'		=>	$inscricao_numero,
	);
	
	$sql = wc_sql_insert("tab_inscricoes_assistencias", $dados);
	$db_conn->exec($sql);
}

/* retorna o codigo da inscricao ou gera nova inscricao e agrega concessionaria */
function inscricao_cod_por_concessionaria_ou_nova_inscricao($concessionaria_cod)
{
	/* checa se ja existe inscricao para concessionaria */
	$inscricao_cod = inscricao_cod_por_concessionaria($concessionaria_cod);
	
	if(empty($inscricao_cod)) /* cria nova inscricao se nao existir */
	{
		$inscricao_cod = inscricao_salva_nova();
		inscricao_associa_concessionaria($inscricao_cod, $concessionaria_cod);
	}
	
	return $inscricao_cod;
}

/* retorna dados das concessionarias pelo código da inscrição */
function inscricao_concessionarias($inscricao_numero)
{
	global $db_conn;
	
	$sql = "SELECT tab_assistencias.* FROM tab_assistencias INNER JOIN tab_inscricoes_assistencias ON int_inscricoes_assistencias_assistencia_id_fk = int_assistencias_cod_pk WHERE int_inscricoes_assistencias_inscricao_id_fk = '{$inscricao_numero}'";
	
	$sth = $db_conn->prepare($sql);
	$sth->execute();
	$res = $sth->fetchAll();
	$sth->closeCursor();
	
	return $res;
}

function inscricao_template($numero)
{
	$dados_view = array();
	
	$dados_view['campo_nome_prefix'] = "inscricao_participante{$numero}";
	$dados_view['titulo'] = "Participante {$numero}";
	$dados_view['numero'] = $numero;
	
	return wc_render_view('_template_inscricao', $dados_view, TRUE);
}

function inscricao_template_usado($numero, $participante, $admin_edit = FALSE)
{
	$dados_view = array();
	
	$dados_view['titulo'] = "Participante {$numero}";
	$dados_view['numero'] = $numero;
	$dados_view['participante'] = $participante;
	$dados_view['admin_edit'] = $admin_edit;
	
	return wc_render_view('_template_inscricao_usado', $dados_view, TRUE);
}

/* lista todas as inscricoes pelo codigo da concessionaria */
function inscricao_lista_todas($codigo)
{
	$codigo = (int)$codigo;
	if(!$codigo) return FALSE;
	
	global $db_conn;
	
	$sql = "SELECT * FROM tab_participantes WHERE int_participantes_inscricao_cod_fk = '{$codigo}' ORDER BY int_participantes_cod_pk";
	
	$sth = $db_conn->prepare($sql);
	$sth->execute();
	$res = $sth->fetchAll();
	$sth->closeCursor();
	
	return $res;
}

/* descobre se participacao tem FILIAL ou MATRIZ */
function inscricao_concessionaria_num_participantes($inscricao_cod, $concessionarias, $total_usado)
{
	
	$disponivel = 1; // FILIAL padrão
	
	foreach($concessionarias as $concessionaria)
	{
		if($concessionaria['int_assistencias_num_participantes'] > $disponivel)
		{
			$disponivel = $concessionaria['int_assistencias_num_participantes'];
		}
	}
	
	return $disponivel;
}

function inscricao_salva_participante($inscricao_cod, $nome, $cargo, $cracha, $carro)
{
	global $db_conn;
	
	$dados = array(
		'int_participantes_inscricao_cod_fk'	=>	$inscricao_cod,
		'str_participantes_nome'				=>	$nome,
		'str_participantes_cargo'				=>	$cargo,
		'str_participantes_cracha'				=>	$cracha,
		'int_participantes_carro'				=>	$carro,
	);
	
	$sql = wc_sql_insert("tab_participantes", $dados);
	$db_conn->exec($sql);
}

function inscricao_atualiza_participante($participante_cod, $nome, $cargo, $cracha, $carro)
{
	global $db_conn;
	
	$dados = array(
		'str_participantes_nome'				=>	$nome,
		'str_participantes_cargo'				=>	$cargo,
		'str_participantes_cracha'				=>	$cracha,
		'int_participantes_carro'				=>	$carro,
	);
	
	$sql = wc_sql_update("tab_participantes", $dados);
	$sql .= " WHERE int_participantes_cod_pk = ".$participante_cod;
	
	$db_conn->exec($sql);
}

function inscricao_carrega_participante($participante_cod)
{
	global $db_conn;
	
	$sql = "SELECT * FROM tab_participantes WHERE int_participantes_cod_pk = '{$participante_cod}' LIMIT 1";
	
	$sth = $db_conn->prepare($sql);
	$sth->execute();
	$res = $sth->fetch();
	$sth->closeCursor();
	
	return $res;
}

function inscricao_remove_participante($participante_cod)
{
	global $db_conn;
	$sql = "DELETE FROM tab_participantes WHERE int_participantes_cod_pk = '{$participante_cod}'";
	$db_conn->exec($sql);
}

function inscricao_remove_concessionaria($inscricao_cod, $concessionaria_cod)
{
	global $db_conn;
	$sql = "DELETE FROM tab_inscricoes_assistencias WHERE int_inscricoes_assistencias_assistencia_id_fk = '{$concessionaria_cod}' AND int_inscricoes_assistencias_inscricao_id_fk = '{$inscricao_cod}'";
	$db_conn->exec($sql);
}

function inscricao_lista_todas_excel()
{
	global $db_conn;
	
	$sql = "SELECT * FROM tab_inscricoes ORDER BY dta_inscricoes_criado_em ASC";
	
	$sth = $db_conn->prepare($sql);
	$sth->execute();
	$res = $sth->fetchAll();
	$sth->closeCursor();
	
	return $res;
}

function inscricao_admin_lista_todas($filtro_cod = NULL, $filtro_inscricao_num = NULL, $pagina = 1, $contar_total = FALSE)
{
	global $db_conn;
	
	
	if(!$contar_total) {
		$sql = "SELECT tab_inscricoes.* FROM tab_inscricoes";
	} else {
		$sql = "SELECT COUNT(*) AS total FROM tab_inscricoes";
	}
	
	$join = "INNER JOIN tab_inscricoes_assistencias ON int_inscricoes_assistencias_inscricao_id_fk = int_inscricoes_id_pk INNER JOIN tab_assistencias ON int_assistencias_cod_pk = int_inscricoes_assistencias_assistencia_id_fk";
	
	$sql .= " {$join} WHERE 1=1";
	
	if($filtro_cod) {
		$sql .= " AND int_assistencias_cod_pk = '{$filtro_cod}'";
	}
	
	if($filtro_inscricao_num) {
		$sql .= " AND int_inscricoes_id_pk = '{$filtro_inscricao_num}'";
	}
	
	if(!$contar_total)
	{
	
		$sql .= " GROUP BY int_inscricoes_id_pk";		
		
		$pagina = (int)$pagina;
		$pagina = max(($pagina-1), 0);		
	
		$offset = RESULTADOS_POR_PAGINA * $pagina;
		$limit = RESULTADOS_POR_PAGINA;
	
		$sql .= " ORDER BY dta_inscricoes_criado_em, str_assistencias_nome_guerra LIMIT {$offset},{$limit}";
		$sql = "SELECT * FROM ({$sql}) AS tbl1 {$join}";
		
		$sth = $db_conn->prepare($sql);
		$sth->execute();
		$res = $sth->fetchAll();
		$sth->closeCursor();
		
		return $res;
	}
	else
	{
		$total = 0;
		
		$sth = $db_conn->prepare($sql);
		$sth->execute();
		$res = $sth->fetch();
		$sth->closeCursor();
		
		if(isset($res['total']) && $res['total']) {
			$total = $res['total'];
		}
		
		return $total;
	}
}

function inscricao_gera_excel()
{
	$objPHPExcel = new PHPExcel();
	
	// configurando cabeçalho
	$campos = array(
		// informacoes da inscricao
		'Inscrição',
		'Criada em',
		
		// informacoes da concessionaria
		'Código',
		'Grupo',
		'Nome Guerra',
		//'Município',
		//'UF',
		//'Tipo',
		
		// informacoes do participante
		'Nome do participante',
		'Cargo',
		'Nome do crachá',
		'Virá de carro?',
	);
	
	foreach($campos as $key => $label) {
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($key, 1, $label);
	}
	
	$linha = 2;
	
	$inscricoes = inscricao_lista_todas_excel();
	foreach($inscricoes as $inscricao)
	{
		// informacoes da inscricao
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $inscricao['int_inscricoes_id_pk']);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, $inscricao['dta_inscricoes_criado_em']);
			$objPHPExcel->getActiveSheet()->getStyle('B'.$linha)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME);
						
		// concessionarias
		$concessionarias = inscricao_concessionarias($inscricao['int_inscricoes_id_pk']);
		$linha_concessionarias = $linha;
		
		foreach($concessionarias as $key => $conc)
		{
			if($key != 0) {
				$linha_concessionarias++;
			}
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha_concessionarias, $conc['int_assistencias_cod_pk']);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha_concessionarias, $conc['str_assistencias_grupo']);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha_concessionarias, $conc['str_assistencias_nome_guerra']);
			//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha_concessionarias, $conc['str_assistencias_municipio']);
			//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha_concessionarias, $conc['str_assistencias_uf']);
			//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha_concessionarias, ($conc['int_assistencias_tipo'] == MATRIZ ? "Matriz" : "Filial"));
			
		}
		
		// participantes
		$participantes = inscricao_lista_todas($inscricao['int_inscricoes_id_pk']);
		$linha_participantes = $linha;
		
		foreach($participantes as $key => $part)
		{
			if($key != 0) {
				$linha_participantes++;
			}
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha_participantes, $part['str_participantes_nome']);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha_participantes, $part['str_participantes_cargo']);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha_participantes, $part['str_participantes_cracha']);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $linha_participantes, ($part['int_participantes_carro'] == CARRO_SIM ? "sim" : "não"));
		}
		
		
		$linha = max($linha_concessionarias, $linha_participantes) + 1;
	}

	// configura padrao de pagina
	$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
	
	// seta sheet principal
	$objPHPExcel->setActiveSheetIndex(0);
	
	// processa filename
	$filename = 'relatorio_'.date('Y.m.d_H.i').'.xls';
	
	// seleciona formato e joga arquivo no stream
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="'.$filename.'"');
	header('Cache-Control: max-age=0');
	
	$objWriter->save('php://output');
}

function inscricao_html_paginacao($pagina_atual, $num_links, $total_linhas, $por_pagina = RESULTADOS_POR_PAGINA, $texto_proximo = "Próxima &#187;", $texto_anterior = "&#171; Anterior")
{
	if($total_linhas <= $por_pagina) return NULL;
		
	$html = NULL;
	$pagina_atual = max($pagina_atual, 0);
	$total_paginas = ceil($total_linhas/$por_pagina);
	//wc_log_write("Total paginas: ".$total_paginas);
	
	$inicio = ((($pagina_atual - $num_links) > 0) ? $pagina_atual - $num_links : 1);
	$fim = (($pagina_atual + $num_links < $total_paginas) ? $pagina_atual + $num_links : $total_paginas);
	
	// processa query string atual
	$opcoes = array();
	parse_str($_SERVER['QUERY_STRING'], $opcoes);
	
	// retira elementos do wc pois usamos url amigaveis
	unset($opcoes['f']);
	unset($opcoes['e']);
	unset($opcoes['a']);
	
	// anterior
	if($pagina_atual > 1) {
		$opcoes['pagina'] = $pagina_atual-1;
		$query = "?".http_build_query($opcoes);		
		$html .= "<a href=\"{$query}\">{$texto_anterior}</a>";
	}
	
	// ... no inicio
	if($inicio != 1) {
		$html .= "<span class=\"apagado\">...</span> ";
	}	
	
	// todos os links
	for($a = $inicio; $a <= $fim; $a++)
	{
		if($a == $pagina_atual) {
			$html .= "<strong>{$a}</strong> ";
		} else {
			$opcoes['pagina'] = $a;
			$query = "?".http_build_query($opcoes);
			
			$html .= "<a href=\"{$query}\">{$a}</a> ";
		}
	}
	
	// ... no final
	if($fim != $total_paginas) {
		$html .= "<span class=\"apagado\">...</span> ";
	}
	
	// proximo
	if($pagina_atual != $fim) {
		$opcoes['pagina'] = $pagina_atual+1;
		$query = "?".http_build_query($opcoes);		
		$html .= "<a href=\"{$query}\">{$texto_proximo}</a>";
	}
	
	
	return trim($html);
}

function inscricao_admin_processa_listagem(&$resultados)
{
	$ultimo_id = 0;
	$registro_proc = array();
	static $registro = NULL;
	
	if(!$registro)
	{
		if(empty($resultados))
		{
			return FALSE;
		}
		
		$registro = array_shift($resultados);
	}
	
	$registro_proc = $registro;
	$ultimo_id = $registro_proc['int_inscricoes_id_pk'];
	
	$registro_proc['cods'] = array();
	$registro_proc['grupos'] = array();
	$registro_proc['nomes_guerra'] = array();
	
	do
	{
		if($registro['int_assistencias_cod_pk'] && $registro['str_assistencias_grupo'] && $registro['str_assistencias_nome_guerra'])
		{
			array_push($registro_proc['cods'], $registro['int_assistencias_cod_pk']);
			array_push($registro_proc['grupos'], $registro['str_assistencias_grupo']);
			array_push($registro_proc['nomes_guerra'], $registro['str_assistencias_nome_guerra']);
		}
		
		$registro = array_shift($resultados);
		
	} while($registro && $registro['int_inscricoes_id_pk'] == $ultimo_id);
	
	return $registro_proc;
}

// monta query string com as opcoes de busca e salva em sessao
function inscricao_salva_query_string_sessao($chave)
{
	$opcoes = array();
	parse_str($_SERVER['QUERY_STRING'], $opcoes);
	
	/* retira elementos do wc pois usamos url amigaveis */
	unset($opcoes['f']);
	unset($opcoes['e']);
	unset($opcoes['a']);
	
	$nova_query = http_build_query($opcoes);
	wc_session_set($chave, $nova_query);
	
	return $nova_query;
}
?>