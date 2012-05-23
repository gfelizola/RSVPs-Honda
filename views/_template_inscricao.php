<h3>Dados do <?php echo $titulo ?></h3>
<div class="form-dados margem_bottom_pequena">
	<table>
		<tr>
			<th>Nome completo:</th>
			<td>
				<input type="text" name="<?php echo "{$campo_nome_prefix}_nome" ?>" value="<?php echo wc_form_input_value("{$campo_nome_prefix}_nome") ?>" class="big">
				
				<span class="mensagem_erro">
					<?php echo wc_validation_error_in_field("{$campo_nome_prefix}_nome", $mensagens_erro) ?>
				</span>
			</td>
		</tr>
	
		<tr>
			<th>Cargo:</th>
			<td>
				<input type="text" name="<?php echo "{$campo_nome_prefix}_cargo" ?>" value="<?php echo wc_form_input_value( "{$campo_nome_prefix}_cargo") ?>" class="">

				<span class="mensagem_erro">
					<?php echo wc_validation_error_in_field("{$campo_nome_prefix}_cargo", $mensagens_erro) ?>
				</span>
			</td>
		</tr>
	
		<tr>
			<th>Nome ou apelido para o crachá:</th>
			<td>
				<input type="text" name="<?php echo "{$campo_nome_prefix}_cracha" ?>" value="<?php echo wc_form_input_value("{$campo_nome_prefix}_cracha") ?>" class="big">

				<span class="mensagem_erro">
					<?php echo wc_validation_error_in_field("{$campo_nome_prefix}_cracha", $mensagens_erro) ?>
				</span>
			</td>
		</tr>
	
		<tr>
			<th>Virá de carro:</th>
			<td>
				<input type="radio" name="<?php echo "{$campo_nome_prefix}_carro" ?>" value="<?php echo CARRO_SIM ?>" <?php echo wc_form_radiobox("{$campo_nome_prefix}_carro", CARRO_SIM, TRUE) ?>><label>sim</label>
				<input type="radio" name="<?php echo "{$campo_nome_prefix}_carro" ?>" value="<?php echo CARRO_NAO ?>" <?php echo wc_form_radiobox("{$campo_nome_prefix}_carro", CARRO_NAO, FALSE) ?>><label>não</label>
				
				<span class="mensagem_erro">
					<?php echo wc_validation_error_in_field("{$campo_nome_prefix}_carro", $mensagens_erro) ?>
				</span>
			</td>
		</tr>
	</table>
</div>