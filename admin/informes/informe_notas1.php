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
<br />
<div align="center" style="max-width:920px;margin:auto;">
<div class="page-header">
  <h2>Informe de Evaluaciones <small> Estad�sticas de Calificaciones</small></h2>
</div>

<?
if (isset($_POST['f_curso']) and !($_POST['f_curso'] == "Curso actual")) {
	$f_curs = substr($_POST['f_curso'],5,4);
	$base_actual = $db.$f_curs;
	//echo $base_actual;
	$conex = mysql_select_db($base_actual);
	if (!$conex) {
		echo "Fallo al seleccionar la base de datos $base_actual";
	}
	else{
		mysql_query("drop table cursos");
		mysql_query("create table cursos select * from $db.cursos");
		//echo "create table if not exists cursos select * from $db.cursos";
	}
}
else{
	$conex = mysql_select_db($db);
}
$act1 = substr($curso_actual,0,4);
$b_act1 = ($act1-1)."-".$act1;
$base=$db.$act1;
$act2=$act1-1;
$b_act2 = ($act2-1)."-".$act2;
$act3=$act1-2;
$b_act3 = ($act3-1)."-".$act3;
$act4=$act1-3;
$b_act4 = ($act4-1)."-".$act4;
if (mysql_query("select * from $base.notas")) {
?>
<form method="POST" class="well well-large" style="width:450px; margin:auto">
<p class="lead">Informe Hist�rico</p>
<select name="f_curso" onchange="submit()">
<?
echo "<option>".$_POST['f_curso']."</option>";
echo "<option>Curso actual</option>";
for ($i=1;$i<5;$i++){
	$base_contr = $db.($act1-$i);
	$sql_contr = mysql_query("select * from $base_contr.notas");
	if (mysql_num_rows($sql_contr)>0) {
		echo "<option>${b_act.$i}</option>";
	}
}
?>
</select>
</form>
<hr />
<?
}
?>
<div class="tabbable" style="margin-bottom: 18px;">
<ul class="nav nav-tabs">
<li class="active"><a href="#tab1" data-toggle="tab">1� Evaluaci�n</a></li>
<li><a href="#tab2" data-toggle="tab">2� Evaluaci�n</a></li>
<li><a href="#tab3" data-toggle="tab">Evaluaci�n Ordinaria</a></li>
</ul>

<div class="tab-content" style="padding-bottom: 9px; border-bottom: 1px solid #ddd;">
<? 
// Comprobamos datos de evaluaciones
$n1 = mysql_query("select * from notas where notas1 not like ''");
if(mysql_num_rows($n1)>0){}
else{
	echo '<div align="center"><div class="alert alert-warning alert-block fade in">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
			<h5>ATENCI�N:</h5>No hay datos de Calificaciones en la tabla NOTAS. Debes importar las Calificaciones desde S�neca (Administraci�n de la Intranet --> Importar Calificaciones) para que este m�dulo funcione.
          </div></div>';
	exit();
}
?>


<?
$titulos = array("1"=>"1� Evaluaci�n","2"=>"2� Evaluaci�n","3"=>"Evaluaci�n Ordinaria");
foreach ($titulos as $key=>$val){
	
// Creamos la tabla en cada evaluaci�n
 $crea_tabla = "CREATE TABLE IF NOT EXISTS `suspensos` (
  `claveal` varchar(12) NOT NULL,
  `suspensos` tinyint(4) NOT NULL,
  `pil` tinyint(4) NOT NULL,
  `grupo` varchar( 64 ) NOT NULL,
  `nivel` varchar( 64 ) NOT NULL,
  KEY `claveal` (`claveal`)
)";
 mysql_query($crea_tabla);
	
	$key == '1' ? $activ=" active" : $activ='';
?>
<div class="tab-pane fade in<? echo $activ;?>" id="<? echo "tab".$key;?>">
<h3>Resultados de los Alumnos por Nivel</h3><br />
<p class="help-block text-warning" align="left">En 4� de ESO y 2� de Bachillerato, los alumnos titulan con <strong>0</strong> asignaturas suspensas. En el resto de los grupos de ESO y Bachillerato los alumnos promocionan con <strong>2 o menos</strong> asignaturas suspensas. </p>
<table class="table table-striped table-bordered"  align="center" style="width:auto" valign="top">
<thead>
<th></th>
<th class='text-info'>Alumnos</th>
<th class='text-warning'>Repiten</th>
<th>0 Susp</th>
<th>1-2 Susp</th>
<th>3-5 Susp</th>
<th>6-8 Susp</th>
<th>9+ Susp.</th>
<th class='text-success'>Promo./Tit.</th>
</thead>
<tbody>
<?
// Evaluaciones ESO
$nivele = mysql_query("select * from cursos");
while ($orden_nivel = mysql_fetch_array($nivele)){
$niv = mysql_query("select distinct curso, nivel, idcurso from alma, cursos where curso=nomcurso and curso = '$orden_nivel[1]'");
while ($ni = mysql_fetch_array($niv)) {
	$idn = $ini[2];
	if ($idn=="101140") { $nivel="1E"; }
	elseif ($idn=="101141") { $nivel="2E"; }
	elseif ($idn=="101142") { $nivel="3E"; }
	elseif ($idn=="6029" or $idn=="2063") { $nivel="1B"; }
	else{ $nivel = $ni[1]; }
	$n_grupo+=1;
	$curso = $ni[0];
	
	$rep = ""; 
	$promo = "";
$notas1 = "select notas". $key .", claveal1, matriculas, unidad, nivel from alma, notas where alma.CLAVEAL1 = notas.claveal and alma.curso = '$curso'";
//echo $notas1."<br>";

$result1 = mysql_query($notas1);
$todos = mysql_num_rows($result1);
if ($todos < '1') {
	echo '<div align="center"><div class="alert alert-warning alert-block fade in">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
			<h5>ATENCI�N:</h5>No hay datos de Calificaciones en la tabla NOTAS. Debes importar las Calificaciones desde S�neca (Administracci�n --> Importar Calificaciones) para que este m�dulo funcione.
          </div></div>';
}
while($row1 = mysql_fetch_array($result1)){
$asignatura1 = substr($row1[0], 0, strlen($row1[0])-1);
$claveal = $row1[1];
$grupo = $row1[3];
$nivel_curso = $row1[4];
if ($row1[2]>"1") {
	$pil = "1";
}
else{
	$pil = '0';
}
$trozos1 = explode(";", $asignatura1);
$num = count($trozos1);
$susp="";
 for ($i=0;$i<$num; $i++)
  {
$bloque = explode(":", $trozos1[$i]);
$nombreasig = "select nombre from calificaciones where codigo = '" . $bloque[1] . "'";
$asig = mysql_query($nombreasig);
$cali = mysql_fetch_row($asig);
if($cali[0] < '5' and !($cali[0] == ''))	{
	$susp+=1; 
	}
		mysql_query("insert into temp values('','$claveal','$bloque[0]','$cali[0]')");
	}
	
mysql_query("insert into suspensos  (
`claveal` ,
`suspensos` ,
`pil` ,
`grupo`,
`nivel`
)
VALUES (
'$claveal',  '$susp',  '$pil', '$grupo', '$curso'
)");
	}

// Calculamos
$cer = mysql_query("select distinct claveal, grupo from suspensos where nivel = '$curso' and suspensos = '0'");
$cero = '';
$cero=mysql_num_rows($cer);

$uno_do = mysql_query("select distinct claveal, grupo from suspensos where nivel = '$curso' and suspensos > '0' and suspensos < '3'");
$uno_dos='';
$uno_dos=mysql_num_rows($uno_do);

$tres_cinc = mysql_query("select distinct claveal, grupo from suspensos where nivel = '$curso' and suspensos > '2' and suspensos < '6'");
$tres_cinco='';
$tres_cinco=mysql_num_rows($tres_cinc);

$seis_och = mysql_query("select distinct claveal, grupo from suspensos where nivel = '$curso' and suspensos > '5' and suspensos < '9'");
$seis_ocho='';
$seis_ocho=mysql_num_rows($seis_och);

$nuev = mysql_query("select distinct claveal, grupo from suspensos where nivel = '$curso' and suspensos > '8'");
$nueve='';
$nueve=mysql_num_rows($nuev);

//$tota = mysql_query("select distinct notas.claveal from notas, alma where alma.claveal1 = notas.claveal and nivel = '$curso'");
$tota = mysql_query("select distinct claveal from suspensos where nivel = '$curso'");
$total='';
$total=mysql_num_rows($tota);

// Promocion
	$extra1 = " and suspensos = '0'";
	$prom1 = mysql_query("select distinct claveal, grupo from suspensos where nivel = '$curso' and grupo not like '1E%' and grupo not like '2E%' and grupo not like '3E%' and grupo not like '1B%'  $extra1");
	$promo1=mysql_num_rows($prom1);
	if ($promo1==0) { $promo1=""; }

	$extra2 = " and suspensos < '3'";
	$prom2 = mysql_query("select distinct claveal, grupo from suspensos where nivel = '$curso' and (grupo like '1E%' or grupo like '2E%' or grupo like '3E%' or grupo like '1B%')  $extra2");
	$promo2=mysql_num_rows($prom2);
	if ($promo2==0) { $promo2=""; }

$n_pil = mysql_query("select distinct claveal, grupo from suspensos where nivel = '$curso' and pil = '1'");
$num_pil='';
$num_pil=mysql_num_rows($n_pil);

$porcient = (($promo1+$promo2)*100)/$total;
$porciento='';
if ($porcient>49) {
	$porciento = "<span class='text-success'>".substr($porcient,0,5)."%</span>";
}
else{
	$porciento = "<span class='text-danger'>".substr($porcient,0,5)."%</span>";	
}

?>

<tr>
<th><? echo $curso;?></th>
<th class='text-info'><? echo $total;?></th>
<td class='text-warning'><? echo $num_pil;?></td>
<td><? echo $cero;?></td>
<td><? echo $uno_dos;?></td>
<td><? echo $tres_cinco;?></td>
<td><? echo $seis_ocho;?></td>
<td><? echo $nueve;?></td>
<th><? echo $porciento." <span class='pull-right'>(".$promo2."".$promo1.")</span>";?></th>
</tr>
<?
}
}
?>
</tbody>
</table>

<!--  Estad�sticas por asignatura -->
<br />
</div>
<?
mysql_query("drop table suspensos");
mysql_query("drop table temp");
}
mysql_close();
?>
</div>
</div>
</div>
</div>

<? include("../../pie.php");?> 
</body>
</html>
                                                                                                                                                                                                  