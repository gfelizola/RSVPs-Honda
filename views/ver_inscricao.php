<?php echo wc_render_view('_header') ?>

<div class="inscricao">
	<h2>Dados enviados!</h2>

	<!-- p>Este é o número de sua inscrição. Apresente-o no momento do credenciamento, no dia do evento, para ter acesso à plenária:</p -->

	<!-- p class="inscricao_num center">resgistro de inscrição: nº <strong><?php printf("%04d", $inscricao_id) ?></strong></p -->
	
	<?php if(count($concessionarias) > 1): ?>
		<h3>Dados das Concessionárias</h3>
	<?php else: ?>
		<h3>Dados da Concessionária</h3>
	<?php endif ?>
	
	<?php foreach($concessionarias as $concessionaria): ?>
		<div class="form-dados margem_bottom_pequena concessionaria_dados">
			<?php echo wc_render_view('concessionaria_dados', array('concessionaria' => $concessionaria)) ?>
		</div>
	<?php endforeach ?>
	
	<h3>Participantes</h3>
	
	<?php $i = 1; ?>
	<?php foreach($participantes as $participante): ?>
		<?php echo inscricao_template_usado($i, $participante) ?>
		<?php $i++; ?>
	<?php endforeach ?>
	
	<p class="aviso_voltar">Se for representar mais de uma concessionária, <a href="<?php echo wc_site_uri("concessionaria_agrega/{$inscricao_id}") ?>">cadastre-a aqui</a>.</p>
	
	<h1>Sua inscrição para a Reunião Nacional de Concessionários no dia 04 de outubro está confirmada.</h1>
	<h6>ATENÇÃO: para inscrever-se no Encontro Nacional de Serviços e Peças, <a href="http://www.encontroposvendahda.com.br/" target="_blank">clique aqui</a> ou acesse <a href="http://www.encontroposvendahda.com.br/" target="_blank">http://www.encontroposvendahda.com.br/</a></h6>
	
	<?php if($disponivel): ?>
		<!-- p class="aviso_voltar">Você pode adicionar mais participantes, <a href="<?php echo wc_site_uri("inscricao/{$inscricao_id}") ?>">cadastre-os aqui</a>.</p -->
	<?php endif ?>
</div>

<?php echo wc_render_view('_footer') ?>