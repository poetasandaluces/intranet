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
<?
include("../../menu.php");
include("menu.php");
?>
<div class="container">
<div class="row">
<div class="page-header">
  <h2>Informes de Tareas <small> Profesores que no redactan Informe</small></h2>
</div>
<br>

<?
$detalles = '1';
?>
<div align="center">
<FORM action="control-luis.php" method="POST" class="well well-large" style="width:450px;">
<div class="form-group">
<label>Informe sobre un Profesor</label>
  <SELECT  name=profes onChange="submit()" class="form-control">
    <option></option>
    <?
  $profe = mysql_query(" SELECT distinct profesor FROM profesores order by profesor asc");
  if ($filaprofe = mysql_fetch_array($profe))
        {
        do {

	      $opcion1 = printf ("<OPTION>$filaprofe[0]</OPTION>");
	      echo "$opcion1";

	} while($filaprofe = mysql_fetch_array($profe));
        }
	?>
  </select>
</div>
</FORM>
</div>

<div class="col-sm-1"></div> 
<div class="col-sm-6" align="left">    
<br />     
<?
$hoy = date('Y-m-d');
//echo $hoy;
 
mysql_connect($db_host, $db_user, $db_pass) or die ("Imposible conectar!");
mysql_select_db($db) or die ("Imposible seleccionar base de datos!");
//  Estructura de tabla para la tabla `tareas_temp`

mysql_query("CREATE TABLE IF NOT EXISTS `tareas_temp` (
  `id` int(11) NOT NULL auto_increment,
  `id_tareas` int(11) NOT NULL default '0',
  `asignatura` varchar(32) NOT NULL default '',
  `profesor` varchar(32) NOT NULL default '',
  `alumno` int(8) NOT NULL default '0',
  `curso` varchar(4) NOT NULL default '',
  `fecha` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`id`),
  KEY `id_tareas` (`id_tareas`),
  KEY `asignatura` (`asignatura`),
  KEY `profesor` (`profesor`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=313 ");

$query = "SELECT id, claveal, unidad, duracion, nombre, apellidos, FECHA FROM tareas_alumnos order by FECHA desc";
$result = mysql_query($query);
if($detalles == '1')
{ 
echo '<h4>Detalles de cada Informe individual</h4><br />';
} 
while($row = mysql_fetch_array($result))
{
$todas = "";
$asig = "";
$inf = "select asignatura from tareas_profesor where id_alumno = $row[0]";
$comp = mysql_query($inf);
while($cadena = mysql_fetch_array($comp))
{
	$todas.=$cadena[0]. ";";
}
//echo $todas."<br>";
if($detalles == '1')
{ 
	echo "<p>$row[6] --> <span style='color:#08c'>$row[6] --> $row[4] $row[5] --> $row[2]</p>";
	
} 
echo "<ul  class='unstyled'>";

//echo "$todos<br>";
$combasi0 = "select combasi, curso from alma where claveal = '$row[1]'";
//echo "$combasi0<br>";
$combasi1 = mysql_query($combasi0);
$combasi2 = mysql_fetch_array($combasi1);
$l_nivel = $combasi2[1];
$combasi = substr($combasi2[0],0,strlen($combasi2[0]) - 1);
$trozo = explode(":",$combasi);
foreach($trozo as $asignatura)
{
$nomasi0 = "select distinct nombre from asignaturas where codigo = '$asignatura' and abrev not like '%\_%' and curso like '$l_nivel%'";
//echo "$nomasi0<br>";
$nomasi1 = mysql_query($nomasi0);
while($nomasi = mysql_fetch_array($nomasi1))
{
$profesores = "";
$pos = strpos($todas,$nomasi[0]);
if($pos === FALSE)
{

$profe0 = "select distinct profesor from profesores where  profesores.grupo = '$row[2]' and materia like '$nomasi[0]' and profesor not in (select tutor from FTUTORES where unidad = '$row[2]') ";

$profe1 = mysql_query($profe0);
while($profe2 = mysql_fetch_array($profe1))
{
$query = "insert into tareas_temp (id_tareas, asignatura, profesor, alumno, fecha, curso) values ('$row[0]','$nomasi[0]','$profe2[0]','$row[1]','$row[6]','$row[2]')";
mysql_query($query);
$profesores .= $profe2[0]."; ";
}
$profesores = substr($profesores,0,strlen($profesores) - 2);
//$query2 = "insert into tareas_temp2 (id_tareas,asignatura, alumno, fecha, profesor) values ('$row[0]','$nomasi[0]','$cadena[0]','$cadena[5]','$profesores')";
//mysql_query($query2);
if(strlen($profesores) > 0)
{
if($detalles == '1')
{ 
	echo "<li><i class='fa fa-user'> </i> $profesores ==> $nomasi[0]</li>";
} 
}
}
}
}
if($detalles == '1')
{ 
echo "</ul>";
echo "<hr width='90%' />";} 
}
?>
</div>
<div class="col-sm-4">
<br />
<?
echo '<h4>Resultados globales por Profesor</h4><br />';
$malo0 = "select profesor, count(*) as total from tareas_temp group by profesor";
//echo "$combasi0<br>";
$malo1 = mysql_query($malo0);
echo "<table class='table table-striped'><tr><th>Profesor</th><th>Total</th></tr>";
while($malo2 = mysql_fetch_array($malo1))
{
echo "<tr><td>$malo2[0]</td><td>$malo2[1]</td></tr>";
}
?>
</table>
<br />
<?
echo '<h4>Resultados globales por Asignatura</h4><br />';
$malo0 = "select distinct asignatura, count(*) as total from tareas_temp group by asignatura";
//echo "$combasi0<br>";
$malo1 = mysql_query($malo0);
echo "<table class='table table-striped'><tr><th>Profesor</th><th>Total</th></tr>";
while($malo2 = mysql_fetch_array($malo1))
{
echo "<tr><td>$malo2[0]</td><td>$malo2[1]</td></tr>";
}
?>
</table>
</div>
</div>
<? 
mysql_query("drop table tareas_temp");

	include("../../pie.php");
?>
</body>
</html>