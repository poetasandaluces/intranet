<?
session_start();
include("../../config.php");
if($_SESSION['autentificado']!='1')
{
session_destroy();
header("location:http://$dominio/intranet/salir.php");	
exit;
}
registraPagina($_SERVER['REQUEST_URI'],$db_host,$db_user,$db_pass,$db);

if(!(stristr($_SESSION['cargo'],'1') == TRUE))
{
header("location:http://$dominio/intranet/salir.php");
exit;	
}
?>
<?
 include("../../menu.php");
 include("../menu.php");
?>
<br />
<div align="center">
<div class="page-header" align="center">
  <h2>Faltas de Asistencia <small> Subir faltas a S&eacute;neca</small></h2>
</div>
<br />
<?
if (isset($_GET['iniciofalta'])) {$iniciofalta = $_GET['iniciofalta'];}elseif (isset($_POST['iniciofalta'])) {$iniciofalta = $_POST['iniciofalta'];}
if (isset($_GET['finfalta'])) {$finfalta = $_GET['finfalta'];}elseif (isset($_POST['finfalta'])) {$finfalta = $_POST['finfalta'];}
if (isset($_GET['Submit'])) {$Submit = $_GET['Submit'];}elseif (isset($_POST['Submit'])) {$Submit = $_POST['Submit'];}

?>
<?

if (isset($iniciofalta) and isset($finfalta)) {

	$dir = "./origen/";
	$fecha0 = explode("/",$iniciofalta);
	$fecha10 = explode("/",$finfalta);
// Refrescamos la tabla de los tramos
mysql_query("truncate table tramos");

// Recorremos directorio donde se encuentran los ficheros y aplicamos la plantilla.
if ($handle = opendir($dir)) {
	$ni=0;
while (false !== ($file = readdir($handle))) {
//header('Content-Type: text/xml');
   	
$doc = new DOMDocument('1.0', 'iso-8859-1');
/*Cargo el XML*/
$doc->load( './origen/'.$file );
// Variables comunes
$curso = explode("_",$file);
$nivel = strtoupper(substr($curso[0],0,2));
$grupo = strtoupper(substr($curso[0],2,1));

// Un archivo de ESO y otro de Bach para los tramos
if (strstr($file,"1EA") == TRUE or strstr($file,"1BA") == TRUE) {       	
$ni+=1;

$tramos = $doc->getElementsByTagName( "TRAMO_HORARIO" );
 
foreach( $tramos as $tramo )
{	
$codigos0 = $tramo->getElementsByTagName( "X_TRAMO" );
$codigo0 = $codigos0->item(0)->nodeValue;
$nombres0 = $tramo->getElementsByTagName( "T_HORCEN" );
$nombre0 = $nombres0->item(0)->nodeValue;

mysql_query("INSERT INTO  `tramos` 
VALUES ('$nombre0',  '$codigo0')");
}	
}

$x_ofert = $doc->getElementsByTagName( "X_OFERTAMATRIG" );
$d_ofert = $doc->getElementsByTagName( "D_OFERTAMATRIG" );
$x_unida = $doc->getElementsByTagName( "X_UNIDAD" );
$t_nombr = $doc->getElementsByTagName( "T_NOMBRE" );
$x_oferta = $x_ofert->item(0)->nodeValue;
$d_oferta = $d_ofert->item(0)->nodeValue;
$x_unidad = $x_unida->item(0)->nodeValue;
$t_nombre = $t_nombr->item(0)->nodeValue;
$n_curso=utf8_decode($d_oferta); 
$n_curso1 = utf8_decode($n_curso);     
$hoy = date('d/m/Y')." 08:00:00"; 
$ano_curso=substr($inicio_curso,0,4); 
$xml="<SERVICIO>
  <DATOS_GENERALES>
    <MODULO>FALTAS DE ASISTENCIA</MODULO>
    <TIPO_INTERCAMBIO>I</TIPO_INTERCAMBIO> 
    <AUTOR>SENECA</AUTOR>
    <FECHA>$hoy</FECHA>
    <C_ANNO>$ano_curso</C_ANNO>
    <FECHA_DESDE>$iniciofalta</FECHA_DESDE>
    <FECHA_HASTA>$finfalta</FECHA_HASTA>
    <CODIGO_CENTRO>$codigo_del_centro</CODIGO_CENTRO>
    <NOMBRE_CENTRO>$nombre_del_centro</NOMBRE_CENTRO>
    <LOCALIDAD_CENTRO>$localidad_del_centro</LOCALIDAD_CENTRO>
  </DATOS_GENERALES>
  <CURSOS>
    <CURSO>
      <X_OFERTAMATRIG>$x_oferta</X_OFERTAMATRIG>
      <D_OFERTAMATRIG>$n_curso</D_OFERTAMATRIG>
      <UNIDADES>
        <UNIDAD>
          <X_UNIDAD>$x_unidad</X_UNIDAD>
          <T_NOMBRE>$t_nombre</T_NOMBRE>
          <ALUMNOS>";
//echo "$cabecera";
$alumn = $doc->getElementsByTagName( "ALUMNO" );
foreach ($alumn as $alumno){
	$x_matricul = $alumno->getElementsByTagName( "X_MATRICULA" );
	$x_matricula = $x_matricul->item(0)->nodeValue;	
	$clavea = $alumno->getElementsByTagName( "C_NUMESCOLAR" );
	$claveal = $clavea->item(0)->nodeValue;

	$xml.="
	<ALUMNO>
       <X_MATRICULA>$x_matricula</X_MATRICULA>
       <FALTAS_ASISTENCIA>";

	include 'exportado.php';
	
	$xml.="
        </FALTAS_ASISTENCIA>
       </ALUMNO>";

}
$xml.="         
     </ALUMNOS>
    </UNIDAD>
   </UNIDADES>
  </CURSO>
 </CURSOS>
</SERVICIO>";
$fp1=fopen("exportado/".$file."","w");
$pepito2=fwrite($fp1,$xml);
}
}

if ($ni==0) {
	echo '<div align="center""><div class="alert alert-error alert-block fade in" style="max-width:500px;" align="left">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
	Parece que no hay archivos que se puedan procesar en el directorio /faltas/seneca/origen/. Aseg�rate de que el directorio contiene los archivos exportados desde S�neca..
			</div></div><br />';
exit();
}
?>
<div align="center""><div class="alert alert-success alert-block fade in" style="max-width:500px;" align="left">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
	 Las Faltas de Asistencia se han escrito correctamente en los archivos  del directorio /exportado/. <br />Puedes proceder a importarlos a S�neca.
			</div></div><br />
<?
}
else{
	
	?>
<div align="center""><div class="alert alert-success alert-block fade in" style="max-width:500px;" align="left">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
Selecciona las fechas de comienzo y final del registro de faltas en el formulario.
			</div></div><br />	
	<?
}
?>