<?php 

// Databse data 
$db = "Path to your Database";
$sc = "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" . $db . ";"; //Set this up depending on your needs
// DB actual connection 
$db_conn = new COM('ADODB.Connection') or exit('Cannot start ADO.');
$db_conn->open($sc);

// Set here the main Domain 
$MAIN_DOMAIN = "https://www.insertyourdomainhere.com";
// Set here the directory where the sitemap file will be created 
$TARGET_DIR = $_SERVER['DOCUMENT_ROOT'] . "\\";			
$SITEMAP_HEADER = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"  xmlns:xhtml="http://www.w3.org/1999/xhtml" >
	<url>
      <loc>' . $MAIN_DOMAIN . '</loc>
      <link rel="alternate" hreflang="en" href="' . $MAIN_DOMAIN . '/en" />
	  <xhtml:link 
			rel="alternate"
			hreflang="fr"
			href="' . $MAIN_DOMAIN . '/fr"
		/><xhtml:link 
			rel="alternate"
			hreflang="de"
			href="' . $MAIN_DOMAIN . '/de"
		/><xhtml:link 
			rel="alternate"
			hreflang="es"
			href="' . $MAIN_DOMAIN . '/es"
		/>
      <changefreq>monthly</changefreq>
      <priority>0.5</priority>
	</url>';
$SITEMAP_FOOTER = "</urlset>";


$priority = 0.50;
$frequency = 'monthly';
$language_list = array("it","en","fr","de","sp");
$finalurl = '';
$shorturl_it = '';
$shorturl_en = '';
$shorturl_fr = '';
$shorturl_de = '';
$shorturl_es = '';



/*
 * You'll have to change the section below depending on how your DB table is organized. 
 * I'll leave my example, i hope it can be helpful.
*/

/**********SITEMAP FILE GENERATION*****************/
$sitemap_txt = $SITEMAP_HEADER;

$sql = "SELECT DISTINCT shorturls.finalurl FROM shorturls;";
$rs = $db_conn->execute($sql);

while(!$rs->EOF){
	
	$finalurl = $rs->fields('finalurl')->value;
	
	foreach($language_list as $language){

		$sql = "SELECT TOP 1 shorturl FROM shorturls WHERE finalurl='" . $finalurl . "' AND language='" . $language . "' ORDER BY id DESC;";
		$rs1 = $db_conn->execute($sql);

		
		
		if(!$rs1->EOF){
			$shorturl = $rs1->fields('shorturl')->value;
			
			switch($language){
				case 'it':
					$shorturl_it = $shorturl ;
				break;
				case 'en':
					$shorturl_en = $shorturl ;
				break;
				case 'fr':
					$shorturl_fr = $shorturl ;
				break;
				case 'de':
					$shorturl_de = $shorturl ;
				break;
				case 'sp':
					$shorturl_es = $shorturl ;
				break;
			}			
		}
	}	
	
	//That's where you initialize a new instance of the object URL_Block.
	$current_url_block = new Url_block($finalurl,$shorturl_it,$shorturl_en,$shorturl_fr,$shorturl_de,$shorturl_es,$priority,$frequency);
	$sitemap_txt .= $current_url_block->url_block_body;
	$base_domain = false;
	
	
	$shorturl_it = '';
	$shorturl_en = '';
	$shorturl_fr = '';
	$shorturl_de = '';
	$shorturl_es = '';
	
	$rs->MoveNext();
}
	
$sitemap_txt .= $SITEMAP_FOOTER;
/************!SITEMAP FILE GENERATION!*****************/


// Where the XML sitemap file is actually created and placed
$current_file = fopen($GLOBALS['TARGET_DIR'] . "sitemap_" . date() . ".xml", "w") or die("Unable to open file!");
fwrite($current_file, $sitemap_txt);
fclose($current_file);

echo "SITEMAP GENERATED - 200";



class Url_block { 

	public $finalurl;
	public $priority;
	public $changefreq;
	public $url_block_body;
	public $language;
	public $shorturl_it;
	public $shorturl_en;
	public $shorturl_fr;
	public $shorturl_de;
	public $shorturl_es;
	
	
	public function __construct ($finalurl,$shorturl_it,$shorturl_en,$shorturl_fr,$shorturl_de,$shorturl_es,$priority='',$changefreq=''){
		$this->finalurl = str_replace('&','&amp;',$finalurl);
		$this->priority = $priority;
		$this->changefreq = $changefreq;
		$this->shorturl_it = str_replace('&','&amp;',$shorturl_it);
		$this->shorturl_en = str_replace('&','&amp;',$shorturl_en);
		$this->shorturl_fr = str_replace('&','&amp;',$shorturl_fr);
		$this->shorturl_de = str_replace('&','&amp;',$shorturl_de);
		$this->shorturl_es = str_replace('&','&amp;',$shorturl_es);
		
		
		$this->url_block_body = '<url>
			<loc>' . $GLOBALS["MAIN_DOMAIN"] . '/it' . $this->shorturl_it . '</loc>';
			
			if($this->shorturl_de != ''){
				$this->url_block_body .= '<xhtml:link 
					rel="alternate"
					hreflang="de"
					href="' . $GLOBALS["MAIN_DOMAIN"] . '/de' . $this->shorturl_de . '"
				/>';
			}
			if($this->shorturl_en != ''){
				$this->url_block_body .= '<link 
					rel="alternate"
					hreflang="en"
					href="'  . $GLOBALS["MAIN_DOMAIN"] . '/en' . $this->shorturl_en . '"
				/>';
			}
			if($this->shorturl_fr != ''){
				$this->url_block_body .= '<xhtml:link 
					rel="alternate"
					hreflang="fr"
					href="'  . $GLOBALS["MAIN_DOMAIN"] . '/fr' . $this->shorturl_fr . '"
				/>';
			}
			if($this->shorturl_es != ''){
				$this->url_block_body .= '<xhtml:link 
					rel="alternate"
					hreflang="es"
					href="'  . $GLOBALS["MAIN_DOMAIN"] . '/es' . $this->shorturl_it . '"
				/>';
			}
			
			$this->url_block_body .= '<changefreq>' . $this->changefreq . '</changefreq>
			<priority>' . $this->priority . '</priority>
		</url>';
		
	}
} 
