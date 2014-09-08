<?php
mysql_query("CREATE TABLE IF NOT EXISTS `actualizacion` (
  `d` int(11) NOT NULL AUTO_INCREMENT,
  `modulo` varchar(128) COLLATE latin1_spanish_ci NOT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`d`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci AUTO_INCREMENT=1 ");
/*
	@descripcion: Fotos de alumnos y profesores en base de datos se mueven a directorio.
	@fecha: 5 de agosto de 2013
*/
$fotos_dir = "./xml/fotos";

$d = dir($fotos_dir);
while (false !== ($entry = $d->read())) {
   $fotos_ya+=1;
}

$result=mysql_query("SELECT datos, nombre FROM fotos");
if (mysql_num_rows($result)>0 and $fotos_ya < "10") {
	while($row = mysql_fetch_array($result)){
		$foto_al = $fotos_dir."/".$row[1];
		# Creamos cada uno de los archivos
		file_put_contents($foto_al,$row[0], FILE_APPEND);	
	}
} 

$fotos_profe_dir = "./xml/fotos_profes"; 
$d_profes = dir($fotos_profe_dir);
while (false !== ($entry_profes = $d_profes->read())) {
   $fotos_profes_ya+=1;
}
$result_profe=mysql_query("SELECT datos, nombre FROM fotos_profes");
if ($result_profe) {
$fila = mysql_num_rows($result_profe);	
if ($fila > "0" and $fotos_profes_ya < "10") {
	while($row_profe = mysql_fetch_array($result_profe)){
		$foto_profe = $fotos_profe_dir."/".$row_profe[1];
		# Creamos cada uno de los archivos
		file_put_contents($foto_profe,$row_profe[0], FILE_APPEND);	
	}   
}
}


/*
	@descripcion: Actualizaci�n de la tabla de noticias
	@fecha: 5 de agosto de 2013
*/
$actua = mysql_query("select modulo from actualizacion where modulo = 'Tabla de Noticias'");
if (mysql_num_rows($actua)>0) {}else{
$hay = mysql_query("show tables");

while ($tabla=mysql_fetch_array($hay)) {
	if ($tabla[0]=="profes") {
		$ya_hay = mysql_query("select * from profes");
		
		if (mysql_num_rows($ya_hay)>0) {
			mysql_query("RENAME TABLE  `profes` TO  `noticias`");
			mysql_query("ALTER TABLE  `noticias` ADD  `pagina` TINYINT( 2 ) NOT NULL");
			mysql_query("update noticias set pagina = '1'");
		}
		else {
			mysql_query("RENAME TABLE  `profes` TO  `noticias`");
			mysql_query("ALTER TABLE  `noticias` ADD  `pagina` TINYINT( 2 ) NOT NULL");
		}
	}
}
mysql_query("insert into actualizacion (modulo, fecha) values ('Tabla de Noticias', NOW())");	
}


/*
	@descripcion: Actualizaci�n juego de caracteres
	@fecha: 11 de septiembre de 2013
	
	@nota: Esta tarea puede demorarse unos segundos

*/
$actua = mysql_query("select modulo from actualizacion where modulo = 'Base de datos espanol'");
if (mysql_num_rows($actua)>0) {}
else{
$flag = FALSE;

// Comprobamos el juego de caracteres de la base de datos principal
$schema_faltas = mysql_fetch_array(mysql_query("SELECT default_collation_name FROM information_schema.SCHEMATA WHERE schema_name = '$db'"));


if ( $schema_faltas[0] != "latin1_spanish_ci" ) {

	// Cambiamos el juego de caracteres de la base de datos
	mysql_query("ALTER DATABASE $db CHARACTER SET latin1 COLLATE latin1_spanish_ci") or die (mysql_error());
	
	// Cambiamos el juego de caracteres de cada tabla de la base de datos
	$todas_tablas = mysql_query("SHOW TABLES FROM $db");
	while ($tabla = mysql_fetch_array($todas_tablas)) {
		$nomtabla = $tabla[0];
		mysql_query("ALTER TABLE $nomtabla CONVERT TO CHARACTER SET latin1 COLLATE latin1_spanish_ci") or die (mysql_error());
	}
	
	$flag = TRUE;
}

// Comprobamos el juego de caracteres de la base de datos de reservas
$schema_reservas = mysql_fetch_array(mysql_query("SELECT default_collation_name FROM information_schema.SCHEMATA WHERE schema_name = '$db_reservas'"));

if ( $schema_reservas[0] != "latin1_spanish_ci" ) {
	
	// Cambiamos el juego de caracteres de la base de datos
	mysql_query("ALTER DATABASE $db_reservas CHARACTER SET latin1 COLLATE latin1_spanish_ci") or die (mysql_error());;
	
	// Cambiamos el juego de caracteres de cada tabla de la base de datos
	$todas_tablas = mysql_query("SHOW TABLES FROM $db_reservas");
	while ($tabla = mysql_fetch_array($todas_tablas)) {
		$nomtabla = $tabla[0];
		mysql_query("ALTER TABLE $nomtabla CONVERT TO CHARACTER SET latin1 COLLATE latin1_spanish_ci") or die (mysql_error());;
	}
	
	$flag = TRUE;
}

unset($schema_faltas);
unset($schema_reservas);

if ( $flag ) {
	unset($todas_tablas);
	unset($tabla);
	unset($nomtabla);
}
mysql_query("insert into actualizacion (modulo, fecha) values ('Base de datos espanol', NOW())");	
}
/*
	@descripcion: Actualizaci�n tabla notas_cuaderno
	@fecha: 5 de abril de 2014

*/
$actua = mysql_query("select modulo from actualizacion where modulo = 'Cuaderno visible exterior'");
if (mysql_num_rows($actua)>0) {}
else{
	
if ( mysql_num_rows(mysql_query("SHOW COLUMNS FROM notas_cuaderno LIKE 'visible_nota'")) == 0 ) {
	mysql_query("ALTER TABLE  `notas_cuaderno` ADD  `visible_nota` INT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `oculto`");
}
mysql_query("insert into actualizacion (modulo, fecha) values ('Cuaderno visible exterior', NOW())");	
}
/*
	@descripcion: Reducci�n del tama�o de las fotos de los alumnos (fotos superiores a 40 KB).
	@fecha: 1 de mayo de 2014

*/
$actua = mysql_query("select modulo from actualizacion where modulo = 'Tamano de las fotos'");
if (mysql_num_rows($actua)>0) {}
else{
	
function redimensionar_jpeg($img_original, $img_nueva, $img_nueva_anchura, $img_nueva_altura, $img_nueva_calidad)
{ 
	$img = ImageCreateFromJPEG($img_original); 
	$thumb = imagecreatetruecolor($img_nueva_anchura,$img_nueva_altura); 
	ImageCopyResized($thumb,$img,0,0,0,0,$img_nueva_anchura,$img_nueva_altura,ImageSX($img),ImageSY($img)); 
	ImageJPEG($thumb,$img_nueva,$img_nueva_calidad);
	ImageDestroy($img);
}

for ($i = 0; $i < 4; $i++) {
	
$d=dir("./xml/fotos");

while($archivo=$d->read()){

$img_fuente=$d->path.'/'.$archivo; 
$img_destino='./xml/fotos/'.$archivo; 

$size=getimagesize($img_fuente);
$size2 = filesize($img_fuente);

if($size[0]>40 and $size[0]<200){ 
$img_nueva_anchura = $size[0]/1.2;
$img_nueva_altura = $size[1]/1.2;
}
if($size[0]>200 and $size[0]<300){ 
$img_nueva_anchura = $size[0]/1.5;
$img_nueva_altura = $size[1]/1.5;
}
if($size[0]>300 and $size[0]<400){ 
$img_nueva_anchura = $size[0]/1.8;
$img_nueva_altura = $size[1]/1.8;
}
if($size[0]>400 and $size[0]<500){ 
$img_nueva_anchura = $size[0]/2.1;
$img_nueva_altura = $size[1]/2.1;
}
if($size[0]>500){ 
$img_nueva_anchura = $size[0]/2.6;
$img_nueva_altura = $size[1]/2.6;
}

if($size2>40000 and size<1500000){ 
$img_nueva_calidad = "95";
redimensionar_jpeg($img_fuente, $img_destino, $img_nueva_anchura, $img_nueva_altura, $img_nueva_calidad);
}
} 

$d->close();

}
mysql_query("insert into actualizacion (modulo, fecha) values ('Tamano de las fotos', NOW())");	
}

// Elimiaci�n de Nivel y Grupo
$actua = mysql_query("select modulo from actualizacion where modulo = 'Final Nivel-Grupo'");
if (mysql_num_rows($actua)>0) {}else{	
	
	$cur = substr($inicio_curso,0,4)+1;
for ($i=$cur;$i>$cur-5;$i--)
{
	//$b_d = "";
	if ($i == $cur){
		$b_d = $db;
	}
	else{
		$b_d = $db.$i;
	}
	mysql_select_db($b_d);	
	
	$base_datos = $b_d;
	$db_tabla2 = mysql_query("show tables from $base_datos");
	while ($arr2 = mysql_fetch_array($db_tabla2)) {
		
		$tabla2 = $arr2[0];	
		$query2 = mysql_query("select distinct nivel, grupo from $tabla2");
		if ($query2 and $tabla2 !== "profesores" and $tabla2 !== "alma" and $tabla2 !== "Textos") {
		mysql_query("ALTER TABLE  `$tabla2` ADD  `unidad` VARCHAR( 64 ) NOT NULL AFTER  `nivel`");
		while ($result3 = mysql_fetch_array($query2)) {
		mysql_query("update $base_datos.$tabla2 set unidad = '$result3[0]-$result3[1]' where nivel = '$result3[0]' and grupo = '$result3[1]'");
		}

		mysql_query("ALTER TABLE `$tabla2` DROP `nivel`");
		mysql_query("ALTER TABLE `$tabla2` DROP `grupo`");
		}			
	}
}
	mysql_query("insert into actualizacion (modulo, fecha) values ('Final Nivel-Grupo', NOW())");	
}
	// Actualizar datos de libros de texto a la desaparici�n de nivel-grupo
$actua = mysql_query("select modulo from actualizacion where modulo = 'Tamano de a_grupo'");
if (mysql_num_rows($actua)>0) {}else{
mysql_query("ALTER TABLE  `horw` CHANGE  `a_grupo`  `a_grupo` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT  ''");
mysql_query("ALTER TABLE  `horw_faltas` CHANGE  `a_grupo`  `a_grupo` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT  ''");
mysql_query("insert into actualizacion (modulo, fecha) values ('Tamano de a_grupo', NOW())");	
}
	
?>