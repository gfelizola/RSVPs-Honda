<?php if (!defined('APPPATH')) exit('No direct script access allowed');

function wc_view_home($args, $ext)
{
	$mensagens_erro = array();
	
	if(wc_http_is_post())
	{
		wc_validation('inscricao_concessionaria_cod', 'wc_validation_required', array(), "Campo obrigatório.", $mensagens_erro);
		wc_validation('inscricao_concessionaria_cod', 'inscricao_valida_cod', array(), "Código inválido.", $mensagens_erro);
		
		if(empty($mensagens_erro))
		{
			$codigo = wc_request_get('inscricao_concessionaria_cod');
			
			/* checa e retorna o numero da inscricao (se houver) para
			 * concessionaria associada, ou cria nova inscricao */
			$inscricao_cod = inscricao_cod_por_concessionaria_ou_nova_inscricao($codigo);
			
			/* redireciona para inscricao */
			wc_http_redirect(wc_site_uri("inscricao/{$inscricao_cod}"));
		}
		
	}

	$dados_view = array();	
	$dados_view['mensagens_erro'] = $mensagens_erro;
	
	wc_render_view('home', $dados_view);
}

function wc_view_resgatar($args, $ext)
{
	$mensagens_erro = array();
	
	if(wc_http_is_post())
	{
		wc_validation('consulta_codigo', 'wc_validation_required', array(), "Campo obrigatório.", $mensagens_erro);
		wc_validation('consulta_codigo', 'inscricao_valida_cod', array(), "Código inválido.", $mensagens_erro);
		
		if(empty($mensagens_erro))
		{
			$consulta_codigo = wc_post_get('consulta_codigo');
			
			$inscricao_id = inscricao_cod_por_concessionaria($consulta_codigo);
			if(!$inscricao_id)
			{
				$mensagens_erro['consulta_codigo'] = 'Concessionária não inscrita.';
			}
			else
			{
				wc_http_redirect(wc_site_uri("ver_inscricao/{$inscricao_id}"));
			}
		}
		
		if(!empty($mensagens_erro))
		{
			$dados_view = array();	
			$dados_view['mensagens_erro'] = $mensagens_erro;

			wc_render_view('home', $dados_view);	
		}
	}
}

function wc_view_inscricao($args, $ext)
{
	$inscricao_id = NULL;
	$inscricao_concessionarias = array();
	$inscricao_participantes = array();
	$mensagens_erro = array();
	$num_participantes = 0; /* numero disponivel para input */
	$num_participantes_usados = 0;
	$dados_view = array();

	
	if(isset($args[0]) && $args[0]) {
		$inscricao_id = $args[0];
	} else {
		return wc_http_404();
	}
	
	/* carrega lista de concessionarias */
	$inscricao_concessionarias = inscricao_concessionarias($inscricao_id);
	if(empty($inscricao_concessionarias))
	{
		return wc_http_404();
	}
	
	/* processa o numero de participantes disponiveis */
	$inscricao_participantes = inscricao_lista_todas($inscricao_id);
	$num_participantes_usados = count($inscricao_participantes);
	
	$num_participantes = inscricao_concessionaria_num_participantes($inscricao_id, $inscricao_concessionarias, $num_participantes_usados);
	
	/* redireciona se ja nao houverem mais vagas */
	$disponivel = $num_participantes - $num_participantes_usados;
	if($disponivel < 1)
	{
		wc_http_redirect(wc_site_uri("ver_inscricao/{$inscricao_id}"));
		return;
	}
	
	/* processa post */
	if(wc_http_is_post())
	{
		/* validando participantes */
		for($a = $num_participantes_usados, $i = $a+1; $a <= $num_participantes; $i++, $a++)
		{
			$nome = trim(wc_request_get("inscricao_participante{$i}_nome"));
			
			if( ! empty($nome) )
			{
				wc_validation("inscricao_participante{$i}_cargo", 'wc_validation_required', array(), "Campo obrigatório.", $mensagens_erro);
				wc_validation("inscricao_participante{$i}_cracha", 'wc_validation_required', array(), "Campo obrigatório.", $mensagens_erro);
				wc_validation("inscricao_participante{$i}_carro", 'wc_validation_required', array(), "Campo obrigatório.", $mensagens_erro);
			}
			else
			{
				if( isset( $_REQUEST[ "inscricao_participante{$i}_nome" ] ) ) {	
					$mensagens_erro[ "inscricao_participante{$i}_nome" ] = "Campo obrigatório.";
				}
			}
		}
		
		if(empty($mensagens_erro))
		{
			/* processando participantes */
			for($a = $num_participantes_usados, $i = $a+1; $a <= $num_participantes; $i++, $a++)
			{
				$nome = trim(wc_request_get("inscricao_participante{$i}_nome"));
				$cargo = trim(wc_request_get("inscricao_participante{$i}_cargo"));
				$cracha = trim(wc_request_get("inscricao_participante{$i}_cracha"));
				$carro = wc_request_get("inscricao_participante{$i}_carro");
				
				if(empty($nome)) continue;
				
				inscricao_salva_participante($inscricao_id, $nome, $cargo, $cracha, $carro);
			}
			
			/* redireciona */
			wc_http_redirect(wc_site_uri("ver_inscricao/{$inscricao_id}"));
		}
	}

	$dados_view = array();
	$dados_view['mensagens_erro'] = $mensagens_erro;
	$dados_view['inscricao_id']	= $inscricao_id;
	$dados_view['concessionarias'] = $inscricao_concessionarias;
	$dados_view['participantes'] = $inscricao_participantes;
	$dados_view['num_participantes'] = $num_participantes;
	$dados_view['num_participantes_usados'] = $num_participantes_usados;
	
	wc_render_view('inscricao', $dados_view);
}

function wc_view_ver_inscricao($args, $ext)
{
	$inscricao_id = NULL;
	$inscricao_concessionarias = array();
	$inscricao_participantes = array();

	if(isset($args[0]) && $args[0]) {
		$inscricao_id = $args[0];
	} else {
		return wc_http_404();
	}
	
	/* carrega lista de concessionarias */
	$inscricao_concessionarias = inscricao_concessionarias($inscricao_id);
	if(empty($inscricao_concessionarias))
	{
		return wc_http_404();
	}
	
	$inscricao_participantes = inscricao_lista_todas($inscricao_id);
	$num_participantes_usados = count($inscricao_participantes);
	
	$num_participantes = inscricao_concessionaria_num_participantes($inscricao_id, $inscricao_concessionarias, $num_participantes_usados);
	
	$disponivel = $num_participantes - $num_participantes_usados;
	$disponivel = max(0, $disponivel);
	
	$dados_view['inscricao_id']	= $inscricao_id;
	$dados_view['concessionarias'] = $inscricao_concessionarias;
	$dados_view['participantes'] = $inscricao_participantes;
	$dados_view['disponivel'] = $disponivel;
	
	wc_render_view('ver_inscricao', $dados_view);
}


function wc_view_concessionaria_dados($args, $ext = EXT)
{
	$codigo = NULL;
	$concessionaria = NULL;
	
	if(isset($args[0]) && $args[0]) {
		$codigo= $args[0];
	} 
	
	if($codigo) {
		$concessionaria = inscricao_concessionaria_por_codigo($codigo);	
	}
	
	$dados_view = array(
		'concessionaria' => $concessionaria,
	);
	
	wc_render_view('concessionaria_dados', $dados_view);
}

function wc_view_concessionaria_agrega($args, $ext)
{
	$inscricao_id = NULL;
	$inscricao_concessionarias = array();
	$dados_view = array();
	$mensagens_erro = array();

	if(isset($args[0]) && $args[0]) {
		$inscricao_id = $args[0];
	} else {
		return wc_http_404();
	}
	
	/* carrega lista de concessionarias */
	$inscricao_concessionarias = inscricao_concessionarias($inscricao_id);
	if(empty($inscricao_concessionarias))
	{
		return wc_http_404();
	}
	
	if(wc_http_is_post())
	{
		wc_validation('inscricao_concessionaria_cod', 'wc_validation_required', array(), "Campo obrigatório.", $mensagens_erro);
		wc_validation('inscricao_concessionaria_cod', 'inscricao_valida_cod', array(), "Código inválido.", $mensagens_erro);
		wc_validation('inscricao_concessionaria_cod', 'inscricao_valida_cod_usado', array(), "Concessionária já foi usada", $mensagens_erro);
		
		if(empty($mensagens_erro))
		{
			$codigo = wc_request_get('inscricao_concessionaria_cod');
			inscricao_associa_concessionaria($inscricao_id, $codigo);
			
			/* redireciona */
			wc_http_redirect(wc_site_uri("ver_inscricao/{$inscricao_id}"));
		}
	}
	
	$dados_view['inscricao_id']	= $inscricao_id;
	$dados_view['concessionarias'] = $inscricao_concessionarias;
	$dados_view['mensagens_erro'] = $mensagens_erro;
	wc_render_view('concessionaria_agrega', $dados_view);
}

function wc_view_admin($args, $ext)
{
	$logado = wc_session_get('logado');
	
	if(!$logado) {
		wc_http_redirect(wc_site_uri('login'));
		return;
	}
	
	inscricao_salva_query_string_sessao('busca');
	
	$pagina = wc_request_get('pagina');
	$pagina = ($pagina > 0 ? $pagina : 1);
	$filtro_cod = wc_request_get('filtro_cod');
	$filtro_inscricao_num = wc_request_get('filtro_inscricao_num');
	
	
	$resultados = inscricao_admin_lista_todas($filtro_cod, $filtro_inscricao_num, $pagina);
	$total_resultados = inscricao_admin_lista_todas($filtro_cod, $filtro_inscricao_num, $pagina, TRUE);
	wc_log_write($total_resultados);
	$html_paginacao = inscricao_html_paginacao($pagina, 3, $total_resultados);
	
	$dados_view = array(
		'resultados'		=>	$resultados,
		'total_resultados'	=>	$total_resultados,
		'html_paginacao'	=>	$html_paginacao,
	);
	
	wc_render_view('admin', $dados_view);
}

function wc_view_login($args, $ext)
{
	$mensagens_erro = array();
	
	if(wc_http_is_post())
	{
		// valida senha, registra session e redireciona usuario
		wc_validation('admin_senha', 'wc_validation_required', array(ADMIN_SENHA), "Senha incorreta.", $mensagens_erro);
		wc_validation('admin_senha', 'wc_validation_matches', array(ADMIN_SENHA), "Senha incorreta.", $mensagens_erro);
		
		if(empty($mensagens_erro))
		{
			wc_session_set('logado', TRUE);
			wc_http_redirect(wc_site_uri('admin'));
			return;
		}
	}
	
	$dados_view = array();
	$dados_view['mensagens_erro'] = $mensagens_erro;
	
	wc_render_view('login', $dados_view);
}

function wc_view_logout($args, $ext)
{
	wc_session_set('logado', FALSE);
	wc_http_redirect(wc_site_uri('login'));
}

function wc_view_admin_gerar_relatorio($args, $ext)
{
	$logado = wc_session_get('logado');
	
	if(!$logado) {
		wc_http_redirect(wc_site_uri('login'));
		return;
	}
	
	inscricao_gera_excel();
}

function wc_view_admin_exibir_inscricao($args, $ext)
{
	$logado = wc_session_get('logado');
	
	if(!$logado) {
		wc_http_redirect(wc_site_uri('login'));
		return;
	}
	
	$inscricao_id = NULL;
	
	if(isset($args[0]) && $args[0]) {
		$inscricao_id = $args[0];
	} else {
		return wc_http_404();
	}
	
	$inscricao_concessionarias = inscricao_concessionarias($inscricao_id);
	$inscricao_participantes = inscricao_lista_todas($inscricao_id);
	
	$dados_view = array(
		'concessionarias'		=>	$inscricao_concessionarias,
		'participantes'			=>	$inscricao_participantes,
		'inscricao_id'			=>	$inscricao_id,
	);
	
	wc_render_view('admin_exibir_inscricao', $dados_view);
}

function wc_view_admin_edita_participante($args, $ext)
{
	$logado = wc_session_get('logado');
	
	if(!$logado) {
		wc_http_redirect(wc_site_uri('login'));
		return;
	}
	
	$participante_id = NULL;
	$participante = NULL;
	$inscricao_cod = NULL;
	$mensagens_erro = array();
	
	if(isset($args[0]) && $args[0]) {
		$participante_id = $args[0];
	} else {
		return wc_http_404();
	}
	
	$participante = inscricao_carrega_participante($participante_id);
	
	if(!$participante)
	{
		return wc_http_404();
	}
	
	$inscricao_cod = $participante['int_participantes_inscricao_cod_fk'];
	
	if(wc_http_is_post())
	{
		wc_validation("participante_nome", 'wc_validation_required', array(), "Campo obrigatório.", $mensagens_erro);
		wc_validation("participante_cargo", 'wc_validation_required', array(), "Campo obrigatório.", $mensagens_erro);
		wc_validation("participante_cracha", 'wc_validation_required', array(), "Campo obrigatório.", $mensagens_erro);
		wc_validation("participante_carro", 'wc_validation_required', array(), "Campo obrigatório.", $mensagens_erro);
		
		if(empty($mensagens_erro))
		{
			$nome = wc_post_get('participante_nome');
			$cargo = wc_post_get('participante_cargo');
			$cracha = wc_post_get('participante_cracha');
			$carro = wc_post_get('participante_carro');
			
			inscricao_atualiza_participante($participante_id, $nome, $cargo, $cracha, $carro);
			
			wc_http_redirect(wc_site_uri('admin_exibir_inscricao/'.$inscricao_cod));
		}
	}
	
	$dados_view = array(
		'participante_id'	=>	$participante_id,
		'participante'		=>	$participante,
		'inscricao_cod'		=>	$inscricao_cod,
		'mensagens_erro'	=>	$mensagens_erro,
	);
	
	wc_render_view('admin_edita_participante', $dados_view);
}

function wc_view_admin_remove_participante($args, $ext)
{
	$logado = wc_session_get('logado');
	
	if(!$logado) {
		wc_http_redirect(wc_site_uri('login'));
		return;
	}	
	
	$inscricao_cod = NULL;
	$participante_cod = NULL;
	
	if(isset($args[0]) && $args[0]) {
		$inscricao_cod = $args[0];
	} else {
		return wc_http_404();
	}
	
	if(isset($args[1]) && $args[1]) {
		$participante_cod = $args[1];
	} else {
		return wc_http_404();
	}
	
	inscricao_remove_participante($participante_cod);
	
	wc_http_redirect(wc_site_uri('admin_exibir_inscricao/'.$inscricao_cod));
}

function wc_view_admin_remove_concessionaria($args, $ext)
{
	$logado = wc_session_get('logado');
	
	if(!$logado) {
		wc_http_redirect(wc_site_uri('login'));
		return;
	}
	
	$inscricao_cod = NULL;
	$concessionaria_cod = NULL;
	
	if(isset($args[0]) && $args[0]) {
		$inscricao_cod = $args[0];
	} else {
		return wc_http_404();
	}
	
	if(isset($args[1]) && $args[1]) {
		$concessionaria_cod = $args[1];
	} else {
		return wc_http_404();
	}
	
	inscricao_remove_concessionaria($inscricao_cod, $concessionaria_cod);
	
	wc_http_redirect(wc_site_uri('admin_exibir_inscricao/'.$inscricao_cod));
}

?>