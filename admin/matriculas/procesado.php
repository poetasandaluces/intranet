<?php
	if ($_POST) {
		foreach ($_POST as $key=>$val){
			$n_curso = substr($curso, 0, 1);
			$curso_anterior = $n_curso-1;
			//echo "$key --> $val<br>";
			$tr = explode("-",$key);
			$id_submit = $tr[1];
			$col = $tr[0];
			if ($col == 'confirmado'){$con.=$id_submit." ";}
			if ($col == 'exencion'){$exen.=$id_submit." ";}
			if ($col == 'bilinguismo'){$bili.=$id_submit." ";}
			if ($col == 'diversificacion'){$diver.=$id_submit." ";}
			if ($col == 'revisado'){$revis.=$id_submit." ";}
			if ($col == "grupo_actual"){$val=strtoupper($val);}
			if ($col=='promociona'){
				if ($val=='3') {
					// Resplado de datos modificados
				$n_promo = mysql_query("select promociona from matriculas where id = '$id_submit'");	
				$n_prom = mysql_fetch_array($n_promo);
				if (!($n_prom[0]=='3')) {
				mysql_query("insert into matriculas_backup select * from matriculas where id = '$id_submit'");
				$promo = "select optativa21, optativa22, optativa23, optativa24, optativa25, optativa26, optativa27, act21 from matriculas where id = '$id_submit'";
				$prom = mysql_query($promo);
				$pro = mysql_fetch_array($prom);
				$cambia_datos = "update matriculas set optativa1 = '$pro[0]', optativa2 = '$pro[1]', optativa3 = '$pro[2]', optativa4 = '$pro[3]', optativa5 = '$pro[4]', optativa6 = '$pro[5]', optativa7 = '$pro[6]', act1 = '$pro[7]', curso = '".$curso_anterior."ESO' ";
				if ($curso=="4ESO") {
				$cambia_datos.=", itinerario = '', matematicas4 = '' ";
				}
				$cambia_datos.=" where id = '$id_submit'";
				mysql_query($cambia_datos);
				}
				}
				else{
					mysql_query("update matriculas set promociona='$val' where id  = '$id_submit'");
				}
			}
			
			mysql_query("update matriculas set $col = '$val' where id = '$id_submit'");
			mysql_query("update matriculas set confirmado = '' where id = '$id_submit'");
			mysql_query("update matriculas set exencion = '' where id = '$id_submit'");
			mysql_query("update matriculas set bilinguismo = '' where id = '$id_submit'");
			mysql_query("update matriculas set diversificacion = '' where id = '$id_submit'");	
			mysql_query("update matriculas set revisado = '' where id = '$id_submit'");
		}
		
		$tr_con = explode(" ",$con);
		foreach ($tr_con as $clave){
			mysql_query("update matriculas set confirmado = '1' where id = '$clave'");
		}
		$tr_con2 = explode(" ",$exen);
		foreach ($tr_con2 as $clave_exen){
			mysql_query("insert into matriculas_backup select * from matriculas where id = '$clave_exen'");
			mysql_query("update matriculas set exencion = '1', optativa1 = '', optativa2 = '', optativa3 = '', optativa4 = '', act1='2' where id = '$clave_exen'");
		}
	
		$tr_con3 = explode(" ",$bili);
		foreach ($tr_con3 as $clave_bili){
			mysql_query("update matriculas set bilinguismo = 'Si' where id = '$clave_bili'");
		}
		$tr_con4 = explode(" ",$diver);
		foreach ($tr_con4 as $clave_diver){
			mysql_query("insert into matriculas_backup select * from matriculas where id = '$clave_diver'");
			mysql_query("update matriculas set diversificacion = '1', optativa1 = '', optativa2 = '', optativa3 = '', optativa4 = '', optativa5 = '', optativa6 = '', optativa7 = '', act1='', itinerario = '', matematicas4 = '' where id = '$clave_diver'");
		}
		$tr_con5 = explode(" ",$revis);
		foreach ($tr_con5 as $clave_revis){
			mysql_query("update matriculas set revisado = '1' where id = '$clave_revis'");
		}
	}
	?>
	
	
	
	
	
	