<h3>Dados do <?php echo $titulo ?></h3>
<div class="form-dados margem_bottom_pequena">
	<table>
		<tr>
			<th>Nome completo:</th>
			<td>
				<?php echo $participante['str_participantes_nome'] ?>
				
				<?php if($admin_edit): ?>
					<a href="<?php echo wc_site_uri('admin_edita_participante/'.$participante['int_participantes_cod_pk']) ?>" class="bt_editar" title="Editar">Editar</a>
					
					<a href="<?php echo wc_site_uri('admin_remove_participante/'.$participante['int_participantes_inscricao_cod_fk'].'/'.$participante['int_participantes_cod_pk']) ?>" class="bt_excluir" title="Remover">Remover</a>
				<?php endif ?>
			</td>
		</tr>
	
		<tr>
			<th>Cargo:</th>
			<td>
				<?php echo $participante['str_participantes_cargo'] ?>
			</td>
		</tr>
	
		<tr>
			<th>Nome ou apelido para o crachá:</th>
			<td>
				<?php echo $participante['str_participantes_cracha'] ?>
			</td>
		</tr>
	
		<tr>
			<th>Virá de carro:</th>
			<td>
				<?php echo ($participante['int_participantes_carro'] == CARRO_SIM ? 'sim' : 'não') ?>
			</td>
		</tr>
	</table><br />
</div>

