<?php
require_once('class.weather.php');
$t = new Weather();

$data = new Weather();

if( isset( $_GET['realTime'] ) ) :
@header('Content-type: application/json');
	
	exit( $data->realTime() );
endif;

if( isset( $_GET['updateChart'] ) ) :
@header('Content-type: application/json');	
  exit( $data->realTime('chart') ); 
endif;



?>
<html>

<meta charset="UTF-8">
<head>
	<title>Monitor do clima - GV</title>
	<link href="css/bootstrap.css" rel="stylesheet">
</head>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=187006538132678";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<body>
	<div class="container">
		<div class="col-sm-8 ">
			<table class="table table-hover">
		        <thead>
		          <tr>
		          	<th><i class="wi-thermometer ic"></i></th>
		            <th><i class="wi-strong-wind ic"></i></th>
		            <th>Condição</th>
		            <th>Velocidade do vento</th>
		            <th>Umidade</th>
		             <th>Pressão</th>
		          </tr>
		        </thead>
		        <tbody>
		          <tr>
		          	<td><?php echo  $t->getWeaterData('temp'); ?></td>
		            <td><?php echo  $t->getWeaterData('winddirection'); ?></td>
		            <td><?php echo $t->getWeaterData('condition'); ?></td>
		            <td><?php echo $t->getWeaterData('windspeed'); ?></td>
		            <td><?php echo $t->getWeaterData('umidade'); ?></td>
		            <td><?php echo $t->getWeaterData('pressure'); ?></td>
		          </tr>
		        </tbody>
			</table>
			<canvas id="introChart" width="730" height="263"></canvas>
			<hr />
			<a href="sobre.php">Sobre</a> <p class="load" style="display:none;float:right"><img src="images/loader.GIF"> Atualizando...</p>
		
		</div>
	
	<div class="col-sm-4 ">
		<div class="river-monitor">
			<label for="nivel">Nível Rio Doce Agora - GV<br />	
				<?php echo $t->getRiverData('date') .' - '. $t->getRiverData('hour'); 	?> 

			</label>
			<br />

			<input type="checkbox" id="liveupdate" /> Parar atualização em tempo real.
			<br />
			<ul class="nav nav-pills nav-stacked">
				<li class="active">
					<a href="#">
						<span class="badge pull-right"><?php echo $t->getRiverData('level'); ?></span>
						Nível atual
					</a>
				</li>
			</ul>
			<table class="table table-hover" id="nu">
				<thead>
					<tr>
					<th>Nível</th>
					<th>Data</th>
					<th>Hora</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach( $t->getRecords() as $data ) :?>
					<tr>
					<td><?php echo $data['medicao']; ?></td>
					<td><?php echo $data['data']; ?></td>
					<td><?php echo str_replace('</','',$data['hora'] ); ?> <div class="fb-share-button" data-href="http://guih.us/" data-type="button"></div></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<div class="paginator">
				<a href="javascript:;" alt="Anterior" class='prevPage'>Anterior</a>
				<span class='currentPage'></span> de 
				<span class='totalPages'></span>
				<a  href="javascript:;" alt="Próxima" class='nextPage'>Próxima</a>
			</div>
		</div>
		<hr />
		<p>Escrito por <a href="https://www.facebook.com/profile.php?id=100001670353742" target="_blank">Guilherme Henrique</a> <br /> <strong>Fontes:</strong> Clima Tempo - SAAE GV</p>
	</div>
	<div id="page_loader" style="display:none;"><p class="loader"></p><span>Carregando</span></div>
</body>
</html>
<script>
window.onload = function() {	
	Monitor.init();
};
</script>
<script type="text/javascript" src="js/Monitor.js"></script>
<script type="text/javascript" src="js/Chart.js"></script>
<script type="text/javascript" src="js/jquery-1.10.1.min.js"></script>
<script type="text/javascript" src="js/jquery.paginate.js"></script>