<?php wc_render_view('_header') ?>

<div class="admin">
	<h2>Administrativo</h2>
	
	<form action="<?php echo wc_site_uri('admin') ?>" method="GET">
		<p>
			<strong>Inscrição:</strong>
			<input type="text" name="filtro_inscricao_num" value="<?php echo wc_form_input_value('filtro_inscricao_num') ?>" class="small">

			<strong>Cód Concessionária:</strong>
			<input type="text" name="filtro_cod" value="<?php echo wc_form_input_value('filtro_cod') ?>" class="small">
			
			<button type="submit">Filtrar</button>
			<a href="<?php echo wc_site_uri('admin') ?>">cancelar</a>
		</p>
	</form>
	
	<table class="width_full menor-destaque margem_bottom_grande">
		<thead>
			<th>Inscrição</th>
			<th>Concessionárias Cod</th>
			<th>Grupo</th>
			<th>Nome Guerra</th>
			<th></th>
		</thead>
		<tbody>
			<?php if(empty($resultados)): ?>
				<td colspan="5">Não há participações cadastradas</td>
			<?php else: ?>
				<?php while($resultado = inscricao_admin_processa_listagem($resultados)): ?>
					<tr>
						<td><?php printf("%04d", $resultado['int_inscricoes_id_pk']) ?></td>
						<td>
							<ul>
								<?php foreach($resultado['cods'] as $cod): ?>
									<li><?php echo $cod ?></li>
								<?php endforeach ?>
							</ul>
						</td>
						<td>
							<ul>
								<?php foreach($resultado['grupos'] as $grupo): ?>
									<li><?php echo $grupo ?></li>
								<?php endforeach ?>
							</ul>
						</td>
						<td>
							<ul>
								<?php foreach($resultado['nomes_guerra'] as $nome_guerra): ?>
									<li><?php echo $nome_guerra ?></li>
								<?php endforeach ?>
							</ul>
						</td>
						<td><a href="<?php echo wc_site_uri('admin_exibir_inscricao/'.$resultado['int_inscricoes_id_pk']) ?>" class="bt_info" title="Ver informações">Ver informações</a></td>
					</tr>
				<?php endwhile ?>
			<?php endif ?>
		</tbody>
	</table>
	
	
	<p class="paginacao"><?php echo $html_paginacao ?></p>
	<p class="total_resultados margem_bottom_grande"><?php echo $total_resultados ?> inscrições</p>	
	
	<p>
		<a href="<?php echo wc_site_uri('admin_gerar_relatorio') ?>" class="btn">Gerar Relatório Excel</a>
		<a href="<?php echo wc_site_uri('logout') ?>" class="btn">Sair</a>
	</p>
</div>


<?php wc_render_view('_footer') ?>