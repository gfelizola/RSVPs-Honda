<?php echo wc_render_view('_header') ?>

<div class="inscricao">
	<h2>Represento mais de uma concessionária</h2>
	
	<form action="<?php echo wc_site_uri("concessionaria_agrega/{$inscricao_id}") ?>" method="POST">

		<div class="form-dados margem_bottom_pequena">
			<table>				
				<tr>
					<th>Código da concessionária:</th>
					<td>
						<input type="text" name="inscricao_concessionaria_cod" value="<?php echo wc_form_input_value('inscricao_concessionaria_cod') ?>" class="small inscricao_concessionaria_cod">
					
						<span class="mensagem_erro">
							<?php echo wc_validation_error_in_field('inscricao_concessionaria_cod', $mensagens_erro) ?>
						</span>
					</td>
				</tr>
			</table>
		</div>
	
		<h3>Dados da outra Concessionária</h3>
		<div class="form-dados margem_bottom_pequena concessionaria_dados">
			<?php wc_view_concessionaria_dados(array(wc_form_input_value('inscricao_concessionaria_cod'))); ?>
		</div>
		
		
		<?php if(count($concessionarias) > 1): ?>
			<h3>Dados das Concessionárias atuais</h3>
		<?php else: ?>
			<h3>Dados da Concessionária atual</h3>
		<?php endif ?>

		<?php foreach($concessionarias as $concessionaria): ?>
			<div class="form-dados margem_bottom_pequena concessionaria_dados">
				<?php echo wc_render_view('concessionaria_dados', array('concessionaria' => $concessionaria)) ?>
			</div>
		<?php endforeach ?>
		
		<button type="submit" class="btn float_right">CONFIRMAR</button>
		<br class="clear">

	</form>
</div>

<?php echo wc_render_view('_footer') ?>