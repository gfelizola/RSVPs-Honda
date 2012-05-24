<?php wc_render_view('_header') ?>

<div class="login">
	<h2>Login</h2>
	
	<div class="form-dados margem_bottom_pequena">
		<form action="<?php echo wc_site_uri('login') ?>" method="POST">
			<table>
				<tr>
					<th>Senha:</th>
					<td>
						<input type="password" name="admin_senha" class="">

						<span class="mensagem_erro">
							<?php echo wc_validation_error_in_field('admin_senha', $mensagens_erro) ?>
						</span>
					</td>
					<td>
						<button type="submit" class="btn btn_small">ENTRAR</button>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>


<?php wc_render_view('_footer') ?>