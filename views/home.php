<?php echo wc_render_view('_header') ?>
				
	<!-- INSCRICAO -->
	<div class="inscricao">
		<h2>Ficha de Inscrição</h2>
	
		<form action="<?php echo wc_site_uri('home') ?>" method="POST">

			<div class="form-dados margem_bottom_pequena">
				<table>				
					<tr>
						<th>Código de Assistência Técnica (7 dígitos):</th>
						<td>
							<input type="text" name="inscricao_concessionaria_cod" value="<?php echo wc_form_input_value('inscricao_concessionaria_cod') ?>" class="small inscricao_concessionaria_cod">
						
							<span class="mensagem_erro">
								<?php echo wc_validation_error_in_field('inscricao_concessionaria_cod', $mensagens_erro) ?>
							</span>
						</td>
					</tr>
				</table>
			</div>
		
			<h3>Dados da Concessionária</h3>
			<div class="form-dados margem_bottom_pequena concessionaria_dados">
				<?php wc_view_concessionaria_dados(array(wc_form_input_value('inscricao_concessionaria_cod'))); ?>
			</div>
			
			<button type="submit" class="btn float_right">CONTINUAR</button>
			<br class="clear">

		</form>
	</div>
	<!-- FIM DE INSCRICAO -->
		
<?php echo wc_render_view('_footer') ?>