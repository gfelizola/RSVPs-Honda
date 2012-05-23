<?php echo wc_render_view('_header') ?>

<!-- INSCRICAO -->
<div class="inscricao">
	<h2>Ficha de Inscrição</h2>
	
	<form action="<?php echo wc_site_uri("inscricao/{$inscricao_id}") ?>" method="POST">
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
		
		<?php for($i = $num_participantes_usados; $i < $num_participantes; $i++): ?>
			<?php echo inscricao_template($i+1) ?>
		<?php endfor ?>
	
		<button type="submit" class="btn float_right">ENVIAR</button>
		<br class="clear">
	</form>
</div>
<!-- FIM DE INSCRICAO -->

<?php echo wc_render_view('_footer') ?>