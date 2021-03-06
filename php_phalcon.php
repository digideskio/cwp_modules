<?php
if ( !isset( $include_path ) )
{
    echo "invalid access";
    exit( );
}

class phalcon
{	
    private $dir = "/root";
    private $status_phalcon = "";
    private $version_phalcon = "";
	private $github_braches = "https://api.github.com/repos/phalcon/cphalcon/branches";
    private $github_url = "https://api.github.com/repos/phalcon/cphalcon/commits?per_page=1&sha=";
	private $date_last_commits = "";

    public $alert = "";	

    public function __construct()
    {
        echo '<center><b>Check is Phalcon installed</b></center> <br>';		
    }

    public function initalize()
    {
        $this->check_is_ph_loaded();
        $this->date_last_commit();
    }

    public function get_ph_version()
    {
        $phalcon_version = shell_exec("/usr/local/bin/php --ri phalcon");
        $version = preg_split('/\s+/', trim($phalcon_version));
        $this->version_phalcon = $version[9];
    }
	
    public function check_is_ph_loaded()
    {
        $php = shell_exec("/usr/local/bin/php -m");
        $oparray = preg_split('/\s+/', trim($php));	
        if(in_array("phalcon",$oparray))
        {
            //Load Phalcon Version Installed
            $this->get_ph_version();

            $this->alert = "alert-success";
            $this->message = "<strong>Success!</strong> Phalcon is installed correctly.<Br> Installed version: <b>{$this->version_phalcon}</b>";
            $this->toHtml();
        }
        else
        {
            $this->alert = "alert-info";
            $this->message = "<strong>Info!</strong> Is not installed.<br>
            To install the Phalcon follow the guidelines below, or use the auto install script.";
			
            //Show Help Installation
            $this->toHtml();
            $this->message_install();
        }
    }
	
	private function message_install()
	{
	    echo '<b>Install</b>:<br><br>';
		echo 'Clone phalcon 2.0 repo:<br>';
		echo '<pre>git clone -b 2.0.0 https://github.com/phalcon/cphalcon.git</pre>';
		
		echo 'Clone zephir repo:<br>';
		echo '<pre>git clone https://github.com/phalcon/zephir.git</pre>';
		
		echo 'clone json-c repo:<br>';
		echo '<pre>git clone https://github.com/json-c/json-c.git</pre>';
		
		echo 'Install required packages:<br>';
		echo '<pre>yum install pcre re2c</pre>';
		
		echo 'Compile json-c:<br>';
		echo '<pre>cd json-c
		sudo sh autogen.sh
		sudo ./configure
		sudo make
		sudo make install
		cd ..</pre>';
		
		echo 'Compile zephir:<br>';
		echo '<pre>cd zephir
		sudo ./install
		cd ..</pre>';
		
		echo 'Compile phalcon:<br>';
		echo '<pre>cd cphalcon
		../zephir/bin/zephir generate
		../zephir/bin/zephir compile</pre>';
		
		echo 'Add extension to your php.ini<br>';
		echo '<pre>extension=phalcon.so</pre>';	
	}
	
	public function toHtml()
	{
			echo '<div class="alert '.$this->alert.'">  
				<a class="close" data-dismiss="alert">×</a>  
				'.$this->message.' 
			       </div>';	
	}
	
	public function date_last_commit()
	{
		$context = stream_context_create(array(
		  'http' => array(
			'header'=> "User-Agent: http://mikeangstadt.name\r\n"
		  )
		));
		
		$branches = json_decode(file_get_contents($this->github_braches, false, $context));
		
		$response = file_get_contents($this->github_url.$branches[26]->commit->sha, false, $context);
     
		if ($response === false){
		  throw new Exception("Error contacting github.");
		}
		 
		//parse the JSON
		$json = json_decode($response);
		if ($json === null){
		  throw new Exception("Error parsing JSON response from github.");
		}
		if (isset($json->error)){
		  throw new Exception($json->error);
		}
		
		$date = new DateTime($json[0]->commit->author->date);

		$this->date_last_commits = $date->format("Y-m-d H:i:s");
		$this->last_commits_message = $json[0]->commit->message;
		
		$this->update();
		//return $date->format("Y-m-d H:i:s");	
	}
	
	public function update()
	{
		
		echo '<center><b>Github last commits date:</b><br>';
		echo $this->date_last_commits.'<br>';
		echo $this->last_commits_message.'<br><br>';

		if(@$_POST['update'] == 'start') {
		$this->message_install();
		} else {
                echo '<div class="btn-group">
		      <form action="index.php?module=php_phalcon" method="POST">
			  <input type="hidden" name="update" value="start">
                          <button class="btn btn-warning">Show Update Instruction</button>
		      </form>
                      </div></center><br>';
		}

	}

}

$phalcon = new phalcon();
$phalcon->initalize();

?>
