<!-- MODULO RESUMEN FALTAS DE ASISTENCIA -->

<a name="faltas"></a>
<h3>Resumen de faltas de asistencia</h3>

<div class="row">
	
	<div class="col-sm-4">
	
		<?php $result = mysql_query("SELECT DISTINCT FALUMNOS.APELLIDOS, FALUMNOS.NOMBRE, FALUMNOS.unidad, FALTAS.fecha, COUNT(*) AS total FROM FALTAS, FALUMNOS WHERE FALUMNOS.claveal = FALTAS.claveal and FALTAS.falta = 'F' and FALUMNOS.claveal = '$claveal' GROUP BY FALUMNOS.apellidos"); ?>
		<?php $total = 0; ?>
		<?php if (mysql_num_rows($result)): ?>
		<?php $row = mysql_fetch_array($result); ?>
		<?php $total = $row['total']; ?>
		<?php mysql_free_result($result); ?>
		<?php endif; ?>
		
		<h3 class="text-info text-center">
			<?php echo $total; ?><br>
			<small class="text-uppercase">faltas injustificadas</small>
		</h3>
		
	</div>
	
	<div class="col-sm-4">
		
		<?php $result = mysql_query("SELECT DISTINCT FALUMNOS.APELLIDOS, FALUMNOS.NOMBRE, FALUMNOS.unidad, FALTAS.fecha, COUNT(*) AS total FROM FALTAS, FALUMNOS WHERE FALUMNOS.claveal = FALTAS.claveal AND FALTAS.falta = 'J' AND  FALUMNOS.claveal = '$claveal' GROUP BY FALUMNOS.apellidos"); ?>
		<?php $total = 0; ?>
		<?php if (mysql_num_rows($result)): ?>
		<?php $row = mysql_fetch_array($result); ?>
		<?php $total = $row['total']; ?>
		<?php mysql_free_result($result); ?>
		<?php endif; ?>
		
		<h3 class="text-info text-center">
			<?php echo $total; ?><br>
			<small class="text-uppercase">faltas justificadas</small>
		</h3>
		
	</div>
	
	<div class="col-sm-4">
		
		<?php $result = mysql_query("SELECT distinct FALUMNOS.APELLIDOS, FALUMNOS.NOMBRE, FALUMNOS.unidad, FALTAS.falta, FALTAS.fecha FROM FALUMNOS, FALTAS where FALUMNOS.CLAVEAL = FALTAS.CLAVEAL and FALTAS.falta = 'F' and  FALUMNOS.claveal = $claveal group by FALUMNOS.APELLIDOS, FALTAS.fecha"); ?>
		<?php $total = mysql_num_rows($result); ?>
		
		<h3 class="text-info text-center">
			<?php echo $total; ?><br>
			<small class="text-uppercase">d�as completos injustificados</small>
		</h3>
		
	</div>

</div>

<br>

<!-- FIN MODULO RESUMEN FALTAS DE ASISTENCIA -->
