<?php
class Weather{
	private $html;
	private $buffer;
	private $filter;
	private $regex;
	private $dom;
	private $pdo;
	private $pieces;
	private $level;
	private $returnedDom;
	private $domraw;
	private $urls = array();


	public function __construct()
	{
	   $this->pdo = new PDO( // replace with your database details
			'mysql:host=localhost;dbname=river', 
			'root', 
			''
	   	);
	   $this->urls = array(
		'http://www.climatempo.com.br/previsao-do-tempo/cidade/140/governadorvaladares-mg',
		"http://www.saaegoval.com.br/v3/index.php?xjxfun=atualizar&xjxr=" . rand( 000000000000, 99999999999999 ) 
		);

	   $this->pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	   $this->addRecord();
	}

	private function gethtmlData( $url='' )
	{
		$ch = curl_init( $url );

		curl_setopt_array($ch,
			array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_BINARYTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
			)
		);

		$this->html = curl_exec( $ch );

		if ( $this->html != true )
			return;


		return $this->html;
	}

	public function getWeaterData( $prop='' )
	{
		
		$this->addRecord();

		$this->buffer = $this->gethtmlData( $this->urls[0] );
		preg_match_all( '{<div\s+class="infos-tempomomento left"\s*>((?:(?:(?!<div[^>]*>|</div>).)++|<div[^>]*>(?1)</div>)*)</div>}si', $this->buffer, $this->regex );

		$this->dom = new DOMDocument;
		$this->dom->loadHTML( $this->regex[0][0] );

		$this->domraw = $this->dom->getElementsByTagName( 'ul' );
		$temp_raw = explode( ' ', $this->dom->textContent );
		$temp = explode( 'Dir',  utf8_decode( $temp_raw[0] ) );

		for ($i = 0; $i < $this->domraw->length; $i++)
			$this->returnedDom = explode( ':', utf8_decode( (string)$this->domraw->item( $i )->nodeValue ) );
		

		switch( $prop ) :
			case 'winddirection' :
				$this->pieces = explode( 'C', trim( $this->returnedDom[1] ) );
				return $this->pieces[0];
			break;
			case 'condition' :
				$this->pieces = explode( 'Pre', trim( $this->returnedDom[2] ) );
				return $this->pieces[0];
			break;
			case 'pressure' :
				$this->pieces = explode( 'I', trim( $this->returnedDom[3] ) );
				return $this->pieces[0];
			break;
			case 'windspeed' :
				$this->pieces = explode( 'U', trim( $this->returnedDom[4] ) );
				return $this->pieces[0];
			break;
			case 'umidade':
				return $this->returnedDom[5];
			break;
			case 'temp' :
				return $temp[0];
			break;
			default:
				return;
			break;
		endswitch;

	}

	public function getRiverData( $prop = '')
	{		
		$data       =  $this->gethtmlData( $this->urls[1] );
		$splitXml   = explode( '<![CDATA[S<p>', $data );
		$this->level = explode( '">', $splitXml[1] );

		switch ( $prop ) :
			case 'level' : 
				return substr( $this->level[0], -0, -31 );
			break;
			case 'hour' : 
				return substr( $this->level[2], -0, -24 );
			break;
			case 'date' :
			 	return substr( $this->level[1], -0, -27 );
			 break;
			 default:
				return;
			break;
		endswitch;

	}

	public function getRecords( $return='', $order='' )
	{
		
		$orderby = ( $order == 'ASC' ) ? 'ASC' : 'DESC';
		$limit = ( $order == 'DESC' ) ? 20 : '0,1000000';
		$queryRecords = $this->pdo->prepare("SELECT * from history order by id ".$orderby." LIMIT $limit");
		$queryRecords->execute();
		$row = $queryRecords->fetchAll(PDO::FETCH_ASSOC); 
		
		if ( $return == 'ajax' ):
			return( json_encode( $row ) );
			exit;
		endif;

		return $row;
	}

	public function addRecord()
	{
		$queryLastid  = $this->pdo->prepare("SELECT * from history where id = LAST_INSERT_ID(id) order by id DESC");
		$queryLastid->execute();
		$row          = $queryLastid->fetch(); 
		
		$level        = ( (string)$this->getRiverData( 'level' ) == ',m' ) ? '' : $this->getRiverData( 'level' );
		$when         = ( (string)$this->getRiverData( 'date' ) == '' ) ? '00/00' : $this->getRiverData( 'date' );
		$whenExaclity = ( (string) $this->getRiverData( 'hour' ) == '' ) ? '00:00 h</' :  $this->getRiverData( 'hour' );
		
		if( ($level != (string)$row['medicao']) && !empty($level)  ) :
			try{	
	
				$addEntry = $this->pdo->prepare( 'INSERT INTO history (medicao, data, hora, timex) VALUES(:medicao, :data, :hora, :timex) ON DUPLICATE KEY UPDATE medicao= :medicao, data = :data, hora = :hora, timex = :timex' );
				
				$addEntry->execute(
					array(
						':medicao' => $level,
						':data'    => $when,
						':hora'    => $whenExaclity,
						':timex'   => date('Y-m-d H:i:s'),
					)
				);
				
			} catch( PDOException $e ) {
				die( 'Error: ' . $e->getMessage() );
			}	
		endif;
		return;
			
	}

	public function realTime( $type='' )
	{			
		
		if ( $type == 'chart' ) :
			return $this->getRecords( 'ajax', 'DESC' );
		endif;
		if ( $type != 'chart' ) :
			return $this->getRecords( 'ajax' );
		endif;
	}
}