<?php
/**
 * https://github.com/FokkeZB/Meetup
 * 
 * @notes Adjusted slightly and added post for OATH
 */
class Meetup 
{
    const BASE 		= 'https://api.meetup.com';
    const AUTHORIZE = 'https://secure.meetup.com/oauth2/authorize';
    const ACCESS	= 'https://secure.meetup.com/oauth2/access';
    
    protected $_parameters = array();
    
    public function __construct(array $parameters = array())
    {
        $this->_parameters = array_merge($this->_parameters, $parameters);
    }

    public function getEvents(array $parameters = array()) 
    {
        return $this->get('/2/events', $parameters);
    }

    public function getGroups(array $parameters = array()) 
    {
        return $this->get('/2/groups', $parameters);
    }
    
    public function getPhotos(array $parameters = array())
    {
        return $this->get('/2/photos', $parameters);
    }

    public function getDiscussionBoards(array $parameters = array()) 
    {
        return $this->get('/:urlname/boards', $parameters);
    }

    public function getDiscussions(array $parameters = array()) 
    {
        return $this->get('/:urlname/boards/:bid/discussions', $parameters);
    }
    public function getMembers(array $parameters = array()) 
    {
        return $this->get('/2/members', $parameters);
    }
    public function getNext($response)
    {
        if (!isset($response) || !isset($response->meta->next))
        {
            throw new Exception("Invalid response object.");
        }
        return $this->api($response->meta->next);
    }

    public function get($path, array $parameters = array())
    {    
        if (preg_match_all('/:([a-z]+)/', $path, $matches))
        {           	
            foreach ($matches[0] as $i => $match)
            {                	
                if (isset($parameters[$matches[1][$i]]))
                {
                    $path = str_replace($match, $parameters[$matches[1][$i]], $path);
                    unset($parameters[$matches[1][$i]]);
                } 
                else 
                {
                    throw new Exception("Missing parameter '" . $matches[1][$i] . "' for path '" . $path . "'.");
                }
            }
        }

        return $this->api(self::BASE . $path, $parameters, false);
    }
    
    public function authorize(array $parameters = array())
    {
    	$location = self::AUTHORIZE . '?' . http_build_query(array_merge($this->_parameters,$parameters));
    	header("Location: " . $location);
    }
    
    public function access(array $parameters = array())
    {
    	return $this->api(self::ACCESS, array_merge($parameters, array('grant_type'=>'authorization_code')), true);
    }
    
    public function refresh(array $parameters = array())
    {
    	$this->api(self::ACCESS, array_merge($parameters, array('grant_type'=>'refresh_token')), true);
    }
    
    protected function api($url, $parameters, $post=false)
    {
    	//merge parameters
    	$this->_parameters = array_merge($parameters, $this->_parameters);
    	
    	
    	//make sure 'sign' is included when using api key only	
	if(in_array('key', $this->_parameters) && $url!=self::ACCESS && $url!=self::AUTHORIZE)
    	{
    		//api request (any) - include sign parameters
    		$this->_parameters = array_merge( array('sign', 'true'), $this->_parameters );
    	}

    	//init curl
    	$ch = curl_init();
    	
    	$headers = array("Accept-Charset: utf-8");
    	
    	//set options for connection
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
    	curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    	curl_setopt($ch, CURLOPT_HEADER, false);
    	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    	
    	//either GET or POST against api
    	if($post===false)
    	{   		
    		//include headers as specified by manual
    		if( $url == self::ACCESS )
    		{
    			array_push($headers, 'Content-Type: application/x-www-form-urlencoded');
    		}
    		else if( strpos($url, self::BASE) === 0 && in_array('access_token', $this->_parameters) )
    		{
    			array_merge($this->_parameters, array('token_type'=>'bearer'));
    		}
    		
    		curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($this->_parameters));
    	}	
    	else
    	{
    		curl_setopt($ch, CURLOPT_URL, $url);    		
    		curl_setopt($ch, CURLOPT_POST, count($parameters));
    		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->_parameters));   		
    	}    	
    	     	  	
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
         
    	//fetch content
    	$content = curl_exec($ch);
    	   	
    	//was there an error on the connection?
    	if (curl_errno($ch))
    	{
    		$error = curl_error($ch);
    		curl_close($ch);
    		 
    		throw new Exception("Failed retrieving  '" . $url . "' because of connection issue: ' " . $error . "'.");
    	}
    	
    	//retrieve json
    	$response = json_decode($content);
    	$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
   	
    	curl_close($ch);
    	
    	if (!is_null($response) && $status != 200)
    	{    	        
    		//tell them what went wrong or just relay the status
    		if( isset($response->error) && isset($response->error_description) )
    		{
    			//what we see against Oath 
    			$error = $response->error . ' - ' . $response->error_description;
    		}
    		else if( isset($response->details) && isset($response->problem) && isset($response->code) )
    		{
    			//what we see against regular access
    			$error = $response->code . ' - ' . $response->problem . ' - ' . $response->details;
    		}
    		else
    		{
    			$error = 'Status ' . $status;
    		}
    		 
    		throw new Exception("Failed retrieving  '" . $url . "' because of ' " . $error . "'.");
    	}	
    	else if (is_null($response)) 
    	{
    		//did we have any parsing issues for the response?
    		switch (json_last_error()) 
    		{
    			case JSON_ERROR_NONE:
    				$error = 'No errors';
    				break;
    			case JSON_ERROR_DEPTH:
    				$error = 'Maximum stack depth exceeded';
    				break;
    			case JSON_ERROR_STATE_MISMATCH:
    				$error = ' Underflow or the modes mismatch';
    				break;
    			case JSON_ERROR_CTRL_CHAR:
    				$error = 'Unexpected control character found';
    				break;
    			case JSON_ERROR_SYNTAX:
    				$error = 'Syntax error, malformed JSON';
    				break;
    			case JSON_ERROR_UTF8:
    				$error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
    				break;
    			default:
    				$error = 'Unknown error';
    				break;
    		}
    	
    		throw new Exception("Cannot read response by  '" . $url . "' because of: '" . $error . "'.");
    	}
    	
    	return $response;
    }
}
?>

