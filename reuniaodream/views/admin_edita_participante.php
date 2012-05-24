<?php wc_render_view('_header') ?>

<div class="admin">
	<h2>Administrativo</h2>
	
	<p class=""><a href="<?php echo wc_site_uri('admin_exibir_inscricao/'.$inscricao_cod) ?>">&larr; Voltar para inscrição</a></p>
	
	<form action="<?php echo wc_site_uri('admin_edita_participante/'.$participante_id) ?>" method="POST">
	<h3>Dados do Participante</h3>
	<div class="form-dados margem_bottom_pequena">
		<table>
			<tr>
				<th>Nome completo:</th>
				<td>
					<input type="text" name="<?php echo "participante_nome" ?>" value="<?php echo wc_form_input_value_arr("participante_nome", "str_participantes_nome", $participante) ?>" class="big">

					<span class="mensagem_erro">
						<?php echo wc_validation_error_in_field("participante_nome", $mensagens_erro) ?>
					</span>
				</td>
			</tr>

			<tr>
				<th>Cargo:</th>
				<td>
					<input type="text" name="<?php echo "participante_cargo" ?>" value="<?php echo wc_form_input_value_arr("participante_cargo", "str_participantes_cargo", $participante) ?>" class="">

					<span class="mensagem_erro">
						<?php echo wc_validation_error_in_field("participante_cargo", $mensagens_erro) ?>
					</span>
				</td>
			</tr>

			<tr>
				<th>Nome ou apelido para o crachá:</th>
				<td>
					<input type="text" name="<?php echo "participante_cracha" ?>" value="<?php echo wc_form_input_value_arr("participante_cracha", "str_participantes_cracha", $participante) ?>" class="big">

					<span class="mensagem_erro">
						<?php echo wc_validation_error_in_field("participante_cracha", $mensagens_erro) ?>
					</span>
				</td>
			</tr>

			<tr>
				<th>Virá de carro:</th>
				<td>
					<input type="radio" name="<?php echo "participante_carro" ?>" value="<?php echo CARRO_SIM ?>" <?php echo wc_form_radiobox_arr("participante_carro", CARRO_SIM, "int_participantes_carro", $participante) ?>><label>sim</label>
					<input type="radio" name="<?php echo "participante_carro" ?>" value="<?php echo CARRO_NAO ?>" <?php echo wc_form_radiobox_arr("participante_carro", CARRO_NAO, "int_participantes_carro", $participante) ?>><label>não</label>

					<span class="mensagem_erro">
						<?php echo wc_validation_error_in_field("participante_carro", $mensagens_erro) ?>
					</span>
				</td>
			</tr>
			
			<tr>
				<th></th>
				<td><button type="submit">Enviar</button></td>
			</tr>
		</table>
	</div>
	</form>
</div>

<?php wc_render_view('_footer') ?>