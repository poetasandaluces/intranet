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

// COMPROBACION DE ACCESO AL MODULO
if ((stristr($_SESSION['cargo'],'1') == false) && (stristr($_SESSION['cargo'],'2') == false) && (stristr($_SESSION['cargo'],'8') == false)) {
	
	if (isset($_SESSION['mod_tutoria'])) unset($_SESSION['mod_tutoria']);
	die ("<h1>FORBIDDEN</h1>");
	
}
else {
	
	// COMPROBAMOS SI ES EL TUTOR, SINO ES DEL EQ. DIRECTIVO U ORIENTADOR
	if (stristr($_SESSION['cargo'],'2') == TRUE) {
		
		$_SESSION['mod_tutoria']['tutor']  = $_SESSION['tut'];
		$_SESSION['mod_tutoria']['unidad'] = $_SESSION['s_unidad'];
		
	}
	else {
	
		if(isset($_POST['tutor'])) {
			$exp_tutor = explode('==>', $_POST['tutor']);
			$_SESSION['mod_tutoria']['tutor'] = trim($exp_tutor[0]);
			$_SESSION['mod_tutoria']['unidad'] = trim($exp_tutor[1]);
		}
		else{
			if (!isset($_SESSION['mod_tutoria'])) {
				header('Location:'.'tutores.php');
			}
		}
		
	}
}

registraPagina($_SERVER['REQUEST_URI'],$db_host,$db_user,$db_pass,$db);


if (isset($_GET['imprimir'])) {
	$imprimir = $_GET['imprimir'];
}
if (isset($_POST['observaciones1'])) {
	$observaciones1 = $_POST['observaciones1'];
}
if (isset($_POST['observaciones2'])) {
	$observaciones2 = $_POST['observaciones2'];
}

// SE DEFINE UNA VARIABLE PARA CARGAR LOS INCLUDES
define('INC_TUTORIA',1);

include("../../menu.php");
include("menu.php");
?>
<br />
<div style="width:960px;margin:auto;padding:25px; border:1px solid #ddd">
<h2 align="center">
 Tutor�a del grupo: <? echo $_SESSION['mod_tutoria']['unidad']; ?> <br /><small>Tutor: <? echo $_SESSION['mod_tutoria']['tutor']; ?></small></h2>
 <br />

 <?
if (isset($_POST['imp_memoria'])) {
	mysql_query("update FTUTORES set observaciones1 = '$observaciones1', observaciones2='$observaciones2' where tutor = '".$_SESSION['mod_tutoria']['tutor']."'");
	echo '<br /><div align="center"><div class="alert alert-success alert-block fade in">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
Las observaciones que has redactado han sido guardadas. Puedes a�adir y editar el texto tantas veces como quieras. O puedes volver a la p�gina de la memoria e imprimirla para entregarla en Jefatura.
</div></div><br />';
	echo '<center><input type="button" value="Volver a la Memoria de Tutor�a" name="boton" onclick="window.location.href = \'informe_memoria.php\'" class="btn btn-primary" /></center>';
	echo "</div>";
	include("../../pie.php");
	exit();
}
$lista = mysql_list_fields($db,"FTUTORES");
$col_obs = mysql_field_name($lista,3);
if ($col_obs=="observaciones1") { }else{
	mysql_query("ALTER TABLE  `FTUTORES` ADD  `observaciones1` TEXT NOT NULL ,
						        ADD  `observaciones2` TEXT NOT NULL");
}

 $obs1=mysql_query("select observaciones1, observaciones2 from FTUTORES where tutor = '".$_SESSION['mod_tutoria']['tutor']."'");
 $obs2=mysql_fetch_array($obs1);
 if (empty($obs2[0]) && empty($obs[1]) && date('m')==06) {$boton = "Redactar Observaciones finales para imprimir";$click="onclick=\"window.location.href = 'informe_memoria.php?imprimir=1#observaciones'\"";}
 	else{
		$boton = "Imprimir Memoria final de Tutor�a";$click="onClick=print()";}
 ?>
  <div style="margin-bottom:0px;">
 <input type="button" class="btn btn-primary no_imprimir pull-right" value="<? echo $boton;?>" <? echo $click;?>>
</div>
 
<br>
<br />
 <h3>Datos Generales de los Alumnos</h3><br />

 <? 
 // Curso
 $SQL0 = "select distinct curso from alma where unidad = '".$_SESSION['mod_tutoria']['unidad']."'";
 $result0 = mysql_query($SQL0);
 $max00 = mysql_fetch_row($result0);
 $curso_seneca = $max00[0];
// Alumnos que se integran a lo largo del Curso
 $SQL = "select max(NC) from FALUMNOS_primero where unidad = '".$_SESSION['mod_tutoria']['unidad']."'";
 $result = mysql_query($SQL);
 $max0 = mysql_fetch_row($result);
 $num_0 =  $max0[0];
 $SQL1 = "select max(NC) from FALUMNOS where unidad = '".$_SESSION['mod_tutoria']['unidad']."'";
 $result1 = mysql_query($SQL1);
 $max1 = mysql_fetch_row($result1);
 $num_1 =  $max1[0];
 $nuevos = $num_1-$num_0;
 $nuevos = str_replace("-","",$nuevos);
 
// Alumnos repetidores
 $SQL = "select * from alma where unidad = '".$_SESSION['mod_tutoria']['unidad']."' and matriculas > '1'";
 $result = mysql_query($SQL);
 $num_repetidores = mysql_num_rows($result);

// Alumnos a comienzo de Curso
 $SQL = "select * from FALUMNOS_primero where unidad = '".$_SESSION['mod_tutoria']['unidad']."'";
 $result = mysql_query($SQL);
 $num_empiezan = mysql_num_rows($result);
 
 // Alumnos a final de Curso
 $SQL = "select * from alma where unidad = '".$_SESSION['mod_tutoria']['unidad']."'";
 $result = mysql_query($SQL);
 $num_acaban = mysql_num_rows($result);

 // Alumnos que promocionan en Junio
 $SQL1 = "select notas3, apellidos, nombre from notas, alma where notas.claveal = alma.claveal1  and unidad = '".$_SESSION['mod_tutoria']['unidad']."'";
 $result1 = mysql_query($SQL1);

 while ($num_promo0 = mysql_fetch_array($result1))                                                                                                                                                           
{                                                                                                                                                                                                          
        $n_susp = "";                                                                                                                                                                                        
        $trozos0 = explode(";",$num_promo0[0]);                                                                                                                                                              
        foreach ($trozos0 as $val)                                                                                                                                                                           
        {                                                                                                                                                                                                    
        $trozos1 = explode(":",$val);                                                                                                                                                                        
                {                                                                                                                                                                                            
                if (stristr($curso_seneca."Bach")==TRUE)                                                                                                                                                                          
                        {                                                                                                                                                                                    
                        if (($trozos1[1] > "416" and $trozos1[1] < "427") or ($trozos1[1] == "439"))          
                                {                                                                                                                                                                            
                $n_susp = $n_susp + 1;                                                                                                                                                                       
                                }                                                                                                                                                                            
                        }                                                                                                                                                                                                                                                                                                                                                                       
                else                                                                                                                                                            
                        {                                                                                                                                                                                    
                if (($trozos1[1] > "336" and $trozos1[1] < "347" and $trozos1[1] !== "339" and $trozos1[1] !== ""))                                                                                          
                                {                                                                                                                                                                            
                $n_susp = $n_susp + 1;                                                                                                                                                                       
                                }                                                                                                                                                                            
                        }                                                                                                                                                                                    
                }                                                                                                                                                                                            
        }                                                                                                                                                                                                    
 if ($n_susp > "0" and (((stristr($curso_seneca."2")==TRUE) and (stristr($curso_seneca."Bach")==TRUE))  or (stristr($curso_seneca."4")==TRUE)))                                                                                                                                                                       
        {                                                                                                                                                                                                    
//              $valor = $valor ."$n_susp: $num_promo0[2] $num_promo0[1] --> $num_promo0[0]<br>";                                                                                                            
                $n_al = $n_al + 1;                                                                                                                                                                           
        }                                                                                                                                                                                                    
        elseif($n_susp > "2" and !(((stristr($curso_seneca."2")==TRUE) and (stristr($curso_seneca."Bach")==TRUE))  or (stristr($curso_seneca."4")==TRUE)))                                                                                                                                                          
        {                                                                                                                                                                                                    
//              $valor = $valor ."$n_susp: $num_promo0[2] $num_promo0[1] --> $num_promo0[0]<br>";                                                                                                            
                $n_al = $n_al + 1;                                                                                                                                                                           
        }                                                                                                                                                                                                    
}    

?>
<table class="table table-striped" style="width:auto;">
<tr>
    <th>Comienzan el Curso</th>
    <th>Terminan el Curso</th>
    <th>No Promocionan</th>
    <th>Promocionan</th>
    <th>Repetidores</th>
    <th>Nuevas Incorporaciones</th>
</tr>
<tr>
	<td><? echo $num_empiezan; ?></td>
    <td><? echo $num_acaban; ?></td>
    <td><? echo $n_al; // echo "<br>".$valor;?></td> 
    <td><? echo $num_acaban-$n_al; ?></td> 
    <td><? echo $num_repetidores; ?></td> 
    <td><? echo $nuevos; ?></td>     
    </tr>
</table>
<?
// Tabla de Absentismo.
 $faltas = "select distinct claveal from absentismo where unidad = '".$_SESSION['mod_tutoria']['unidad']."' order by claveal";
 $faltas0 = mysql_query($faltas);
 $num_faltas = mysql_num_rows($faltas0);
  ?>
 <? 
 $SQL = "select distinct id from Fechoria, FALUMNOS where FALUMNOS.claveal = Fechoria.claveal and unidad = '".$_SESSION['mod_tutoria']['unidad']."' order by Fechoria.claveal";
 $result = mysql_query($SQL);
 $num_conv = mysql_num_rows($result);
 ?>
  <?    
 $SQL = "select distinct id from Fechoria, FALUMNOS where FALUMNOS.claveal = Fechoria.claveal and unidad = '".$_SESSION['mod_tutoria']['unidad']."' and grave = 'leve' order by Fechoria.claveal";
 $result = mysql_query($SQL);
 $num_leves = mysql_num_rows($result);
 ?>
  <?    
 $SQL = "select distinct id from Fechoria, FALUMNOS where FALUMNOS.claveal = Fechoria.claveal and unidad = '".$_SESSION['mod_tutoria']['unidad']."' and grave = 'grave' order by Fechoria.claveal";
 $result = mysql_query($SQL);
 $num_graves = mysql_num_rows($result);
 ?>
   <?    
 $SQL = "select distinct id from Fechoria, FALUMNOS where FALUMNOS.claveal = Fechoria.claveal and unidad = '".$_SESSION['mod_tutoria']['unidad']."' and grave = 'muy grave' order by Fechoria.claveal";
 $result = mysql_query($SQL);
 $num_muygraves = mysql_num_rows($result);
 ?>
  <?    
 $SQL = "select distinct id from Fechoria, FALUMNOS where FALUMNOS.claveal = Fechoria.claveal and unidad = '".$_SESSION['mod_tutoria']['unidad']."' and expulsion > '0' order by Fechoria.claveal";
 $result = mysql_query($SQL);
 $num_expulsion = mysql_num_rows($result);
 ?>
  <?    
 $SQL = "select distinct Fechoria.claveal from Fechoria, FALUMNOS where FALUMNOS.claveal = Fechoria.claveal and unidad = '".$_SESSION['mod_tutoria']['unidad']."' and expulsion > '0' order by Fechoria.claveal";
 $result = mysql_query($SQL);
 $num_expulsados = mysql_num_rows($result);
 ?>
   <?    
 $SQL = "select distinct Fechoria.claveal from Fechoria, FALUMNOS where FALUMNOS.claveal = Fechoria.claveal and unidad = '".$_SESSION['mod_tutoria']['unidad']."' and expulsionaula = '1' order by Fechoria.claveal";
 $result = mysql_query($SQL);
 $num_expulsadosaula = mysql_num_rows($result);
 ?>
   <?    
 $SQL = "select distinct id from infotut_alumno where unidad = '".$_SESSION['mod_tutoria']['unidad']."' order by claveal";
 $result = mysql_query($SQL);
 $num_informes = mysql_num_rows($result);
 ?>
   <?    
 $SQL = "select id from tutoria where unidad = '".$_SESSION['mod_tutoria']['unidad']."' and prohibido not like '1' order by id";
 $result = mysql_query($SQL);
 $num_acciones = mysql_num_rows($result);
 ?>
   <?  
 $grupo_act = str_replace("-","",$_SESSION['mod_tutoria']['unidad']);  
 $SQL = "select * from actividades where grupos like '%$grupo_act%' order by id";
 $result = mysql_query($SQL);
 $num_actividades = mysql_num_rows($result);
 ?>
 <table class="table table-striped" style="width:auto;">
<tr>
    <th>Absentismo</th>
    <th>Problemas de Convivencia</th>
    <th>Informes de Tutor&iacute;a (Visitas de Padres)</th>
    <th>Intervenciones del Tutor</th>
    <th>Actividades Extraescolares</th>
</tr>
<tr>
	<td><? echo $num_faltas; ?></td>
    <td><? echo $num_conv; ?></td>
    <td><? echo $num_informes; ?></td> 
    <td><? echo $num_acciones; ?></td>
    <td><? echo $num_actividades; ?></td>
</tr>
</table>
<hr>
 <br /><h3>
 Informaci&oacute;n sobre Problemas de Convivencia</h3><br />
 <table class="table table-striped" style="width:auto;">
<tr>
    <th>Problemas Leves</th>
    <th>Problemas Graves</th>
	<th>Problemas Muy Graves</th>
    <th>Expulsiones</th>
    <th>Alumnos Expulsados</th>
	<th>Expulsi&oacute;n del Aula</th>
</tr>
<tr>
    <td><? echo $num_leves; ?></td>
    <td><? echo $num_graves; ?></td>
    <td><? echo $num_muygraves; ?></td>	
    <td><? echo $num_expulsion; ?></td>
    <td><? echo $num_expulsados; ?></td>
	<td><? echo $num_expulsadosaula; ?></td>
</tr>
</table>

 
 <hr><br /><h3>Informaci�n de Tutor�a por Alumno</h3>
  <div class="row">     
 <div class="col-sm-6">
 <hr><br /><legend>Alumnos absentistas</legend>

<?
$faltas = "select distinct absentismo.claveal, count(*), nombre, apellidos from absentismo, FALUMNOS where absentismo.claveal = FALUMNOS.claveal and absentismo.unidad = '".$_SESSION['mod_tutoria']['unidad']."'  group by apellidos, nombre";
 $faltas0 = mysql_query($faltas);
 if(mysql_num_rows($faltas0) > 0)
 {
 echo '<table class="table table-striped" style="width:auto;">';
 while($absentista = mysql_fetch_array($faltas0))
 {
 echo '<tr>
<td style="text-align:left">'.$absentista[2] .' '. $absentista[3].'</td><td>'.$absentista[1].'</td>
</tr>';
 }
 echo '</table>';
 }
 ?>
 </div> 

  <div class="col-sm-6">       
 <hr><br /><legend>Faltas sin Justificar</legend>

<?php
 echo "<table class='table table-striped' style='width:auto;'>";
		
$SQL = "select distinct FALTAS.claveal, count(*), apellidos, nombre from FALTAS, FALUMNOS  where FALTAS .claveal = FALUMNOS .claveal and FALTAS.falta = 'F' and FALTAS.unidad = '".$_SESSION['mod_tutoria']['unidad']."' and date(fecha) > '$inicio_curso' group BY apellidos, nombre";
$result = mysql_query($SQL);

  if ($row = mysql_fetch_array($result))
        {
	$hoy = date("d"). "-" . date("m") . "-" . date("Y");
                do {
	$claveal = $row[0];
          echo "<tr><td style='text-align:left'>$row[2], $row[3]</td><td style='text-align:left'>$row[1]</td></tr>";
        } while($row = mysql_fetch_array($result));
        }
		        echo "</table>";
  ?>
</div>
</div>
  <div class="row">     
 <div class="col-sm-4">
  <hr><br /><legend>Problemas de Convivencia</legend>

<?
$faltas = "select distinct Fechoria.claveal, count(*), nombre, apellidos from Fechoria, FALUMNOS where FALUMNOS.claveal = Fechoria.claveal and unidad = '".$_SESSION['mod_tutoria']['unidad']."' and date(fecha) > '$inicio_curso' group by NC";
 $faltas0 = mysql_query($faltas);
 if(mysql_num_rows($faltas0) > 0)
 {
 echo '<table class="table table-striped" style="width:auto;">';
  while($fech = mysql_fetch_array($faltas0))
 {
 echo '<tr>
<td style="text-align:left">'.$fech[2] .' '. $fech[3].'</td><td>'.$fech[1].'</td>
</tr>';
 }
 echo '</table>';
 }
 ?>
                   </div>
                    <div class="col-sm-4">
                    <hr><br /><legend>Alumnos expulsados</legend>

<?
  
 
 $faltas = "select distinct Fechoria.claveal, count(*), nombre, apellidos from Fechoria, FALUMNOS where FALUMNOS.claveal = Fechoria.claveal and unidad = '".$_SESSION['mod_tutoria']['unidad']."' and expulsion > '0' and date(fecha) > '$inicio_curso' group by NC";
 $faltas0 = mysql_query($faltas);
 if(mysql_num_rows($faltas0) > 0)
 {
 echo '<table class="table table-striped" style="width:auto;">';
 while($exp = mysql_fetch_array($faltas0))
 {
 echo '<tr>
<td style="text-align:left">'.$exp[2] .' '. $exp[3].'</td><td>'.$exp[1].'</td>
</tr>';
 }
 echo '</table>';
 }
 ?>
 </div> <div class="col-sm-4"><hr><br /><legend>Alumnos expulsados del aula</legend>

 <?
$faltas = "select distinct Fechoria.claveal, count(*), nombre, apellidos from Fechoria, FALUMNOS where FALUMNOS.claveal = Fechoria.claveal and unidad = '".$_SESSION['mod_tutoria']['unidad']."' and expulsionaula = '1' and date(fecha) > '$inicio_curso' group by NC";
 $faltas0 = mysql_query($faltas);
 if(mysql_num_rows($faltas0) > 0)
 {
 echo '<table class="table table-striped" style="width:auto;">';
 while($exp = mysql_fetch_array($faltas0))
 {
 echo '<tr>
<td style="text-align:left">'.$exp[2] .' '. $exp[3].'</td><td>'.$exp[1].'</td>
</tr>';
 }
 echo '</table>';
 }
 ?>
 </div>
 </div>
 
 <hr><br /><legend>Informes de Tutor�a por visita de padres</legend>

<?
 $faltas = "select distinct claveal, count(*), nombre, apellidos from infotut_alumno where unidad = '".$_SESSION['mod_tutoria']['unidad']."' and date(F_ENTREV) > '$inicio_curso' group by apellidos";
 $faltas0 = mysql_query($faltas);
 if(mysql_num_rows($faltas0) > 0)
 {
 echo '<table class="table table-striped" style="width:auto;">';
 while($infotut = mysql_fetch_array($faltas0))
 {
 echo '<tr>
<td style="text-align:left">'.$infotut[2] .' '. $infotut[3].'</td><td>'.$infotut[1].'</td>
</tr>';
 }
 echo '</table>';
 }
 ?>
 <div class="row">
  <div class="col-sm-5">
<hr><br /><legend>Intervenciones del Tutor</legend>

<?
 $faltas = "select distinct apellidos, nombre, count(*) from tutoria where unidad = '".$_SESSION['mod_tutoria']['unidad']."' and prohibido not like '1' and date(fecha) > '$inicio_curso' group by apellidos";
 $faltas0 = mysql_query($faltas);
 if(mysql_num_rows($faltas0) > 0)
 {
 echo '<table class="table table-striped" style="width:auto;">';
 while($tutoria = mysql_fetch_array($faltas0))
 {
 echo '<tr>
<td style="text-align:left">'.$tutoria[1] .' '. $tutoria[0].'</td><td>'.$tutoria[2].'</td>
</tr>';
 }
 echo '</table>';
 }
 
 $faltas = "select distinct apellidos, nombre, causa, accion, observaciones from tutoria where unidad = '".$_SESSION['mod_tutoria']['unidad']."' and prohibido not like '1' and accion not like '%SMS%'  and date(fecha) > '$inicio_curso' order by apellidos";
 $faltas0 = mysql_query($faltas);
 if(mysql_num_rows($faltas0) > 0)
 {
	 ?>
	 </div> <div class="col-sm-7">
 <hr><br /><legend>Intervenciones de Tutor�a (excluidos SMS)</legend>

     <?
 echo '<table class="table table-striped" style="width:auto;">';
 while($tutoria = mysql_fetch_array($faltas0))
 {
 echo '<tr>
<td style="text-align:left" nowrap>'.$tutoria[0] .', '. $tutoria[1].'</td><td style="text-align:left" >'.$tutoria[2].'</td><td style="text-align:left" >'.$tutoria[3].'</td>
</tr>';
 }
 echo '</table>';
 }
  $grupo_act2 = str_replace("-","",$_SESSION['mod_tutoria']['unidad']);  
  $n_activ = mysql_query("select * from actividades where  grupos like '%$grupo_act2-%' and date(fecha) > '$inicio_curso'");
  if(mysql_num_rows($n_activ) > "0"){
 ?>
  </div>
  </div>
  
 <hr><br /><legend>Informe sobre Actividades Extraescolares del Grupo</legend>
 <?
include("inc_actividades.php");
include("informe_notas.php");
 ?>
 <?
 }
 ?>
<?

if($imprimir == "1" or strlen($obs2[0]) > "1" or strlen($obs[1])>"1")
{
?>
<a name="observaciones" id="obs"></a>
<hr><br /><legend>
 Observaciones sobre dificultades encontradas en el Grupo<br />(Integraci�n, Motivaci�n, Rendimiento acad�mico, etc.)</legend>
 <form action="" method="POST">
 <textarea class="form-control" name="observaciones1" rows="7"><? echo $obs2[0];?></textarea>
 <hr>
<br />
<legend>
 Otras Observaciones</legend>
 <textarea class="form-control" name="observaciones2" rows="7"><? echo $obs2[1];?></textarea>
 <br />
<input type="hidden" name="tutor" value="<? echo $_SESSION['mod_tutoria']['tutor']; ?>">
<input type="hidden" name="unidad" value="<? echo $_SESSION['mod_tutoria']['unidad']; ?>">
<br />
<input type="submit" name="imp_memoria" value="Enviar datos" class="btn btn-primary no_imprimir">
</form>
<?
if((strlen($obs2[0]) > "1" or strlen($obs[1])>"1"))
{
?>
<br />
  <p align="center">En <?php echo $localidad_del_centro; ?> a   <? $today = date("d") . "/" . date("m") . "/" . date("Y"); echo $today;?></p>
  <br>
<p align="center">EL Tutor</p>
<br>
<br>
<br>
<p align="center">Fdo. <?  echo $_SESSION['mod_tutoria']['tutor']; ?></p>
<br />
<?
}
}
 ?>
 </div>
 <script language="JavaScript">
 function doPrint(){
 document.all.item("noprint").style.visibility='hidden' 
 window.print()
 document.all.item("noprint").style.visibility='visible'
 }
 </script>
 <script type="text/javascript">
 function countLines(strtocount, cols) {
     var hard_lines = 1;
     var last = 0;
     while ( true ) {
         last = strtocount.indexOf("\n", last+1);
         hard_lines ++;
         if ( last == -1 ) break;
     }
     var soft_lines = Math.round(strtocount.length / (cols-1));
     var hard = eval("hard_lines  " + unescape("%3e") + "soft_lines;");
     if ( hard ) soft_lines = hard_lines;
     return soft_lines;
 }
 function cleanForm() {
     var the_form = document.forms[0];
     for ( var x in the_form ) {
         if ( ! the_form[x] ) continue;
         if( typeof the_form[x].rows != "number" ) continue;
         the_form[x].rows = countLines(the_form[x].value,the_form[x].cols) +3;
     }
     setTimeout("cleanForm();", 300);
 }
 </script>
 <?php include("../../pie.php"); ?>
</body>
</html>