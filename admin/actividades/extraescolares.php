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
?>
  <?php
include("../../menu.php");
include("menu.php");  
  ?>
<div class="container">
<div class="row">
<div class="page-header">
  <h2>Actividades Complementarias y Extraescolares <small> Selecci�n de alumnos</small></h2>
</div>
</div>

<div class="col-sm-6 col-sm-offset-3">
    <div class="well well-lg">       
<a href="javascript:seleccionar_todo()" class="btn btn-primary btn-sm">Marcar todos</a>
<a href="javascript:deseleccionar_todo()" class="btn btn-primary btn-sm">Desmarcar todos</a>
<br />
    <FORM action="imprimir.php" method="POST" name="imprime">

  <?
$cursos0 = mysql_query("select grupos, profesor from actividades where id = '$id'");
while($cursos = mysql_fetch_array($cursos0))
{
$profesor="";
$profes="";
$profes = explode(";",$cursos[1]);
foreach ($profes as $n_profe)
{
$profe = explode(", ",$n_profe);
$profesor.=$profe[1]." ".$profe[0].", ";
//$profesor.=$profeso.",";
}
$profesor = substr($profesor,0,-5);
$trozos = explode("-",$cursos[0]);
foreach($trozos as $valor)
{
$unidad = $valor;
$alumnos0 = "select alma.nombre, alma.apellidos, NC, alma.claveal from alma, FALUMNOS where alma.claveal = FALUMNOS.claveal and REPLACE(alma.unidad,'-','') = '$unidad' order by NC";
//echo $alumnos0;
$alumnos1 = mysql_query($alumnos0);
$num = mysql_num_rows($alumnos1);
if($alumno = mysql_fetch_array($alumnos1))
{
$datos0 = "select fecha, horario, profesor, actividad, descripcion from actividades where id ='$id'";
$datos1 = mysql_query($datos0);
$datos = mysql_fetch_array($datos1);
$fecha0 = explode("-",$datos[0]);
$fecha  = $fecha0[2]."-". $fecha0[1]."-". $fecha0[0];
$horario = $datos[1];
$actividad = $datos[3];
$descripcion = $datos[4];
?>
<input name="fecha" type="hidden" id="A" value="<? echo $fecha;?>">
<input name="horario" type="hidden" id="A" value="<? echo $horario;?>">
<input name="profesor" type="hidden" id="A" value="<? echo $profesor;?>">
<input name="actividad" type="hidden" id="A" value="<? echo $actividad;?>">
<input name="descripcion" type="hidden" id="A" value="<? echo $descripcion;?>">
<input name="id" type="hidden" id="A" value="<? echo $id;?>">   
<table class="table table-striped table-condensed">
<tr><td colspan="2"><h4><? echo "Alumnos de $unidad";?></h4></td>
</tr>

<?
do{
$apellidos = $alumno[0];
$nombre = $alumno[1];
$nc = $alumno[2];
$claveal = $alumno[3];
?>
<tr><td >
<input name="<? echo $nc.$claveal;?>" type="checkbox" id="A" value="<? echo $claveal;?>"> </td>
<td>   
<?
echo " $nc. $apellidos $nombre</td></tr>";
}while($alumno = mysql_fetch_array($alumnos1));
?>
</table>
<?
}}
}
?>
<br />
<button type="submit" name="submit" value="Imprimir Carta para los Padres" class="btn btn-primary">Imprimir Carta para los Padres</button>
  </FORM>
  </div>
  </div>
  </div>
</div> 
  <script>
function seleccionar_todo(){
	for (i=0;i<document.imprime.elements.length;i++)
		if(document.imprime.elements[i].type == "checkbox")	
			document.imprime.elements[i].checked=1
}
function deseleccionar_todo(){
	for (i=0;i<document.imprime.elements.length;i++)
		if(document.imprime.elements[i].type == "checkbox")	
			document.imprime.elements[i].checked=0
}
</script>
<? include("../../pie.php");?>