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


if(!(stristr($_SESSION['cargo'],'1') == TRUE))
{
header("location:http://$dominio/intranet/index.php");
exit;	
}
?>
<? include("../../menu.php");?>
      <div class="page-header">
  <h2>Programa de Ayudas al Estudio <small> Importaci�n de datos</small></h2>
</div>
<br />
<?
if(isset($_POST['enviar']))
{
// Nivel de los Libros
if(substr($_FILES['archivo']['name'],0,1) == '1') {$nivel = '1� de E.S.O.';}
if(substr($_FILES['archivo']['name'],0,1) == '2') {$nivel = '2� de E.S.O.';}
if(substr($_FILES['archivo']['name'],0,1) == '3') {$nivel = '3� de E.S.O.';}
if(substr($_FILES['archivo']['name'],0,1) == '4') {$nivel = '4� de E.S.O.';}
$nombre_nivel = $_FILES['archivo']['name'];
 // Creamos Base de datos y enlazamos con ella.
 $base0 = "delete from textos_gratis where nivel = '$nivel'";
 mysql_query($base0);
// Importamos los datos del fichero CSV (todos_alumnos.csv) en la tab�a alma.
$handle = fopen ($_FILES['archivo']['tmp_name'] , "r" ) or die("<br><blockquote>No se ha podido abrir el fichero.<br> Aseg�rate de que su formato es correcto.</blockquote>"); 
while (($data1 = fgetcsv($handle, 1000, "|")) !== FALSE) 
{
$datos1 = "INSERT INTO textos_gratis (materia, isbn, ean, editorial, titulo, ano, caducado, importe, utilizado, nivel) VALUES (\"". trim($data1[0]) . "\",\"". trim($data1[1]) . "\",\"". trim($data1[2]) . "\",\"". trim($data1[3]) . "\",\"". trim($data1[4]) . "\",\"". trim($data1[5]) . "\",\"". trim($data1[6]) . "\",\"". trim($data1[7]) . "\",\"". trim($data1[8]) . "\",\"". $nivel . "\")";
// echo $datos1."<br>";
mysql_query($datos1);
}
fclose($handle);
$borrarvacios = "delete from textos_gratis where editorial = ''";
mysql_query($borrarvacios);
echo '<div align="center"><div class="alert alert-success alert-block fade in">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
Tabla de Libros de Texto Gratuitos: los datos de '.$nombre_nivel.' han sido introducidos correctamente.
</div></div><br />';
}
?>
<div align="center">
<input type="button" name="Volver atr�s" onclick="history.back(1)" class="btn btn-primary" value="Volver atr�s"/>
</div>
</div>
</body>
</html>
