<?
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


$profe = $_SESSION['profi'];
if(!(stristr($_SESSION['cargo'],'1') == TRUE))
{
	header("location:http://$dominio/intranet/salir.php");
	exit;
}

include("../../menu.php");
?>


<div class="container">
	
	<!-- TITULO DE LA PAGINA -->
	<div class="page-header">
		<h2>Administraci�n <small>Importaci�n de datos del centro</small></h2>
	</div>
	
	<?php $result = mysql_query("SELECT * FROM cursos LIMIT 1"); ?>
	<?php if(mysql_num_rows($result) && !isset($_FILES['ExpGenHor'])): ?>
	<div class="alert alert-warning">
		Ya existe informaci�n relativa a este curso escolar. Este proceso sustituir� parte de la informaci�n almacenada. Los cambios realizados manualmente en las dependencias y departamentos no se ver�n afectadas. Es recomendable realizar una <a href="copia_db/index.php" class="alert-link">copia de seguridad</a> antes de proceder a la importaci�n de los datos.
	</div>
	<?php endif; ?>
	
	<?php if(isset($_FILES['ExpGenHor'])): ?>
	<div class="alert alert-success">
		Los datos del centro han sido importados.
	</div>
	<?php endif; ?>
	
	
	<!-- SCAFFOLDING -->
	<div class="row">
	
		<!-- COLUMNA IZQUIERDA -->
		<div class="col-sm-6">
			
			<div class="well">
				
				<form enctype="multipart/form-data" method="post" action="">
					<fieldset>
						<legend>Importaci�n de datos del centro</legend>
						
						<input type="hidden" name="curso_escolar" value="<?php echo $curso_actual; ?>">
						
						<div class="form-group">
						  <label for="ExpGenHor"><span class="text-info">ExportacionHorarios.xml</span></label>
						  <input type="file" id="ExpGenHor" name="ExpGenHor" accept="text/xml">
						</div>
						
						<br>
						
					  <button type="submit" class="btn btn-primary" name="enviar">Importar</button>
					  <a class="btn btn-default" href="../index.php">Volver</a>
				  </fieldset>
				</form>
				
			</div><!-- /.well -->
			
			<?php
			$ExpGenHor = $_FILES['ExpGenHor']['tmp_name'];
			if (isset($ExpGenHor)) {
				include ('importacion_xml.php');
				importarDatos();
			}
			?>
			
		</div><!-- /.col-sm-6 -->
		
		
		<div class="col-sm-6">
			
			<h3>Informaci�n sobre la importaci�n</h3>
			
			<p>Este m�dulo se encarga de importar la relaci�n de <strong>cursos</strong> y <strong>unidades</strong> del centro registrados en S�neca, as� como la relaci�n de <strong>materias</strong> que se imparten y <strong>actividades</strong> del personal docente, necesarias para comprobar la coherencia de los horarios y poder realizar tareas de depuraci�n. Se importar� tambi�n la relaci�n de <strong>dependencias</strong>, que se utilizar� para realizar reservas de aulas o consultar el horario de aulas.</p>
			
			<p>Para obtener el archivo de exportaci�n debe dirigirse al apartado <strong>Utilidades</strong>, <strong>Importaci�n/Exportaci�n de datos</strong>. Seleccione <strong>Exportaci�n hacia generadores de horario</strong> y proceda a descargar el archivo XML.</p>
			
		</div>
		
	
	</div><!-- /.row -->
	
</div><!-- /.container -->
  
<?php include("../../pie.php"); ?>
	
</body>
</html>