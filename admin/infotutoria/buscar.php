<?php
  if(isset($_GET['todos']) and $_GET['todos'] == "1") { 
  $titulo = "Todos los Informes en este a�o escolar";
} else { 
  $titulo = "Informes que responden a los datos introducidos";
}
  if(isset($_GET['ver']) or isset($_POST['ver'])) { 
  $id = $_GET['ver'];
  include("infocompleto.php");
exit;}
  if(isset($_GET['meter']) or isset($_POST['meter'])) { 
  $id = $_GET['llenar'];
  include("informar.php");
exit;
}
$profesor = $_SESSION['profi'];
?>
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

<style type="text/css">
.table td{
	vertical-align:middle;
}
</style>
  <?php


$PLUGIN_DATATABLES = 1;

include("../../menu.php");
include("menu.php");
?>
<div class="container">
<div class="row">
<div class="page-header">
  <h2>Informes de Tutor�a <small> Buscar Informes</small></h2>
</div>
<br>

<div class="col-md-8 col-md-offset-2">

<?php
if (isset($_POST['apellidos'])) {$apellidos = $_POST['apellidos'];}else{$apellidos="";}
if (isset($_POST['nombre'])) {$nombre = $_POST['nombre'];}else{$nombre="";}
if (!(empty($unidad))) {
$grupo = $unidad;
}
// Consulta
 $query = "SELECT ID, CLAVEAL, APELLIDOS, NOMBRE, unidad, tutor, F_ENTREV
  FROM infotut_alumno WHERE 1=1 "; 
  if(!(empty($apellidos))) {$query .= "and apellidos like '%$apellidos%'";} 
  if(!(empty($nombre))) {$query .=  "and nombre like '%$nombre%'";} 
  if(!(empty($unidad))) {$query .=  "and unidad = '$unidad'";} 
  $query .=  " ORDER BY F_ENTREV DESC";
$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());

echo "<table class='table table-striped table-bordered datatable' align='center'><thead>";
echo "<th>Alumno </th>
<th>Curso</th>
<Th>Cita</th><th></th></thead><tbody>";
if (mysql_num_rows($result) > 0)
{

	while($row = mysql_fetch_object($result))
	{
   echo "<tr><td nowrap style='vertical-align:middle'>";
		$foto="";
		$foto = "<img src='../../xml/fotos/".$row->CLAVEAL.".jpg' width='55' height='64'  />";
		echo $foto."&nbsp;&nbsp;";	
   echo "$row->NOMBRE $row->APELLIDOS</TD>
   <TD style='vertical-align:middle' nowrap>$row->unidad</TD>
   <TD style='vertical-align:middle' nowrap>$row->F_ENTREV</TD>";
echo "<td style='vertical-align:middle' nowrap><div class='btn-group'><a href='infocompleto.php?id=$row->ID' class='btn btn-primary'><i class='fa fa-search ' title='Ver Informe'> </i></a>";	

$result0 = mysql_query ( "select tutor from FTUTORES where unidad = '$row->unidad'" );
$row0 = mysql_fetch_array ( $result0 );	
$tuti = $row0[0];
		 if (stristr($_SESSION ['cargo'],'1') == TRUE or ($tuti == $_SESSION['profi'])) {
   	echo "<a href='borrar_informe.php?id=$row->ID&del=1' class='btn btn-primary' data-bb='confirm-delete'><i class='fa fa-trash-o ' title='Borrar Informe' > </i></a>";
   	echo "<a href='informar.php?id=$row->ID' class='btn btn-primary'><i class='fa fa-pencil-square-o ' title='Rellenar Informe'> </i> </a>";
   }	
echo '</div></td></tr>';
	}
echo "</tbody></table><br />";
}
// Si no hay datos
else
{
	echo '<br /><div align="center"><div class="alert alert-warning alert-block fade in" style="max-width:500px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
			<h5>ATENCI�N:</h5>
No hay Informes de Tutor&iacute;a disponibles.</div></div><hr>';
}
?>
<?
if(mysql_num_rows($result0) > 50) {
?>
<a href="buscar.php?pag=<? echo $pag;?>" class="btn btn-primary">Siguientes 50 Informes</a>
<? 
}
?>
</div>
</div>

		</div>
<? include("../../pie.php");?>		

	<script>
	$(document).ready(function() {
		var table = $('.datatable').DataTable({
			"paging":   true,
	    "ordering": true,
	    "info":     false,
	    
			"lengthMenu": [[15, 35, 50, -1], [15, 35, 50, "Todos"]],
			
			"order": [[ 2, "desc" ]],
			
			"language": {
			            "lengthMenu": "_MENU_",
			            "zeroRecords": "No se ha encontrado ning�n resultado con ese criterio.",
			            "info": "P�gina _PAGE_ de _PAGES_",
			            "infoEmpty": "No hay resultados disponibles.",
			            "infoFiltered": "(filtrado de _MAX_ resultados)",
			            "search": "Buscar: ",
			            "paginate": {
			                  "first": "Primera",
			                  "next": "�ltima",
			                  "next": "",
			                  "previous": ""
			                }
			        }
		});
	});
	</script>

</body>
</html>