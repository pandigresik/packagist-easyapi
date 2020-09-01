<?php namespace asligresik\easyapi\Commands\API;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Autoload;

/**
 * Class PublishCommand.
 */
class PublishCommand extends BaseCommand
{
    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'Generators';

    /**
     * The command's name.
     *
     * @var string
     */
    protected $name = 'api:publish';

    /**
     * The command's short description.
     *
     * @var string
     */
    protected $description = 'Publish assets plugin into the current public directory.';

    /**
     * The command's usage.
     *
     * @var string
     */
    protected $usage = 'api:publish';

    /**
     * The commamd's argument.
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The command's options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * The path to agungsugiarto\boilerplate\src directory.
     *
     * @var string
     */
    protected $sourcePath;

    //--------------------------------------------------------------------

    /**
     * Displays the help for the spark cli script itself.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $this->determineSourcePath();
        $config = new Autoload();
        $appPath = $config->psr4[APP_NAMESPACE];        
        $viewsSource = $this->sourcePath.'/Views';
        $viewsDestination = $appPath . '/Views';        
        $this->recursive_copy($viewsSource, $viewsDestination);

        $swaggerSource = $this->sourcePath . '/Controllers/Swagger.php';
        $swaggerDestination = $appPath . '/Controllers/Swagger.php';        
        copy($swaggerSource, $swaggerDestination);

        $publicSource = $this->sourcePath . '/public';
        $publicDestination = dirname($appPath) . '/public';        
        $this->recursive_copy($publicSource, $publicDestination);
    }
    
    /**
     * Determines the current source path from which all other files are located.
     */
    protected function determineSourcePath()
    {
        $this->sourcePath = realpath(__DIR__ . '/../../');
        
        if ($this->sourcePath == '/' || empty($this->sourcePath)) {
            CLI::error('Unable to determine the correct source directory. Bailing.');
            exit();
        }
    }

    /* 
* This function copy $source directory and all files 
* and sub directories to $destination folder
*/

    private function recursive_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);        
        while (($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recursive_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
