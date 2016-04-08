<?php
/********************************************************************************************************************************
*
* Dokuwiki Video Carousel by Digilent
*
* Written By Sam Kristoff
*
* www.github.com/digilent/dokuwiki-video-carousel
* www.digilent.com
*
/*******************************************************************************************************************************/
  
 
// must be run within DokuWiki
if(!defined('DOKU_INC')) die();
 
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once DOKU_PLUGIN.'syntax.php';
//Using PEAR Templates
require_once "HTML/Template/IT.php";

 
/********************************************************************************************************************************
* All DokuWiki plugins to extend the parser/rendering mechanism
* need to inherit from this class
********************************************************************************************************************************/
class syntax_plugin_digilentvideocarousel extends DokuWiki_Syntax_Plugin 
{
	//Return Plugin Info
	function getInfo() 
	{
        return array('author' => 'Sam Kristoff',
                     'email'  => 'admin@digilent.com',
                     'date'   => '2016-04-06',
                     'name'   => 'Digilent Video Carousel',
                     'desc'   => 'Dokuwiki Video Carousel by Digilent',
                     'url'    => ' www.github.com/digilent/dokuwiki-video-carousel');
    }	
	
	//Store user variables to parse in one pass
	protected $data = array();
	protected $dataIndex = 0;
	 
    function getType() { return 'protected'; }
    function getSort() { return 32; }
  
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('{{Digilent Video Carousel.*?(?=.*?}})',$mode,'plugin_digilentvideocarousel');
		
		//Add Internal Pattern Match For Product Page Elements	
		$this->Lexer->addPattern('\|.*?(?=.*?)\n','plugin_digilentvideocarousel');
    }
	
    function postConnect() {
      $this->Lexer->addExitPattern('}}','plugin_digilentvideocarousel');
    }
	 
    function handle($match, $state, $pos, &$handler) 
	{	
		
		switch ($state) 
		{
		
			case DOKU_LEXER_ENTER :
				break;
			case DOKU_LEXER_MATCHED :					
				//Find The Token And Value (Before '=' remove white space, convert to lower case).
				$tokenDiv = strpos($match, '=');												//Find Token Value Divider ('=')
				$prettyToken = trim(substr($match, 1, ($tokenDiv - 1)));				//Everything Before '=', Remove White Space
				$token = strtolower($prettyToken);											//Convert To Lower Case
				$value = substr($match, ($tokenDiv + 1));									//Everything after '='
				switch($token)
				{
					case 'youtube':						
						$videoTokens = explode('|', $value);
						$this->data[$this->dataIndex] = array('youtube', trim($videoTokens[0]),  trim($videoTokens[1]));
						$this->dataIndex++;
						break;
					case 'link':
						$linkTokens = explode('|', $value);
						$this->data[$this->dataIndex] = array('link', trim($linkTokens[0]),  trim($linkTokens[1]));
						$this->dataIndex++;
						break;
					default:						
						break;
				}
				return array($state, $value);
				break;
			case DOKU_LEXER_UNMATCHED :
				break;
			case DOKU_LEXER_EXIT :
								
				//----------Process User Data Into Video Carousel----------
				
				//Load HTML Template
				$videoCarouselTpl = new HTML_Template_IT(dirname(__FILE__) . "/templates");
				$videoCarouselTpl->loadTemplatefile("video-carousel.tpl.html", true, true);
				
				//Add Side Buttons
				$videoIndex = 0;
				foreach($this->data as $content)
				{
					switch($content[0])
					{
						case 'youtube':
							$videoCarouselTpl->setCurrentBlock("BUTTONS");
							$videoCarouselTpl->setVariable("NUMBER", $videoIndex);
							$videoCarouselTpl->setVariable("NAME", $content[1]);
							$videoCarouselTpl->parseCurrentBlock("BUTTONS");
							$videoIndex++;
							break;						
						case 'link':
							$videoCarouselTpl->setCurrentBlock("LINK");
							$videoCarouselTpl->setVariable("NAME", $content[1]);
							$videoCarouselTpl->setVariable("URL", $content[2]);
							$videoCarouselTpl->parseCurrentBlock("LINK");
							break;
						default:
						break;
					}
				}
				
				//Iterate over elements and build from template
				$elemtnIndex = 0;
				foreach($this->data as $content)
				{
					switch($content[0])
					{
						case 'youtube':
							$videoCarouselTpl->setCurrentBlock("YOUTUBE");
							$videoCarouselTpl->setVariable("NUMBER", $elemtnIndex);
							$videoCarouselTpl->setVariable("EMBEDCODE", $content[2]);
							if($elemtnIndex == 0)
							{
								$videoCarouselTpl->setVariable("ACTIVE", 'active');
							}
							else
							{
								$videoCarouselTpl->setVariable("ACTIVE", '');
							}
							$videoCarouselTpl->parseCurrentBlock("YOUTUBE");
							$elemtnIndex++;
						break;
						
						default:
						break;
					}
				}		
					
				$output = $videoCarouselTpl->get();
				
				return array($state, $output);				
				break;
			case DOKU_LEXER_SPECIAL :
				break;
		}
		
		return array($state, $match);
    }
 
    function render($mode, &$renderer, $data) 
	{
    // $data is what the function handle return'ed.
        if($mode == 'xhtml')
		{		
			
			$renderer->doc .= $this->fullName;
			switch ($data[0]) 
			{
			  case DOKU_LEXER_ENTER : 
				break;
			  case DOKU_LEXER_MATCHED :				
				break;
			  case DOKU_LEXER_UNMATCHED :
				break;
			  case DOKU_LEXER_EXIT :
			  
				//Extract cached render data and add to renderer
				$output = $data[1];				
				$renderer->doc .= $output;				
				break;
				
			  case DOKU_LEXER_SPECIAL :
				break;
			}			
            return true;
        }
        return false;
    }	
}