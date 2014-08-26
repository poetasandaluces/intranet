<?php
session_start();
include("../../config.php");
// COMPROBAMOS LA SESION
if ($_SESSION['autentificado'] != 1) {
	$_SESSION = array();
	session_destroy();
	header('Location:'.'http://'.$dominio.'/intranet/salir.php');
	exit();
}

if($_SESSION['cambiar_clave']) {
	header('Location:'.'http://'.$dominio.'/intranet/clave.php');
}

registraPagina($_SERVER['REQUEST_URI'],$db_host,$db_user,$db_pass,$db);

mysql_query("CREATE TABLE IF NOT EXISTS `evaluaciones` (
  `unidad` varchar(64) COLLATE latin1_spanish_ci NOT NULL,
  `asignatura` varchar(64) COLLATE latin1_spanish_ci NOT NULL,
  `evaluacion` char(3) COLLATE latin1_spanish_ci NOT NULL,
  `profesor` text COLLATE latin1_spanish_ci NOT NULL,
  `calificaciones` blob NOT NULL,
  PRIMARY KEY (`unidad`,`asignatura`,`evaluacion`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;");


$evaluaciones = array(
	'ini' => 'Evaluaci�n Inicial (Septiembre)',
	'in1' => 'Intermedia 1 (Octubre)',
	'in2' => 'Intermedia 2 (Noviembre)',
	'1ev' => '1� Evaluaci�n (Diciembre)',
	'in3' => 'Intermedia 3 (Enero)',
	'in4' => 'Intermedia 4 (Febrero)',
	'2ev' => '2� Evaluaci�n (Marzo)',
	'in5' => 'Intermedia 5 (Abril)',
	'in6' => 'Intermedia 6 (Mayo)',
	'ord' => 'Evaluaci�n Ordinaria (Junio)',
	'ext' => 'Evaluaci�n Extraordinaria (Septiembre)',
);


if (isset($_POST['curso'])) {
	$form_curso = $_POST['curso'];
	$exp_curso = explode('-->', $form_curso);
	$curso = $exp_curso[0];
	$asignatura = $exp_curso[1];
}
if (isset($_POST['evaluacion']) && !empty($_POST['evaluacion'])) $evaluacion = $_POST['evaluacion'];

// ENVIO DEL FORMULARIO
if (isset($_POST['submit'])) {

	$curso = $_POST['unidad'];
	$asignatura = $_POST['asignatura'];
	$evaluacion = $_POST['evaluacion'];
	
	$calificaciones = array();
	
	foreach ($_POST as $campo => $valor) {
		if ($campo != 'submit' && $campo != 'curso' && $campo != 'evaluacion') {
			
			$exp_campo = explode('-', $campo);
			$alumno = $exp_campo[1];
			
			if ($exp_campo[0] == 'nota') $alumno_nota = $valor;
			if ($exp_campo[0] == 'obs')  $alumno_obs  = $valor;
			
			if (isset($alumno_obs)) {
				$calif_alumno = array(
					array(
						'alumno' => $alumno,
						'nota'   => $alumno_nota,
						'obs'    => $alumno_obs,
					),
				);
				
				$calificaciones = array_merge($calificaciones, $calif_alumno);
				
				unset($alumno_obs);
			}
			
		}
	}
	
	$result = mysql_query("INSERT INTO evaluaciones (unidad, asignatura, evaluacion, profesor, calificaciones) VALUES ('$curso', '$asignatura', '$evaluacion', '".$_SESSION['profi']."', '".serialize($calificaciones)."')");
	
	if (!$result) {
		
		if (mysql_errno() == 1062) {
			$result1 = mysql_query("UPDATE evaluaciones SET calificaciones='".serialize($calificaciones)."' WHERE unidad='$curso' AND asignatura='$asignatura' AND evaluacion='$evaluacion' LIMIT 1");
			
			if (!$result1) $msg_error = "No se ha podido actualizar las calificaciones de la ".$evaluaciones[$evaluacion].". Error: ".mysql_error();
			else $msg_success = "Las calificaciones de la ".$evaluaciones[$evaluacion]." han sido actualizadas.";
		}
		else {
			$msg_error = "No se ha podido registrar las calificaciones de la ".$evaluaciones[$evaluacion].". Error: ".mysql_error();
		}
	}
	else {
		$msg_success = "Las calificaciones de la ".$evaluaciones[$evaluacion]." han sido registradas.";
	}
}


// RECUPERAMOS LOS DATOS DE LA EVALUACION
if ((isset($curso) && isset($asignatura)) && isset($evaluacion)) {
	$result = mysql_query("SELECT calificaciones FROM evaluaciones WHERE unidad='$curso' AND asignatura='$asignatura' AND evaluacion='$evaluacion' LIMIT 1");
	
	if (mysql_num_rows($result)) {
		$row = mysql_fetch_array($result);
		
		$calificaciones = unserialize($row['calificaciones']);
		
		for ($i = 0; $i < count($calificaciones); $i++) {
			$nota{'-'.$calificaciones[$i]['alumno']} = $calificaciones[$i]['nota'];
			$obs{'-'.$calificaciones[$i]['alumno']} = $calificaciones[$i]['obs'];
		}
	}
}

include("../../menu.php");
include("menu.php");
?>
	
	<div class="container">
		
		<!-- TITULO DE LA PAGINA -->
		<div class="page-header">
			<h2>Evaluaciones <small>Evaluar una unidad</small></h2>
		</div>
		
		<!-- MENSAJES -->
		<?php if (isset($msg_error)): ?>
		<div class="alert alert-danger">
			<?php echo $msg_error; ?>
		</div>
		<?php endif; ?>
		
		<?php if (isset($msg_success)): ?>
		<div class="alert alert-success">
			<?php echo $msg_success; ?>
		</div>
		<?php endif; ?>
		
		
		<div class="row hidden-print">
		
			<div class="col-sm-12">
			
				<form method="post" action="">
					
					<fieldset>
					
						<div class="well">
							
							<legend>Seleccione unidad y evaluaci�n</legend>
							
							<div class="row">
								
								<div class="col-sm-6">
								
									<div class="form-group">
										<label for="curso">Unidad</label>
										<?php $result = mysql_query("SELECT DISTINCT c_asig, asig, a_grupo FROM horw WHERE prof='".mb_strtoupper($_SESSION['profi'], 'iso-8859-1')."' AND nivel <> '' AND n_grupo <> '' AND a_asig NOT LIKE '%TUT%' ORDER BY a_grupo ASC"); ?>
										<select class="form-control" id="curso" name="curso" onchange="submit()">
											<option value=""></option>
											<?php while ($row = mysql_fetch_array($result)): ?>
											<option value="<?php echo $row['a_grupo'].'-->'.$row['c_asig']; ?>" <?php echo (isset($form_curso) && $form_curso == $row['a_grupo'].'-->'.$row['c_asig']) ? 'selected' : (isset($curso) && isset($asignatura) && $curso.'-->'.$asignatura == $row['a_grupo'].'-->'.$row['c_asig']) ? 'selected' : ''; ?>><?php echo $row['a_grupo']; ?> - <?php echo $row['asig']; ?></option>
											<?php endwhile; ?>
										</select>
									</div>
									
								</div>
								
								<div class="col-sm-6">
									
									<div class="form-group">
										<label for="evaluacion">Evaluaci�n</label>
										<select class="form-control" id="evaluacion" name="evaluacion" onchange="submit()">
											<option value=""></option>
											<?php foreach ($evaluaciones as $eval => $desc): ?>
											<option value="<?php echo $eval; ?>" <?php echo (isset($evaluacion) && $evaluacion == $eval) ? 'selected' : ''; ?>><?php echo $desc; ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									
								</div>
								
							</div>
								
							
						</div><!-- /.well -->
						
					</fieldset>
					
				</form>
				
			</div><!-- /.col-sm-12 -->
			
		</div><!-- /.row -->
		
		
		<?php if ((isset($curso) && isset($asignatura)) && isset($evaluacion)): ?>
		<div class="row">
		
			<div class="col-sm-12">
				
				<div class="visible-print">
					<h3><?php echo $evaluaciones[$evaluacion]; ?>  de <?php echo $curso; ?></h3>
				</div>
				
				<form method="post" action="">
					<input type="hidden" name="unidad" value="<?php echo $curso; ?>">
					<input type="hidden" name="asignatura" value="<?php echo $asignatura; ?>">
					<input type="hidden" name="evaluacion" value="<?php echo $evaluacion; ?>">
				
					<table class="table table-bordered table-striped table-hover table-vcentered">
						<thead>
							<tr>
								<th class="col-sm-4" colspan="2">Alumno/a</th>
								<th class="col-sm-1">Nota</th>
								<th class="col-sm-7">Observaciones</th>
							</tr>
						</thead>
						<tbody>
							<?php $result = mysql_query("SELECT apellidos, nombre, claveal FROM alma WHERE unidad='$curso'"); ?>
							<?php while ($row = mysql_fetch_array($result)): ?>
							<tr>
								<?php $foto = '../../xml/fotos/'.$row['claveal'].'.jpg'; ?>
								<?php if (file_exists($foto)): ?>
								<td class="text-center"><img class="img-thumbnail" src="<?php echo $foto; ?>" alt="<?php echo $row['apellidos'].', '.$row['nombre']; ?>" width="54"></td>
								<?php else: ?>
								<td class="text-center"><span class="fa fa-user fa-fw fa-3x"></span></td>
								<?php endif; ?>
								<td nowrap>
									<?php echo $row['apellidos'].', '.$row['nombre']; ?>
								</td>
								<td>
									<select class="form-control" name="nota-<?php echo $row['claveal']; ?>">
										<?php for ($i = 0; $i <= 10; $i++): ?>
										<option value="<?php echo $i; ?>" <?php echo (isset($nota{'-'.$row['claveal']}) && $nota{'-'.$row['claveal']} == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
										<?php endfor; ?>
									</select>
								</td>
								<td>
									<textarea class="form-control" name="obs-<?php echo $row['claveal']; ?>" rows="1"><?php echo (isset($obs{'-'.$row['claveal']}) && $obs{'-'.$row['claveal']}) ? $obs{'-'.$row['claveal']} : ''; ?></textarea>
								</td>
							</tr>
							<?php endwhile; ?>
						</tbody>
					</table>
					
					<div class="hidden-print">
						<button type="submit" class="btn btn-primary" name="submit">Registrar</button>
						<button type="reset" class="btn btn-default">Cancelar</button>
						<a href="#" class="btn btn-info" onclick="javascript:print();">Imprimir</a>
					</div>
					
				</form>
				
			</div><!-- /.col-sm-12 -->
			
		</div><!-- /.row -->
		<?php endif; ?>
	
	</div><!-- /.container -->

<? include("../../pie.php");?>
 
</body>
</html>