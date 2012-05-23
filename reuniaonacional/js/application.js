// carrega estilo zebra
function configura_tabela_zebra(selector)
{
	$(selector).find("tbody > tr").removeClass("diff");
	$(selector).find("tbody > tr:not(:hidden):odd").addClass("diff");
}

function configura_inscricao_concessionaria_cod(main_selector)
{
	$(main_selector).find('input.inscricao_concessionaria_cod:first').keyup(function(){
		if(is_getting) return;
		
		var val = $(this).val();
		var url = base_uri + 'concessionaria_dados/' + val;
		var el = $('div.concessionaria_dados:first');
		
		$.ajax({
			url: url,
			data: {},
			timeout: 10000,
			success: function(data, textStatus, XMLHttpRequest) {
				el.html(data);
				$('.mensagem_erro').hide();
				configura_eventos_iniciais(el)
			},
			beforeSend: function(XMLHttpRequest) {
				is_getting = true;
				el.html('');
				el.addClass('loading');
			},
			complete: function(XMLHttpRequest, textStatus) {
				is_getting = false;
				el.removeClass('loading');
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
			},
			dataType: 'html'
		});
	});
}

function configura_esconde_mostra_inscricao_concessionaria_tipo(main_selector)
{
	$(main_selector).find('.inscricao_concessionaria_tipo').click(function(e){
		var el = $('div.dados-acompanhante');
		var val = parseInt($(this).val());
		
		switch(val) {
			case 1:
				$(el).show();
			break;
			
			case 2:
				$(el).hide();
			break;
		}
	});
}


function configura_eventos_iniciais(main_selector)
{
	configura_inscricao_concessionaria_cod(main_selector);
	configura_esconde_mostra_inscricao_concessionaria_tipo(main_selector);
	configura_tabela_zebra('table.menor-destaque');
}

// inicializando pagina
$(document).ready(function(){
	configura_eventos_iniciais(document); /* configura eventos na pagina atual */
	
	$('a.bt_excluir').click(function(e)
	{
		if(!confirm('Confirmar remoção?'))
		{
			e.preventDefault();
		}
	});
});