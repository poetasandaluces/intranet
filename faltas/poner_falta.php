<?
session_start();
include("../config.php");
// COMPROBAMOS LA SESION
if ($_SESSION['autentificado'] != 1) {
	$_SESSION = array();
	session_destroy();
	
	if(isset($_SERVER['HTTPS'])) {
	    if ($_SERVER["HTTPS"] == "on") {
	        header('Location:'.'https://'.$dominio.'/intranet/salir.php');
	        exit();
	    } 
	}
	else {
		header('Location:'.'http://'.$dominio.'/intranet/salir.php');
		exit();
	}
}

if($_SESSION['cambiar_clave']) {
	if(isset($_SERVER['HTTPS'])) {
	    if ($_SERVER["HTTPS"] == "on") {
	        header('Location:'.'https://'.$dominio.'/intranet/clave.php');
	        exit();
	    } 
	}
	else {
		header('Location:'.'http://'.$dominio.'/intranet/clave.php');
		exit();
	}
}


registraPagina($_SERVER['REQUEST_URI'],$db_host,$db_user,$db_pass,$db);


?>
<?
include("../menu.php");
if (isset($_GET['menu_cuaderno'])) {
	include("../cuaderno/menu.php");
	echo "<br>";
	$extra = "&menu_cuaderno=1&profesor=".$_SESSION['profi']."&dia=$dia&hora=$hora&curso=$curso&asignatura=$asignatura";
}
else {
	include("menu.php");
}
// nprofe hora ndia hoy codasi profesor clave
if (isset($_POST['nprofe'])) {$nprofe = $_POST['nprofe'];} else{$nprofe="";}
if (isset($_POST['hora'])) {$hora = $_POST['hora'];} else{$hora="";}
if (isset($_POST['ndia'])) {$ndia = $_POST['ndia'];} else{$ndia="";}
if (isset($_POST['hoy'])) {$hoy = $_POST['hoy'];} else{$hoy="";}
if (isset($_POST['codasi'])) {$codasi = $_POST['codasi'];} else{$codasi="";}
if (isset($_POST['profesor'])) {$profesor = $_POST['profesor'];} else{$profesor="";}
if (isset($_POST['clave'])) {$clave = $_POST['clave'];} else{$clave="";}
if (isset($_POST['fecha_dia'])) {$fecha_dia = $_POST['fecha_dia'];} else{$fecha_dia="";}
?>

<div class="container">

<div class="page-header">
<h2>Faltas de Asistencia <small> Poner faltas</small></h2>
</div>

<div class="row"><?		
// Borramos faltas para luego colocarlas de nuevo.
$borra = mysqli_query($db_con, "delete from FALTAS where HORA = '$hora' and FECHA = '$hoy' and PROFESOR = '$nprofe' and (FALTA = 'F' or FALTA = 'J')");
$db_pass = trim($clave);
foreach($_POST as $clave => $valor)
{
	if(strlen(strstr($clave,"falta_")) > 0)
	{
		$nc0 = explode("_",$clave);
		$nc = $nc0[1];
		// Nivel y grupo
		$unidad = $nc0[2];

		$clave1 = "select claveal from FALUMNOS where NC = '$nc' and unidad = '$unidad'";
		$clave0 = mysqli_query($db_con, $clave1);
		$clave2 = mysqli_fetch_row($clave0);
		$claveal = $clave2[0];

		$diames = date("j");
		$nmes = date("n");
		$nano = date("Y");
		$hoy_hoy = mktime(0,0,0,$nmes,$diames,$nano);

		$fecha0 = explode('-',$hoy);
		$dia0 = $fecha0[0];
		$mes0 = $fecha0[1];
		$ano0 = $fecha0[2];

		$hoy2 = strtotime($hoy);

		$comienzo_del_curso = strtotime($inicio_curso);
		
		// Tiene actividad extraescolar en la fecha 
		
		/*$extraescolar=mysqli_query($db_con, "select cod_actividad from actividadalumno where claveal = '$claveal' and cod_actividad in (select id from calendario where date(fechaini) >= date('$hoy') and date(fechafin) <= date('$hoy'))");
		//echo "select cod_actividad from actividadalumno where claveal = '$claveal' and cod_actividad in (select id from calendario where date(fechaini) >= date('$hoy') and date(fechafin) <= date('$hoy'))<br>";
		if (mysqli_num_rows($extraescolar) > '0') {
			while($actividad = mysqli_fetch_array($extraescolar)){
			$tr = mysqli_query($db_con,"select * from calendario where id = '$actividad[0]' and hour(horaini)>= (select hour(hora_inicio) from jornada where tramo = '$hora') and hour(horafin)<= (select hour(hora_fin) from jornada where tramo = '$hora')");
			//echo "select * from calendario where id = '$actividad[0]'  and hour(horaini)>= (select hour(hora_inicio) from jornada where tramo = '$hora') and hour(horafin)<= (select hour(hora_fin) from jornada where tramo = '$hora')";	
			}
			
		}*/
		
		// Es festivo
		$fiesta=mysqli_query($db_con, "select fecha from festivos where date(fecha) = date('$hoy')");

		if (mysqli_num_rows($fiesta) > '0') {
			$dia_festivo='1';
		}

		if($dia_festivo=='1')
		{
			$mens_fecha = "No es posible poner Faltas en un <b>D�a Festivo</b> o en <b>Vacaciones</b>. <br>Comprueba la Fecha: <b>$hoy</b>";
		}
		elseif ($hoy2 > $hoy_hoy) {
			$mens_fecha = "No es posible poner Faltas en el <b>Futuro</b>.<br>Comprueba la Fecha: <b>$hoy</b>.";
		}
		elseif ($hoy2 < $comienzo_del_curso) {
			$mens_fecha = "No es posible poner Faltas del <b>Curso Anterior</b>.<br>Comprueba la Fecha: <b>$hoy</b>.";
		}
		else{
			// Insertamos las faltas de TODOS los alumnos.
			$t0 = "insert INTO  FALTAS (  CLAVEAL , unidad ,  NC ,  FECHA ,  HORA , DIA,  PROFESOR ,  CODASI ,  FALTA )
//VALUES ('$claveal',  '$unidad', '$nc',  '$hoy',  '$hora', '$ndia',  '$nprofe',  '$codasi', 'F')";
			//	echo $t0;
			$t1 = mysqli_query($db_con, $t0) or die("No se han podido insertar los datos");
			$count += mysqli_affected_rows();
		}

	}
}
if (empty($mens_fecha)) {
	echo '<br /><div align="center"><div class="alert alert-success alert-block fade in">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            Las Faltas han sido registradas.
          </div></div>'; 
}
else{
	echo '<br /><div align="center"><div class="alert alert-danger alert-block fade in">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            '. $mens_fecha.'</div></div>'; 
}
?> 

<script language="javascript">
setTimeout("window.location='index.php?fecha_dia=<? if (!empty($fecha_dia)) {  echo $fecha_dia;}else {echo date('d-m-Y');}?>&hora_dia=<? echo $hora; ?><? echo $extra;?>'", 3000) 
</script> 

</body>
</html>