<?php wc_render_view('_header') ?>

<div class="admin">
	<h2>Administrativo</h2>
	
	<p>Selecione uma ação:</p>
	
	<p>
		<a href="<?php echo wc_site_uri('admin_gerar_relatorio') ?>" class="btn">Gerar Relatório Excel</a>
		<a href="<?php echo wc_site_uri('logout') ?>" class="btn">Sair</a>
	</p>
</div>


<?php wc_render_view('_footer') ?>