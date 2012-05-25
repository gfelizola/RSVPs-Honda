<?php if(!isset($concessionaria) || empty($concessionaria)): ?>
	<p class="mensagem_vazia"><em>Preencha com o código da concessionária.</em></p>
<?php else: ?>
	<table class="">
		<tr>
			<th>Código:</th>
			<td>
				<?php echo $concessionaria['int_assistencias_cod_pk'] ?>
				
				<?php if(isset($admin_edit) && isset($inscricao_cod) && $admin_edit): ?>
					<a href="<?php echo wc_site_uri('admin_remove_concessionaria/'.$inscricao_cod.'/'.$concessionaria['int_assistencias_cod_pk']) ?>" title="remover" class="bt_excluir">Remover</a>
				<?php endif ?>
			</td>
		</tr>

		<tr>
			<th>Razão Social:</th>
			<td><?php echo $concessionaria['str_assistencias_grupo'] ?></td>
		</tr>

		<tr>
			<th>Nome Fantasia:</th>
			<td><?php echo $concessionaria['str_assistencias_nome_guerra'] ?></td>
		</tr>

		<!-- tr>
			<th>Tipo:</th>
			<td><?php echo ($concessionaria['int_assistencias_tipo'] == MATRIZ ? "Matriz" : "Filial") ?></td>
		</tr-->
	</table>
<?php endif ?>

