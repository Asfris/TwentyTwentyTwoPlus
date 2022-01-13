<?php

namespace Update;

use ErrorHandle\Error;
use Exception;
use ZipArchive;

const ASSET_FILE_SIZE_LIMIT = 2; // AS MEGABYTE

trait Converter
{
    /**
     * Convert mb to bytes
     * @param int $mb
     * @return int
     * @since 0.1.3
     */
    private function megabyteToByte(int $mb): int
    {
        return $mb * 1000000;
    }
}

trait CurlOptions
{
    private $curl;

    /**
     * Init Curl
     * @param string $url
     * @return void
     */
    private function cInit(): void {
        $this->curl = curl_init();
    }
    
    /**
     * setOption
     * @param int $option
     * @param int $value
     * @return void
     */
    private function cSet_option (int $option, mixed $value): void {
        curl_setopt($this->curl, $option, $value);
    }

    /**
     * @return mixed Returns response of request
     */
    private function cExec(): mixed {
        return curl_exec($this->curl);
    }
    
    /**
     * Close curl session
     * @return void
     */
    private function cClose(): void {
        curl_close($this->curl);
    }
}

class Request
{
    use CurlOptions;
    
    /**
     * Headers
     */
	private array $header = array();

    /**
     * Curl opt
     */
    private array $curlOptions;

    /**
     * Method of request
     */
    private string $method;

    /**
     * Body of request. if method is POST
     */
    private $body;

    function __construct() {
        $this->cInit();
    }

    /**
     * Set request target url
     * @param string $url
     * @return void
     */
    public function set_url(string $url): void {
        $this->cSet_option(CURLOPT_URL, $url);
    }

    /**
     * Set request method
     * @param string $methdo
     * @return void
     */
    public function set_method(string $method): void {
        if ($method !== "POST" && $method !== "GET" && $method !== "PUT" && $method !== "DELETE")
            throw new Exception($method . " not defined in methods list.");

        $this->method = $method;
    }

    /**
     * Set request body.
     * @param mixin $data
     * @return void
     */
    public function set_body($data): void {
        $this->body = $data;
    }

    /**
     * Add new header.
     * @param string $param
     * @param string $value
     * @return void
     */
    public function push_header(string $param): void {
        array_push($this->header, $param);
    }

    /**
     * Finalize request
     */
    public function finish(): mixed {
        // Set http headers
        $this->cSet_option(CURLOPT_HTTPHEADER, $this->header);

        if ($this->method == "POST") {
            $this->cSet_option(CURLOPT_POST, true);
            $this->cSet_option(CURLOPT_POSTFIELDS, $this->body);
        }

        $this->cSet_option(CURLOPT_RETURNTRANSFER, true);
        
        $response = $this->cExec();
        
        $this->cClose();
        
        return $response;
    }
}

/**
 * Github Asset Files
 */
class AssetFile {

    /**
     * Downloaded file
     * @var $file
     */
    private $dFile;

    /**
     * Address of file
     * @var string
     */
    public string $addr;

    /**
     * Content type
     * @var string
     */
    public string $content_type;

    /**
     * size of file
     * @var int
     */
    public int $size;

    /**
     * @since 0.1.0
     */
    function __construct(string $addr, string $content_type, int $size) {
        $this->addr = $addr;
        $this->content_type = $content_type;
        $this->size = $size;
    }

    /**
     * Download File
     * @since 0.1.0
     * @since 0.1.3
     * @return bool
     */
    public function download(): bool {
        try
        {
            $this->dFile = download_url($this->addr);
        }
        catch (Error $ex)
        {
            echo $ex->fullErrorMessage();
            return false;
        }

        return true;
    }

    /**
     * @param string $addr
     * @since 0.1.3
     * @return bool
     */
    public function save(string $addr): bool {
        try
        {
            copy( $this->dFile , $addr );
            @unlink( $this->dFile );
        }
        catch (Error $ex)
        {
            echo $ex->fullErrorMessage();
            return false;
        }

        return true;
    }
}

/**
 * Sends get request to api, and recives response data
 */
class Get {
    /**
     * @since 0.1.5
     * @var string USER_AGENT
     */
    private const USER_AGENT = "Nothing";

    /**
     * @var stirng $url of api
     */
    private string $url;

    /**
     * @var Request $request Request Handler
     */
    private Request $request;

    /**
     * When class created
     * @param string $url Pass main url of api
     * @since 0.1.0
     */
    function __construct(string $url) {
        $this->url = $url;
        
        $this->request = new Request();
        $this->request->set_method("GET");
        $this->request->push_header("User-Agent: " . self::USER_AGENT);
    }

    /**
     * Send get request to api router and recives data
     * @param string $router Addr to send get request. example: github.com/sampleRouter
     * @since 0.1.0
     * @since 0.1.3 Removed Unnecessary variable
     */
    public function get_data(string $router) {
        $this->request->set_url($this->url . $router);
        return $this->request->finish();
    }

    /**
     * Send get request to api router and recives data and decoded in json
     * @param string $router Addr to send get request. example: github.com/sampleRouter
     * @since 0.1.0
     */
    public function get_data_as_json(string $router) {
        $response = $this->get_data($router);

        if (!$response) throw new Error("Response is null");

        return json_decode($response);
    }
}

/**
 * Github API
 */
class GithubApi {
    /**
     * Github api url
     */
    private string $url = "https://api.github.com";

    /**
     * Repository name
     */
    private string $repository;

    /**
     * Username
     */
    private string $username;

    /**
     * Get class
     */
    private Get $get;

    /**
     * When class creates
     * @since 0.1.0
     */
    function __construct(string $repo = "", string $username = "") {
        $this->repository = $repo;
        $this->username = $username;
        $this->get = new Get($this->url);
    }

    /**
     * Get repository releases detail as object
     * @since 0.1.0
     * @since 0.1.3 Removed Unnecessary variable
     */
    public function get_repo_releases() {
        $username = $this->username;
        $repo = $this->repository;

        if(empty($username) && empty($repo)) return array("message" => "Error");

        return $this->get->get_data_as_json("/repos/$this->username/$this->repository/releases");
    }

    /**
     * Get latest release and download asset
     * @param int $assetIndex
     * @param int $sizeLimit
     * @return AssetFile
     * @since 0.1.0
     */
    public function get_latest_release_asset(int $assetIndex, int $sizeLimit): AssetFile {
        $latest = $this->get_repo_releases();

        $file_addr = $latest[0]->assets[$assetIndex]->browser_download_url;
        $file_type = $latest[0]->assets[$assetIndex]->content_type;
        $file_size = $latest[0]->assets[$assetIndex]->size;

        if ($file_size > $sizeLimit) {
            // Error
            exit("Error: Asset file size is bigger than limit.");
        }

        return new AssetFile($file_addr, $file_type, $file_size);
    }
}

trait Path
{
    /**
     * Returns the latest folder name of path
     * example path -> localhost:8080/addr/addr/[PLUGIN_NAME]
     * @param string $path
     * @since 0.1.4
     * @return string
     */
    private function lastFolderName(string $path): string
    {
        $folder_dir = preg_split("#/#", $path);
        $folder_dir_len = count($folder_dir) - 2;

        return $folder_dir[$folder_dir_len];
    }
}

/**
 * Handle plugin update
 */
class Update {
    use Converter;
    use Path;

    /**
     * Current version of plugin
     */
    private string $plugin_version;

    /**
     * GitHubApi
     */
    private GithubApi $api;

    /**
     * When class created
     * @param string $current_version pass current version of plugin
     * @param string $username
     * @param string $repo
     * @since 0.1.0
     */
    function __construct(string $current_version, string $username, string $repo) {
        $this->plugin_version = $current_version;
        $this->api = new GitHubApi($repo, $username);
    }

    /**
     * Checks version of plugin
     * If $version deferent than $this->plugin_version then return true, if not return false.
     * @since 0.1.0
     */
    private function check_version(string $version): bool {
        if ($this->plugin_version !== $version) {
            return true;
        }

        return false;
    }

    /**
     * Get the latest version from GithubApi and return object (message)
     * @since 0.1.0
     * @return array 
     */
    public function check_update(): array {
        $version = $this->api->get_repo_releases()[0]->tag_name;

        if ($this->check_version($version)) return array($version, true);
        else return array($version, false);
    }

    /**
     * Download version and extract zip to plugin dir
     * @since 0.1.4
     */
    public function upgrade(): void {
        global $dir; // Plugin dir

        $result_folder = $this->lastFolderName($dir);

        $asset = $this->api->get_latest_release_asset(0, $this->megabyteToByte(ASSET_FILE_SIZE_LIMIT));

        // Bug fixed : https://github.com/Asfris/TwentyTwentyTwoPlus/issues/23
        $newFilePath = ABSPATH . "wp-content/plugins/" . $result_folder . "/tttp.zip";
        $pluginPath  = ABSPATH . "wp-content/plugins/" . $result_folder;

        // Download and save asset file
        $asset->download();
        $asset->save($newFilePath);

        $zip = new ZipArchive;

        $res = $zip->open($newFilePath);

        if ($res === TRUE) {
            echo "Zip file opened.\n";
            echo "Zip file extracted.\n";
            try
            {
                $zip->extractTo($pluginPath);
                $zip->close();
            }
            catch(Error $ex)
            {
                echo $ex->fullErrorMessage();
            }
        }
        else {
            echo "ERROR :" . $res;
        }

        echo "Updated.\n";
    }
}
