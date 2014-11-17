<?php
/**
 * @package    Meetup
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
class Meetup 
{
  /**
    * Base meetup api url
    * @const 
   */		
    const BASE 		= 'https://api.meetup.com';
  /**
    * Base meetup api url
    * @const 
   */
    const AUTHORIZE = 'https://secure.meetup.com/oauth2/authorize';
  /**
    * ACCESS meetup api url
    * @const 
   */
    const ACCESS	= 'https://secure.meetup.com/oauth2/access';
  /**
    * GET request
    * @const 
   */        
    const GET    = 1;
  /**
    * POST request
    * @const 
   */    
    const POST   = 2;
  /**
    * PUT request
    * @const 
   */    
    const PUT    = 3;
  /**
    * DELETE request
    * @const 
   */    
    const DELETE = 4;
   /**
    * Parameters for requests
    * @var array
   */       
    protected $_parameters = array();
  /**
    * The response object from the request
    * @var mixed
   */           
    protected $_response = null;
   /**
    * Constructor
    * @param array $parameters The parameters passed during construction
   */   
    public function __construct(array $parameters = array())
    {
        $this->_parameters = array_merge($this->_parameters, $parameters);
        $this->_next = $this->_response = null;
    }
   /**
    * Stub for fetching events
    *
    * @param array $parameters The parameters passed for this request
    * @return mixed A json object containing response data
    * @throws Exception if anything goes wrong
   */  
    public function getEvents(array $parameters = array()) 
    {
        return $this->get('/2/events', $parameters);
    }
   /**
    * Stub for fetching groups
    *
    * @param array $parameters The parameters passed for this request
    * @return mixed A json object containing response data
    * @throws Exception if anything goes wrong
   */  
    public function getGroups(array $parameters = array()) 
    {
        return $this->get('/2/groups', $parameters);
    }
    /**
    * Stub for fetching photos
    *
    * @param array $parameters The parameters passed for this request
    * @return mixed A json object containing response data
    * @throws Exception if anything goes wrong
   */     
    public function getPhotos(array $parameters = array())
    {
        return $this->get('/2/photos', $parameters);
    }
   /**
    * Stub for fetching discussion boards
    *
    * @param array $parameters The parameters passed for this request
    * @return mixed A json object containing response data
    * @throws Exception if anything goes wrong
   */  
    public function getDiscussionBoards(array $parameters = array()) 
    {
        return $this->get('/:urlname/boards', $parameters);
    }
   /**
    * Stub for fetching discussions
    *
    * @param array $parameters The parameters passed for this request
    * @return mixed A json object containing response data
    * @throws Exception if anything goes wrong
   */  
    public function getDiscussions(array $parameters = array()) 
    {
        return $this->get('/:urlname/boards/:bid/discussions', $parameters);
    }
    /**
    * Stub for fetching member
    *
    * @param array $parameters The parameters passed for this request
    * @return mixed A json object containing response data
    * @throws Exception if anything goes wrong
   */     
    public function getMembers(array $parameters = array()) 
    {
        return $this->get('/2/members', $parameters);
    }
   /**
    * Stub for grabbing the next response data if it's available in the meta information
    * of a response.  Normally if there's too many results it won't return them all.
    *
    * @return mixed A json object containing response data
   */      
    public function getNext()
    {   	
    	return $this->hasNext() ? $this->api($this->_response->meta->next, array(), self::GET) : null;
    }
   /**
    * Is there more data to retrieve?
    *
    * @return boolean True if there's more results to process
   */     
    public function hasNext()
    {
    	$next = null;
    	if( isset($this->_response->meta) && isset($this->_response->meta->next) )
    	{
    		$next = $this->_response->meta->next;
    		if( strlen($next) )
    		{
    			return true;
    		}
    	}
    	
    	return false;
    }
   /**
    * Stub for updating an event
    *
    * @param array $parameters The parameters passed for this request
    * @return mixed A json object containing response data
    * @throws Exception if anything goes wrong
   */   
    public function postEvent(array $parameters = array())
    {
    	return $this->post('/2/event/:id', $parameters);
    }
   /**
    * Stub for deleting an event
    *
    * @param array $parameters The parameters passed for this request
    * @return mixed A json object containing response data
    * @throws Exception if anything goes wrong
   */       
    public function deleteEvent(array $parameters = array())
    {
    	return $this->delete('/2/event/:id', $parameters);
    }   
   /**
    * Perform a get on any url supported by meetup, use : to specify parameters that use
    * placeholders and pass that exact parameter name as a parameter.
    *
    * @param array $parameters The parameters passed for this request
    * @return mixed A json object containing response data
    * @throws Exception if anything goes wrong
    *
    * @code 
    * $meetup->get('/2/event/:id', array('id'=>10));
    * $meetup->get('/2/members', array('group_urlname'=>'foobar'));
    * @endcode
   */            
    public function get($path, array $parameters = array())
    {    
    	list($url, $params) = $this->params($path, $parameters);
    	
        return $this->api(self::BASE . $url, $params, self::GET);
    }
   /**
    * Perform a post on any url supported by meetup, use : to specify parameters that use
    * placeholders and pass that exact parameter name as a parameter.
    *
    * @param array $parameters The parameters passed for this request
    * @return mixed A json object containing response data
    * @throws Exception if anything goes wrong
    *
    * @code 
    * $meetup->post('/2/member/:id', array('id'=>10));
    * @endcode
   */                
    public function post($path, array $parameters = array())
    {
     	list($url, $params) = $this->params($path, $parameters);
    	
        return $this->api(self::BASE . $url, $params, self::POST);   
    }
    /**
    * Perform a put on any url supported by meetup, use : to specify parameters that use
    * placeholders and pass that exact parameter name as a parameter.
    *
    * @param array $parameters The parameters passed for this request
    * @return mixed A json object containing response data
    * @throws Exception if anything goes wrong
    *
    * @note There isn't any PUT supported events at the moment
   */      
    public function put($path, array $parameters = array())
    {
     	list($url, $params) = $this->params($path, $parameters);
    	
        return $this->api(self::BASE . $url, $params, self::PUT);       
    }
   /**
    * Perform a delete on any url supported by meetup, use : to specify parameters that use
    * placeholders and pass that exact parameter name as a parameter.
    *
    * @param array $parameters The parameters passed for this request
    * @return mixed A json object containing response data
    * @throws Exception if anything goes wrong
    *
    * @code 
    * $meetup->delete('/2/member/:id', array('id'=>10));
    * @endcode
   */  
    public function delete($path, array $parameters = array())
    {
     	list($url, $params) = $this->params($path, $parameters);
    	
        return $this->api(self::BASE . $url, $params, self::DELETE);       
    }
   /**
    * Utility function for swapping place holders with parameters if any are found in 
    * the request url.  The place holder parameter gets swapped out and the array gets
    * the parameter removed, otherwise the request is left un-altered.
    *
    * @param string $path The relative path of the request from meetup (not including base path)
    * @param array $parameters The parameters passed for this request
    * @return array An array of the path and parameters modified or un-altered
    * @throws Exception if anything goes wrong
   */          
    protected function params($path, array $parameters = array())
    {
    	$url    = $path;
    	$params = $parameters;
        if (preg_match_all('/:([a-z]+)/', $url, $matches))
        {           	
            foreach ($matches[0] as $i => $match)
            {                	
                if (isset($params[$matches[1][$i]]))
                {
                    $url = str_replace($match, $params[$matches[1][$i]], $url);
                    unset($params[$matches[1][$i]]);
                } 
                else 
                {
                    throw new Exception("Missing parameter '" . $matches[1][$i] . "' for path '" . $path . "'.");
                }
            }
        }
        
        return array($url, $params);    
    }  
   /**
    * Utility function for authorizing ourselves with meetup.  Visit this url
    * https://secure.meetup.com/meetup_api/oauth_consumers/ to learn about OATH and the 
    * consumer details required for authorized access.
    *
    * @param array $parameters The parameters passed for this request
    * @note You're sent to meetup and they will either have an error or a page requiring you to authorize, they'll send
    *       you back to the redirect uri specified in your consumer details
    * @note The parameter 'response_type' is automatically included with value 'code'
   */       
    public function authorize(array $parameters = array())
    {   	
    	$location = self::AUTHORIZE . '?' . http_build_query(array_merge($this->_parameters,$parameters, array('response_type'=>'code')));
    	header("Location: " . $location);
    }
   /**
    * Utility function for getting an access token from meetup with the code they passed back in
    * the authorization step.  Visit this url https://secure.meetup.com/meetup_api/oauth_consumers/ 
    * to learn about OATH and the consumer details required for authorized access.
    *
    * @param array $parameters The parameters passed for this request
    * @throws Exception if anything goes wrong
    * @note The parameter 'grant_type' is automatically included with value 'authorization_code'
   */      
    public function access(array $parameters = array())
    {
    	return $this->api(self::ACCESS, array_merge($parameters, array('grant_type'=>'authorization_code')), self::POST);
    }
    /**
    * Utility function for getting an refresh token from meetup to avoid authorization from expiring.  
    * Visit this url https://secure.meetup.com/meetup_api/oauth_consumers/ to learn about OATH and the 
    * consumer details required for authorized access.
    *
    * @param array $parameters The parameters passed for this request
    * @throws Exception if anything goes wrong
    * @note The parameter 'grant_type' is automatically included with value 'refresh_token'
   */    
    public function refresh(array $parameters = array())
    {    	
    	return $this->api(self::ACCESS, array_merge($parameters, array('grant_type'=>'refresh_token')), self::POST);
    }
    /**
    * Main routine that all requests go through which handles the CURL call to the server and
    * prepares the request accordingly.
    *
    * @param array $parameters The parameters passed for this request
    * @throws Exception if anything goes wrong
    * @note The parameter 'sign' is automatically included with value 'true' if using an api key
   */        
    protected function api($url, $parameters, $action=self::GET)
    {
    	//merge parameters
    	$params = array_merge($parameters, $this->_parameters);
    	   	
    	//make sure 'sign' is included when using api key only	
	if(in_array('key', $params) && $url!=self::ACCESS && $url!=self::AUTHORIZE)
    	{
    		//api request (any) - include sign parameters
    		$params = array_merge( array('sign', 'true'), $params );
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
    	
    	//either GET/POST/PUT/DELETE against api
    	if($action==self::GET || $action==self::DELETE)
    	{   		
    		//GET + DELETE
    		
    		//include headers as specified by manual
    		if( $url == self::ACCESS )
    		{
    			array_push($headers, 'Content-Type: application/x-www-form-urlencoded');
    		}
    		else if( strpos($url, self::BASE) === 0 && in_array('access_token', $params) )
    		{
    			array_merge($params, array('token_type'=>'bearer'));
    		}
    		
    		curl_setopt($ch, CURLOPT_URL, $url . (!empty($params) ? ('?' . http_build_query($params)) : ''));
    	}	
    	else
    	{
    		//POST + PUT
    		
    		curl_setopt($ch, CURLOPT_URL, $url);    		
    		curl_setopt($ch, CURLOPT_POST, count($params));
    		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    	}    	
    	 
    	//need custom types for PUT/DELETE
    	switch($action)
    	{
    		case self::DELETE:
    			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    		break;
    		case self::PUT:
    			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    		break;
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
    	
    	//retrieve json and store it internally
    	$this->_response = json_decode($content);
    	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
   	
    	curl_close($ch);
    	    	
    	if (!is_null($this->_response) && $status != 200)
    	{    	        
    		//tell them what went wrong or just relay the status
    		if( isset($this->_response->error) && isset($this->_response->error_description) )
    		{
    			//what we see against Oath 
    			$error = $this->_response->error . ' - ' . $this->_response->error_description;
    		}
    		else if( isset($this->_response->details) && isset($this->_response->problem) && isset($this->_response->code) )
    		{
    			//what we see against regular access
    			$error = $this->_response->code . ' - ' . $this->_response->problem . ' - ' . $this->_response->details;
    		}
    		else
    		{
    			$error = 'Status ' . $status;
    		}
    		 
    		throw new Exception("Failed retrieving  '" . $url . "' because of ' " . $error . "'.");
    	}	
    	else if (is_null($this->_response)) 
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
   	
    	return $this->_response;
    }
}
?>
