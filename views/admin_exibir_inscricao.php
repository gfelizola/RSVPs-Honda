<?php wc_render_view('_header') ?>

<div class="admin">
	<h2>Administrativo</h2>
	
	<p class=""><a href="<?php echo wc_site_uri('admin?'.wc_session_get('busca')) ?>">&larr; Voltar para listagem</a></p>
	
	<p class="inscricao_num center"><strong>Exibindo inscrição <?php printf("%04d", $inscricao_id) ?></strong></p>
	
	<?php if(count($concessionarias) > 1): ?>
		<h3>Dados das Concessionárias</h3>
	<?php else: ?>
		<h3>Dados da Concessionária</h3>
	<?php endif ?>
	
	<?php foreach($concessionarias as $concessionaria): ?>
		<div class="form-dados margem_bottom_pequena concessionaria_dados">
			<?php echo wc_render_view('concessionaria_dados', array('concessionaria' => $concessionaria, 'admin_edit' => (count($concessionarias) > 1), 'inscricao_cod'	=> $inscricao_id)) ?>
		</div>
	<?php endforeach ?>	
	
	<h3>Participantes</h3>
	
	<?php $i = 1; ?>
	<?php foreach($participantes as $participante): ?>
		<?php echo inscricao_template_usado($i, $participante, TRUE) ?>
		<?php $i++; ?>
	<?php endforeach ?>
</div>


<?php wc_render_view('_footer') ?>