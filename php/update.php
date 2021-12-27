<?php

namespace Update;

use ErrorHandle\Error;
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

trait Request
{
    /**
     * Sends get request with cURL
     * @since 0.1.0
     * @since 0.1.4 Added inside Request trait
     */
    private function send_get(string $url): bool | string {
        $userAgent = 'Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0';

        //Initialize cURL.
        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_USERAGENT, $userAgent );

        //Set the URL that you want to GET by using the CURLOPT_URL option.
        curl_setopt($ch, CURLOPT_URL, $url);

        //Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        //Execute the request.
        $data = curl_exec($ch);

        //Close the cURL handle.
        curl_close($ch);

        return $data;
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
    use Request;

    /**
     * Url of api
     */
    private string $url;

    /**
     * When class created
     * @param string $url Pass main url of api
     * @since 0.1.0
     */
    function __construct(string $url) {
        $this->url = $url;
    }

    /**
     * Send get request to api router and recives data
     * @param string $router Addr to send get request. example: github.com/sampleRouter
     * @since 0.1.0
     * @since 0.1.3 Removed Unnecessary variable
     */
    public function get_data(string $router) {
        return $this->send_get($this->url . $router);
    }

    /**
     * Send get request to api router and recives data and decoded in json
     * @param string $router Addr to send get request. example: github.com/sampleRouter
     * @since 0.1.0
     * @return string[] decoded json
     */
    public function get_data_as_json(string $router) {
        $response = $this->send_get($this->url . $router);

        if (!$response) return array("message" => "Error: Response is null");

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
     * @return string[] response or error
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
