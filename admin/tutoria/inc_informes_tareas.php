<?php if (! defined('INC_TUTORIA')) die ('<h1>Forbidden</h1>'); ?>

<!-- INFORMES DE TAREAS -->

<h3>Informes de tareas</h3>

<?php $result = mysql_query("SELECT id, apellidos, nombre, fecha FROM tareas_alumnos WHERE unidad='".$_SESSION['mod_tutoria']['unidad']."' ORDER BY fecha DESC"); ?>

<?php if (mysql_num_rows($result)): ?>
<table class="table table-hover">
	<thead>
		<tr>
			<th>Alumno/a</th>
			<th>Fecha</th>
		</tr>
	</thead>
	<tbody>
		<?php while ($row = mysql_fetch_array($result)): ?>
		<tr>
			<td><a href="../tareas/infocompleto.php?id=<?php echo $row['id']; ?>"><?php echo $row['nombre'].' '.$row['apellidos']; ?></a></td>
			<td nowrap><?php echo strftime('%e %b',strtotime($row['fecha'])); ?></td>
		</tr>
		<?php endwhile; ?>
		<?php mysql_free_result($result); ?>
	</tbody>
</table>

<?php else: ?>

<br>
<p class="lead text-muted">No hay informes de tareas registradas para esta unidad.</p>
<br>

<?php endif; ?>
            
<!-- FIN INFORMES DE TAREAS -->